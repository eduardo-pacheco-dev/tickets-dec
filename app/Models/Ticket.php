<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Models\TicketStatus as TicketStatusModel;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $tracking_code
 * @property string $site_id
 * @property string $technician_name
 * @property string $report_description
 * @property bool $checked_in
 * @property TicketStatus $status
 * @property string|null $admin_response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ReportType> $reportTypes
 */
#[Fillable(['site_id', 'technician_name', 'report_description', 'checked_in', 'status', 'admin_response'])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'checked_in' => 'boolean',
        ];
    }

    public function reportTypes(): BelongsToMany
    {
        return $this->belongsToMany(ReportType::class)->withTimestamps();
    }

    public function statusModel(): BelongsTo
    {
        return $this->belongsTo(TicketStatusModel::class, 'status', 'name');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class)->latest();
    }

    public function statusLabel(): string
    {
        return $this->statusModel?->label
            ?? TicketStatus::tryFrom($this->status)?->label()
            ?? $this->status;
    }

    public function statusColor(): string
    {
        return $this->statusModel?->color
            ?? TicketStatus::tryFrom($this->status)?->color()
            ?? 'zinc';
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->tracking_code)) {
                $ticket->tracking_code = self::generateTrackingCode();
            }
        });
    }

    public static function generateTrackingCode(): string
    {
        do {
            $code = 'TK-'.strtoupper(Str::random(6));
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }
}
