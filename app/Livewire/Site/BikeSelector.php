<?php

namespace App\Livewire\Site;

use App\Models\MotorcycleMake;
use App\Models\MotorcycleModel;
use Livewire\Component;

class BikeSelector extends Component
{
    public ?string $makeSlug = null;
    public ?string $modelSlug = null;
    public ?int $year = null;

    public function mount(?string $makeSlug = null, ?string $modelSlug = null, ?int $year = null): void
    {
        $this->makeSlug = $makeSlug ?: $this->makeSlug;
        $this->modelSlug = $modelSlug ?: $this->modelSlug;
        $this->year = $year ?: $this->year;
    }

    public function updatedMakeSlug(): void
    {
        $this->modelSlug = null;
        $this->year = null;
    }

    public function updatedModelSlug(): void
    {
        $this->year = null;
    }

    public function findParts(): mixed
    {
        if (! $this->makeSlug) {
            return null;
        }

        $params = ['marca' => $this->makeSlug];
        $route = 'site.parts.make';

        if ($this->modelSlug) {
            $params['modelo'] = $this->modelSlug;
            $route = 'site.parts.model';

            if ($this->year) {
                $params['ano'] = $this->year;
                $route = 'site.parts.year';
            }
        }

        return $this->redirectRoute($route, $params, navigate: false);
    }

    public function getMakesProperty()
    {
        return MotorcycleMake::active()->ordered()->get(['id', 'name', 'slug']);
    }

    public function getModelsProperty()
    {
        if (! $this->makeSlug) {
            return collect();
        }

        return MotorcycleModel::active()
            ->whereHas('make', fn ($q) => $q->where('slug', $this->makeSlug))
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'year_start', 'year_end']);
    }

    public function getYearsProperty()
    {
        if (! $this->modelSlug) {
            return collect();
        }

        $model = MotorcycleModel::where('slug', $this->modelSlug)->first();
        if (! $model) {
            return collect();
        }

        $start = $model->year_start ?: 2000;
        $end = $model->year_end ?: (int) date('Y');

        return collect(range($end, $start));
    }

    public function render()
    {
        return view('livewire.site.bike-selector', [
            'makes' => $this->makes,
            'models' => $this->models,
            'years' => $this->years,
        ]);
    }
}
