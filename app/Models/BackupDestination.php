<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupDestination extends Model
{
    protected $fillable = [
        'database_id',
        'type',
        'path',
        'credentials',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:json',
            'is_active' => 'boolean',
        ];
    }

    public function database(): BelongsTo
    {
        return $this->belongsTo(Database::class);
    }
}
