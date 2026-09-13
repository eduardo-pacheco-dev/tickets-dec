<?php

namespace App\Models;

use App\Enums\StationAttachmentType;
use Database\Factories\StationAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $station_id
 * @property string $type
 * @property string $original_name
 * @property string $path
 * @property string $mime_type
 * @property int $size
 * @property int|null $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class StationAttachment extends Model
{
    /** @use HasFactory<StationAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'station_id',
        'type',
        'original_name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => StationAttachmentType::class,
            'size' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Station, $this>
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function humanSize(): string
    {
        $bytes = $this->size;

        $units = ['B', 'KB', 'MB', 'GB'];

        foreach ($units as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 2).' '.$unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 2).' TB';
    }
}
