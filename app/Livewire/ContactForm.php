<?php

namespace App\Livewire;

use App\Events\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public string $honeypot = '';

    public string $preferredChannel = 'email';

    public bool $acceptedMarketing = false;

    public string $turnstileToken = '';

    public bool $succeeded = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:20|max:2000',
            'preferredChannel' => 'required|in:phone,email,whatsapp',
            'acceptedMarketing' => 'accepted',
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
            'preferredChannel.required' => __('contact.form_validation_preferred_contact_required'),
            'acceptedMarketing.accepted' => __('contact.form_validation_marketing_required'),
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

        $turnstileSecret = config('services.turnstile.secret_key');
        if ($turnstileSecret) {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $turnstileSecret,
                'response' => $this->turnstileToken,
                'remoteip' => $ip,
            ]);

            $outcome = $response->json();

            if (! ($outcome['success'] ?? false)) {
                $this->addError('turnstile', __('contact.form_validation_captcha'));

                return;
            }
        }

        $contactMessage = ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
            'preferred_channel' => $this->preferredChannel,
            'accepted_marketing' => $this->acceptedMarketing,
        ]);

        event(new ContactMessageReceived($contactMessage));

        $this->reset(['name', 'email', 'subject', 'message', 'preferredChannel', 'acceptedMarketing', 'honeypot', 'turnstileToken']);
        $this->succeeded = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
