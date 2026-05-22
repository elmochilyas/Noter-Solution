<?php

namespace App\Livewire;

use App\Models\Faq;
use Livewire\Component;

class FaqSearch extends Component
{
    public string $query = '';

    public string $category = '';

    public function render()
    {
        $faqs = Faq::where('is_published', true);

        if ($this->category) {
            $faqs->where('category', $this->category);
        }

        if ($this->query) {
            $locale = app()->getLocale();
            $like = 'LIKE';

            $faqs->where(function ($q) use ($locale, $like) {
                $q->where("question_translations->{$locale}", $like, "%{$this->query}%")
                    ->orWhere("answer_translations->{$locale}", $like, "%{$this->query}%");
            });
        }

        $faqs->orderBy('display_order');

        $categories = Faq::where('is_published', true)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.faq-search', [
            'faqs' => $faqs->get(),
            'categories' => $categories,
        ]);
    }

    public function filterByCategory(string $category): void
    {
        $this->category = $category;
    }

    public function clearCategory(): void
    {
        $this->category = '';
    }

    public function incrementViewCount(int $id): void
    {
        Faq::where('id', $id)->increment('view_count');
    }
}
