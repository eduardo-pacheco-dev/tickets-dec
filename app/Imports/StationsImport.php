<?php

namespace App\Imports;

use App\Models\Station;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class StationsImport implements SkipsEmptyRows, ToArray, WithHeadingRow
{
    /**
     * @var array<string, list<string>>
     */
    protected const ALIASES = [
        'site_id' => ['site_id', 'site id'],
        'address_id' => ['endereco_id', 'endereco', 'address_id'],
        'element_type' => ['tipo_de_elemento', 'elemento', 'element_type'],
        'technology' => ['tecnologia', 'technology'],
        'classification' => ['classificacao', 'classification'],
        'status' => ['status'],
        'area_holder' => ['detentor_da_area', 'area_holder'],
        'infra_contract_type' => ['tipo_de_contrato_infra', 'infra_contract_type'],
        'infra_holder' => ['detentor_de_infra', 'infra_holder'],
        'infra_type' => ['tipo_de_infra', 'infra_type'],
        'ev_type' => ['tipo_de_ev', 'ev_type'],
        'ev_provider' => ['fornecedor_de_ev', 'ev_provider'],
        'tower_type' => ['tipo_da_torre', 'tower_type'],
        'street_type' => ['tipo_de_logradouro', 'street_type'],
        'street' => ['logradouro', 'street'],
        'number' => ['numero', 'number'],
        'complement' => ['complemento', 'complement'],
        'neighborhood' => ['bairro', 'neighborhood'],
        'city' => ['municipio', 'city', 'cidade'],
        'state' => ['estado', 'uf', 'state'],
        'cep' => ['cep'],
        'regional' => ['regional'],
        'latitude' => ['latitude', 'lat'],
        'longitude' => ['longitude', 'long', 'lng'],
        'external_id' => ['external_id_station_id', 'external_id', 'station_id', 'station id'],
        'aev_nominal' => ['aev_nominal', 'aev'],
        'land_area' => ['area_de_solo', 'area_solo', 'land_area'],
        'structure_height' => ['altura_da_estrutura', 'structure_height'],
        'observation' => ['observacao', 'observation'],
        'justification' => ['justificativa', 'justification'],
    ];

    /**
     * @var array{created: int, updated: int, errors: list<array{row: int, message: string}>}
     */
    protected array $result = [
        'created' => 0,
        'updated' => 0,
        'errors' => [],
    ];

    /**
     * @var array<string, string>
     */
    protected array $headerMap = [];

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function array(array $rows): void
    {
        $this->resolveHeaders(array_keys($rows[0] ?? []));

        foreach ($rows as $index => $row) {
            $this->importRow($row, $index + 2);
        }
    }

    /**
     * @return array{created: int, updated: int, errors: list<array{row: int, message: string}>}
     */
    public function summary(): array
    {
        return $this->result;
    }

    /**
     * @param  list<string>  $headers
     */
    protected function resolveHeaders(array $headers): void
    {
        foreach (self::ALIASES as $attribute => $aliases) {
            foreach ($headers as $header) {
                if (in_array($this->normalizeHeader($header), array_map(fn (string $alias): string => $this->normalizeHeader($alias), $aliases), true)) {
                    $this->headerMap[$attribute] = $header;

                    continue 2;
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function importRow(array $row, int $rowNumber): void
    {
        $siteId = $this->column($row, 'site_id');

        if ($siteId === null || trim($siteId) === '') {
            $this->result['errors'][] = [
                'row' => $rowNumber,
                'message' => 'O campo "Site ID" é obrigatório.',
            ];

            return;
        }

        try {
            $data = [
                'site_id' => strtoupper(trim($siteId)),
                'element_type' => $this->uppercase($row, 'element_type'),
                'address_id' => $this->uppercase($row, 'address_id'),
                'technology' => $this->uppercase($row, 'technology'),
                'classification' => $this->nullIfEmpty($this->column($row, 'classification')),
                'status' => $this->nullIfEmpty($this->column($row, 'status')),
                'area_holder' => $this->nullIfEmpty($this->column($row, 'area_holder')),
                'infra_contract_type' => $this->nullIfEmpty($this->column($row, 'infra_contract_type')),
                'infra_holder' => $this->nullIfEmpty($this->column($row, 'infra_holder')),
                'infra_type' => $this->nullIfEmpty($this->column($row, 'infra_type')),
                'ev_type' => $this->nullIfEmpty($this->column($row, 'ev_type')),
                'ev_provider' => $this->nullIfEmpty($this->column($row, 'ev_provider')),
                'tower_type' => $this->nullIfEmpty($this->uppercase($row, 'tower_type')),
                'street_type' => $this->nullIfEmpty($this->uppercase($row, 'street_type')),
                'street' => $this->nullIfEmpty($this->column($row, 'street')),
                'number' => $this->nullIfEmpty($this->column($row, 'number')),
                'complement' => $this->nullIfEmpty($this->column($row, 'complement')),
                'neighborhood' => $this->nullIfEmpty($this->uppercase($row, 'neighborhood')),
                'city' => $this->nullIfEmpty($this->uppercase($row, 'city')),
                'state' => $this->nullIfEmpty($this->uppercase($row, 'state')),
                'cep' => $this->nullIfEmpty($this->cepDigits($row)),
                'regional' => $this->nullIfEmpty($this->uppercase($row, 'regional')),
                'latitude' => $this->coordinate($row, 'latitude'),
                'longitude' => $this->coordinate($row, 'longitude'),
                'external_id' => $this->nullIfEmpty($this->uppercase($row, 'external_id')),
                'aev_nominal' => $this->number($row, 'aev_nominal'),
                'land_area' => $this->number($row, 'land_area'),
                'structure_height' => $this->number($row, 'structure_height'),
                'observation' => $this->nullIfEmpty($this->column($row, 'observation')),
                'justification' => $this->nullIfEmpty($this->column($row, 'justification')),
                'is_active' => true,
            ];

            $station = Station::updateOrCreate(['site_id' => $data['site_id']], $data);

            $this->result[($station->wasRecentlyCreated ? 'created' : 'updated')]++;
        } catch (Throwable $e) {
            $this->result['errors'][] = [
                'row' => $rowNumber,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function normalizeHeader(string $header): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower($header));

        if ($normalized === false) {
            $normalized = mb_strtolower($header);
        }

        return preg_replace('/[^a-z0-9]/', '', $normalized) ?? '';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function column(array $row, string $attribute): ?string
    {
        $header = $this->headerMap[$attribute] ?? null;

        if ($header === null || ! array_key_exists($header, $row)) {
            return null;
        }

        $value = $row[$header];

        if (! is_scalar($value)) {
            return null;
        }

        $value = (string) $value;

        return trim($value) === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function uppercase(array $row, string $attribute): ?string
    {
        $value = $this->column($row, $attribute);

        return $value === null ? null : strtoupper($value);
    }

    protected function nullIfEmpty(?string $value): ?string
    {
        return $value === null ? null : (trim($value) === '' ? null : $value);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function cepDigits(array $row): ?string
    {
        $value = $this->column($row, 'cep');

        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        return $digits === '' ? null : $digits;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function coordinate(array $row, string $attribute): ?string
    {
        $value = $this->column($row, $attribute);

        if ($value === null) {
            return null;
        }

        return str_replace(',', '.', $value);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function number(array $row, string $attribute): ?string
    {
        $value = $this->coordinate($row, $attribute);

        if ($value === null) {
            return null;
        }

        return is_numeric($value) ? (string) (float) $value : $value;
    }
}
