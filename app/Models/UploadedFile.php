<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UploadedFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'file_type',
        'mime_type',
        'file_path',
        'file_size',
        'is_public',
        'description',
        'views_count',
        'downloads_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'views_count' => 'integer',
        'downloads_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permissions()
    {
        return $this->hasMany(FilePermission::class, 'file_id');
    }

    public function canBeViewedBy(User $user): bool
    {
        if ($this->user_id === $user->id || $user->isAdmin() || $this->is_public) {
            return true;
        }
        return $this->permissions()
            ->where('user_id', $user->id)
            ->where('can_view', true)
            ->exists();
    }

    public function canBeDownloadedBy(User $user): bool
    {
        if ($this->user_id === $user->id || $user->isSuperAdmin()) {
            return true;
        }
        return $this->permissions()
            ->where('user_id', $user->id)
            ->where('can_download', true)
            ->exists();
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementDownloads()
    {
        $this->increment('downloads_count');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('file_type', $type);
    }
}