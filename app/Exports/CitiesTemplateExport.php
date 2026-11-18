<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sample sheet for the cities bulk upload.
 */
class CitiesTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return [
            ['Austin', 'TX', 31, 1, 30.2711286, -97.7436995],
            ['Denver', 'CO', 32, 1, 39.7392364, -104.984862],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['city', 'state', 'sort_order', 'is_active', 'latitude', 'longitude'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
