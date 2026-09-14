<?php

namespace App\Models;

use Database\Factories\TicketStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string $color
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TicketStatus extends Model
{
    /** @use HasFactory<TicketStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function finalName(): ?string
    {
        return static::query()->active()->orderByDesc('sort_order')->value('name');
    }

    public static function queueCount(): int
    {
        $final = static::finalName();

        if ($final === null) {
            return 0;
        }

        return Ticket::query()->where('status', '!=', $final)->count();
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'status', 'name');
    }

    /**
     * @return array<string, string>
     */
    public static function colorOptions(): array
    {
        return [
            'yellow' => 'Amarelo',
            'blue' => 'Azul',
            'green' => 'Verde',
            'red' => 'Vermelho',
            'zinc' => 'Cinza',
            'purple' => 'Roxo',
            'orange' => 'Laranja',
            'amber' => 'Âmbar',
        ];
    }
}
