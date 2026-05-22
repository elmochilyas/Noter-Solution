<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\ConsultationPlanRepository;
use App\Models\ConsultationPlan;
use Illuminate\Database\Eloquent\Collection;

final class EloquentConsultationPlanRepository implements ConsultationPlanRepository
{
    public function findBySlug(string $slug): ?ConsultationPlan
    {
        return ConsultationPlan::where('slug', $slug)->first();
    }

    public function active(): Collection
    {
        return ConsultationPlan::where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function recommended(): ?ConsultationPlan
    {
        return ConsultationPlan::where('is_recommended', true)
            ->where('is_active', true)
            ->first();
    }
}
