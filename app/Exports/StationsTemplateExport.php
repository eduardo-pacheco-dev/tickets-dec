<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StationsTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return list<string>
     */
    public const HEADINGS = [
        'Site ID',
        'Endereço ID',
        'Tipo de elemento',
        'Tecnologia',
        'Classificação',
        'Status',
        'Detentor da Área',
        'Tipo de contrato Infra',
        'Detentor de Infra',
        'Tipo de Infra',
        'Tipo de EV',
        'Fornecedor de EV',
        'Tipo da torre',
        'Tipo de logradouro',
        'Logradouro',
        'Número',
        'Complemento',
        'Bairro',
        'Município',
        'Estado',
        'CEP',
        'Regional',
        'Latitude',
        'Longitude',
        'External ID (Station ID)',
        'AEV Nominal',
        'Área de solo',
        'Altura da estrutura',
        'Observação',
        'Justificativa',
    ];

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return self::HEADINGS;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return [
            [
                '4G-ABLAJ1',
                'ACABL_0001',
                'ENODE B',
                'LTE',
                'RANSHARING',
                'Aquisitado',
                'IHS BRAZIL',
                'Built-to-Suit',
                'AMERICAN TOWER',
                'GREENFIELD',
                'TORRE METALICA TRIANGULAR',
                'BRASILSAT',
                'TORRE METALICA',
                'RUA',
                'MANOEL BATISTA DE ARAÚJO',
                'S/N',
                'QUADRA 12, LOTE 09',
                'CENTRO',
                'ASSIS BRASIL',
                'AC',
                '69935000',
                'TCO',
                '-10,925094',
                '-69,554056',
                'ACR001TM',
                '0',
                '0',
                '40',
                'Exemplo para preenchimento',
                null,
            ],
        ];
    }
}
