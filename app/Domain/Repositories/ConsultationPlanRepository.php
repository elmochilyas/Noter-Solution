<?php

namespace App\Domain\Repositories;

use App\Models\ConsultationPlan;
use Illuminate\Database\Eloquent\Collection;

interface ConsultationPlanRepository
{
    public function findBySlug(string $slug): ?ConsultationPlan;

    public function active(): Collection;

    public function recommended(): ?ConsultationPlan;
}
