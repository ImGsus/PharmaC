<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class OrganizedFileStorage
{
    public const ROOT = 'system';

    public function store(UploadedFile $file, string $folder, ?string $filename = null): string
    {
        $directory = storage_path('app/'.self::ROOT.'/'.$folder);
        File::ensureDirectoryExists($directory);
        $filename = $filename ?: Str::uuid().'.'.$file->extension();
        $file->move($directory, $filename);
        return $filename;
    }

    public function path(string $folder, string $filename): string
    {
        return storage_path('app/'.self::ROOT.'/'.$folder.'/'.basename($filename));
    }

    public function legacyPath(string $folder, string $filename): string
    {
        return storage_path('app/'.$folder.'/'.basename($filename));
    }

    public function delete(string $folder, ?string $filename): void
    {
        if (!$filename) return;
        $path = $this->path($folder, $filename);
        if (File::exists($path)) File::delete($path);
    }
}