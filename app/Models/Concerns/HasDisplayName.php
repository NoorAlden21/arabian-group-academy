<?php

namespace App\Models\Concerns;

trait HasDisplayName
{
    public function getDisplayNameAttribute(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            return $this->name_ar ?: $this->name;
        }
        return $this->name ?: $this->name_ar;
    }

    public function scopeOrderByDisplayName($query, string $direction = 'asc')
    {
        $col = app()->getLocale() === 'ar' ? 'name_ar' : 'name';
        return $query->orderBy($col, $direction);
    }

    public function getTranslationsAttribute(): array
    {
        return ['en' => $this->name, 'ar' => $this->name_ar];
    }
}
