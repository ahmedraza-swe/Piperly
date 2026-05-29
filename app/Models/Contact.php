<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model implements HasName
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function (Contact $contact): void {
            if (! $contact->is_primary || ! $contact->lead_id) {
                return;
            }

            static::query()
                ->where('tenant_id', $contact->tenant_id)
                ->where('lead_id', $contact->lead_id)
                ->whereKeyNot($contact->id)
                ->update(['is_primary' => false]);
        });
    }

    protected $fillable = [
        'tenant_id',
        'lead_id',
        'first_name',
        'last_name',
        'job_title',
        'email',
        'phone',
        'is_primary',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(function (): string {
            $parts = array_filter([$this->first_name, $this->last_name]);

            return $parts !== [] ? implode(' ', $parts) : (string) $this->first_name;
        });
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
    }
}
