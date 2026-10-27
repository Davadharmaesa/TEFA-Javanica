<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GreenBeanSheetExport implements FromCollection, WithTitle, ShouldAutoSize, WithStyles
{
    protected $entries;
    protected $start;
    protected $end;

    public function __construct($entries, $start = null, $end = null)
    {
        $this->entries = $entries;
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        $rows = [];
        $rows[] = ['TEFA JAVANICA', '', '', ''];
        $rows[] = ['LAPORAN STOK GREEN BEAN', '', '', ''];

        if ($this->start && $this->end) {
            $periodeStr = 'PERIODE: ' . strtoupper(\Carbon\Carbon::parse($this->start)->translatedFormat('d F Y')) . ' - ' . strtoupper(\Carbon\Carbon::parse($this->end)->translatedFormat('d F Y'));
            $rows[] = [$periodeStr, '', '', ''];
        } else {
            $rows[] = ['PERIODE: SEMUA WAKTU', '', '', ''];
        }

        $rows[] = ['', '', '', ''];
        $rows[] = ['Tanggal Beli', 'Jenis Green Bean', 'Stok (Gram)', 'Harga Beli (Rp)'];

        foreach ($this->entries as $item) {
            $rows[] = [
                $item->tanggal_beli,
                $item->greenBean->nama_jenis ?? '-',
                $item->stok_gram,
                $item->harga_beli,
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(10);

        // Header Table (Row 5)
        $sheet->getStyle('A5:D5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7065A3'],
            ],
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Stok Green Bean';
    }
}