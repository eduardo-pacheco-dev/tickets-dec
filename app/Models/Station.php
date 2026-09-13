<?php

namespace App\Models;

use Database\Factories\StationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $element_type
 * @property string $technology
 * @property string $address_id
 * @property string $classification
 * @property string $area_holder
 * @property string $infra_contract_type
 * @property string $infra_holder
 * @property string $infra_type
 * @property string|null $ev_type
 * @property string|null $ev_provider
 * @property string|null $observation
 * @property string|null $justification
 * @property string $street_type
 * @property string $street
 * @property string $number
 * @property string|null $complement
 * @property string $neighborhood
 * @property string $city
 * @property string $state
 * @property string $cep
 * @property string $regional
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $status
 * @property string|null $tower_type
 * @property string|null $aev_nominal
 * @property string|null $land_area
 * @property string|null $structure_height
 * @property string|null $external_id
 * @property string $site_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory;

    protected $fillable = [
        'element_type',
        'technology',
        'address_id',
        'classification',
        'area_holder',
        'infra_contract_type',
        'infra_holder',
        'infra_type',
        'ev_type',
        'ev_provider',
        'observation',
        'justification',
        'street_type',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'cep',
        'regional',
        'latitude',
        'longitude',
        'status',
        'tower_type',
        'aev_nominal',
        'land_area',
        'structure_height',
        'external_id',
        'site_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->site_id).' — '.trim($this->city.'/'.$this->state);
    }
}
