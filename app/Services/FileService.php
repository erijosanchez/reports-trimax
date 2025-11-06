<?php

namespace App\Services;

use App\Models\UploadedFile;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public static function uploadFile(
        HttpUploadedFile $file,
        int $userId,
        ?string $description = null,
        bool $isPublic = false
    ): UploadedFile {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, 'local');

        return UploadedFile::create([
            'user_id' => $userId,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'is_public' => $isPublic,
            'description' => $description,
        ]);
    }

    public static function deleteFile(UploadedFile $file): bool
    {
        if (Storage::exists($file->file_path)) {
            Storage::delete($file->file_path);
        }

        return $file->delete();
    }

    public static function getFileContent(UploadedFile $file): string
    {
        return Storage::get($file->file_path);
    }

    public static function getUserFiles(int $userId, ?string $fileType = null)
    {
        return UploadedFile::where('user_id', $userId)
            ->when($fileType, fn($q) => $q->where('file_type', $fileType))
            ->latest()
            ->get();
    }
}
