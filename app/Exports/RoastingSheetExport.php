<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RoastingSheetExport implements FromCollection, WithHeadings, WithTitle
{
    protected $entries;

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    public function collection()
    {
        return $this->entries->map(function ($item) {
            return [
                'Kode Batch'       => $item->kode_batch,
                'Tanggal Roasting' => $item->tanggal_roasting,
                'Operator'         => $item->user->name ?? '-',
                'Status'           => $item->status_batch,
            ];
        });
    }

    public function headings(): array
    {
        return ['Kode Batch', 'Tanggal Roasting', 'Operator', 'Status'];
    }

    public function title(): string
    {
        return 'Batch Roasting';
    }
}