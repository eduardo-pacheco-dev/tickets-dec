<?php

namespace App\Models;

use Database\Factories\TicketStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property string|null $from_status
 * @property string $to_status
 * @property int|null $changed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TicketStatusHistory extends Model
{
    /** @use HasFactory<TicketStatusHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'from_status',
        'to_status',
        'changed_by',
    ];

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function fromLabel(): ?string
    {
        return $this->from_status ? (new Ticket)->forceFill(['status' => $this->from_status])->statusLabel() : null;
    }

    public function toLabel(): string
    {
        return (new Ticket)->forceFill(['status' => $this->to_status])->statusLabel();
    }
}
