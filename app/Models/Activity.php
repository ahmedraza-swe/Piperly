<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model implements HasName
{
    protected $fillable = [
        'tenant_id',
        'lead_id',
        'contact_id',
        'user_id',
        'type',
        'subject',
        'description',
        'due_at',
        'completed_at',
        'last_reminder_sent_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Activity $activity): void {
            if (! $activity->lead_id) {
                return;
            }

            Lead::query()->whereKey($activity->lead_id)->update([
                'last_contacted_at' => now(),
            ]);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFilamentName(): string
    {
        return $this->subject;
    }

    public function isOpen(): bool
    {
        return $this->completed_at === null;
    }

    public function isOverdue(): bool
    {
        if ($this->completed_at !== null || $this->due_at === null) {
            return false;
        }

        return $this->due_at->isPast();
    }

    /**
     * Open activities with a due date of today (calendar) or earlier (overdue).
     */
    public function scopeOpenDueWindow(Builder $query): Builder
    {
        return $query
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where(function (Builder $q): void {
                $q->whereDate('due_at', today())
                    ->orWhere('due_at', '<', today()->startOfDay());
            });
    }
}
