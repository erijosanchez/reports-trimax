<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilePermission extends Model
{
    protected $fillable = [
        'file_id',
        'user_id',
        'role_id',
        'can_view',
        'can_download',
        'can_delete',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_download' => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function file()
    {
        return $this->belongsTo(UploadedFile::class, 'file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }
}

