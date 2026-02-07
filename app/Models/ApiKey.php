<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'name',
        'key',
        'secret',
        'tier',
        'rate_limit',
        'permissions',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];

    /**
     * Generate a new API key and secret.
     */
    public static function generate(int $userId, string $name, ?int $vendorId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'vendor_id' => $vendorId,
            'name' => $name,
            'key' => 'gps_' . Str::random(32),
            'secret' => Str::random(64),
            'tier' => 'free',
            'rate_limit' => 1000,
            'permissions' => ['*'], // Full access by default for now
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApiRequest::class);
    }

    public function isValid(): bool
    {
        return !$this->expires_at || $this->expires_at->isFuture();
    }
}
