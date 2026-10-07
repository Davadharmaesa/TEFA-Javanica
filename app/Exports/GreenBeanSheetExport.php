<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GreenBeanSheetExport implements FromCollection, WithHeadings, WithTitle
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
                'Tanggal Beli' => $item->tanggal_beli,
                'Nama Jenis'   => $item->greenBean->nama_jenis ?? '-',
                'Stok (Gram)'  => $item->stok_gram,
                'Harga Beli'   => $item->harga_beli,
            ];
        });
    }

    public function headings(): array
    {
        return ['Tanggal Beli', 'Jenis Green Bean', 'Stok (Gram)', 'Harga Beli (Rp)'];
    }

    public function title(): string
    {
        return 'Stok Green Bean';
    }
}