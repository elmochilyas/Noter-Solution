<?php

namespace App\Livewire;

use App\Events\ContactMessageReceived;
use App\Models\ContactMessage;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public string $honeypot = '';

    public bool $succeeded = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:20|max:2000',
            'honeypot' => 'prohibited',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('contact.form_validation_name_required'),
            'email.required' => __('contact.form_validation_email_required'),
            'email.email' => __('contact.form_validation_email_valid'),
            'subject.required' => __('contact.form_validation_subject_required'),
            'message.required' => __('contact.form_validation_message_required'),
            'message.min' => __('contact.form_validation_message_min'),
            'message.max' => __('contact.form_validation_message_max'),
            'honeypot.prohibited' => __('contact.form_validation_rate_limit'),
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $ip = request()->ip();

        $recentCount = ContactMessage::where('ip', $ip)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        abort_if($recentCount >= 5, 429, __('contact.form_validation_rate_limit'));

        $contactMessage = ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
        ]);

        event(new ContactMessageReceived($contactMessage));

        $this->reset(['name', 'email', 'subject', 'message', 'honeypot']);
        $this->succeeded = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
