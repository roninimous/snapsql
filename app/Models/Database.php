<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Database extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'connection_type',
        'host',
        'port',
        'database',
        'username',
        'password',
        'is_active',
        'backup_frequency',
        'custom_backup_interval_minutes',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'is_active' => 'boolean',
            'port' => 'integer',
            'custom_backup_interval_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    public function backupDestination(): HasOne
    {
        return $this->hasOne(BackupDestination::class);
    }
}
