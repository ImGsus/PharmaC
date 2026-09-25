<?php

namespace App\Services;

use App\Models\ArchiveEntry;
use Illuminate\Database\Eloquent\Model;

class ArchiveService
{
    public static function record(Model $model, string $label, array $data = []): ArchiveEntry
    {
        return ArchiveEntry::create([
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'label' => $label,
            'deleted_at' => now(),
            'data' => array_merge(['attributes' => $model->toArray()], $data),
        ]);
    }
}
