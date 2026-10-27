<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ArusKasSheetExport implements FromCollection, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $d = $this->data;
        $periodeStr = strtoupper(\Carbon\Carbon::parse($d['start'])->translatedFormat('d/F/Y')) . ' - ' . strtoupper(\Carbon\Carbon::parse($d['end'])->translatedFormat('d/F/Y'));
        $kolomTanggal = \Carbon\Carbon::parse($d['start'])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($d['end'])->format('d/m/Y');

        $rows = [];
        $rows[] = ['TEFA JAVANICA', ''];
        $rows[] = ['LAPORAN ARUS KAS', ''];
        $rows[] = [$periodeStr, ''];
        $rows[] = ['(Dalam IDR)', ''];
        $rows[] = ['', ''];

        // Header Tabel (Row 6)
        $rows[] = ['Akun & Kategori', $kolomTanggal];

        // 1. Aktivitas Operasional
        $rows[] = ['Arus Kas dari Aktivitas Operasional', ''];
        $rows[] = ['  Penerimaan dari pelanggan', $d['penerimaanPelanggan']];
        $rows[] = ['  Aset lancar lainnya', 0];
        $rows[] = ['  Pembayaran ke pemasok', -$d['pembayaranPemasok']];
        $rows[] = ['  Kartu kredit dan liabilitas jangka pendek lainnya', 0];
        $rows[] = ['  Pendapatan lainnya', 0];
        $rows[] = ['  Pengeluaran operasional', -$d['pengeluaranOperasional']];
        $rows[] = ['Kas Bersih dari Aktivitas Operasional', $d['kasBersihOperasional']];
        $rows[] = ['', ''];

        // 2. Aktivitas Investasi
        $rows[] = ['Arus Kas dari Aktivitas Investasi', ''];
        $rows[] = ['  Perolehan/Penjualan aset', 0];
        $rows[] = ['  Aktivitas investasi lainnya', 0];
        $rows[] = ['Kas Bersih dari Aktivitas Investasi', $d['kasBersihInvestasi']];
        $rows[] = ['', ''];

        // 3. Aktivitas Pendanaan
        $rows[] = ['Arus Kas dari Aktivitas Pendanaan', ''];
        $rows[] = ['  Pembayaran/Penerimaan pinjaman', 0];
        $rows[] = ['  Ekuitas/Modal', 0];
        $rows[] = ['Kas Bersih dari Aktivitas Pendanaan', $d['kasBersihPendanaan']];
        $rows[] = ['', ''];

        // 4. Rekonsiliasi Saldo Kas
        $rows[] = ['Kenaikan (penurunan) kas', $d['kenaikanKas']];
        $rows[] = ['Total revaluasi bank', 0];
        $rows[] = ['Saldo kas awal', $d['saldoKasAwal']];
        $rows[] = ['Saldo kas akhir', $d['saldoKasAkhir']];

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('A4:B4');

        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('311A0D');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('C77054');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('5A4033');
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('7A6050');

        // Header Tabel (Row 6)
        $sheet->getStyle('A6:B6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'C77054'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('B6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Kategori Utama
        $categoryRows = [7, 16, 21];
        foreach ($categoryRows as $r) {
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}:B{$r}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5EBE6'],
                ],
            ]);
        }

        // Subtotal
        $subtotalRows = [14, 19, 24];
        foreach ($subtotalRows as $r) {
            $sheet->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
            $sheet->getStyle("B{$r}")->applyFromArray([
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0C0B0']],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0C0B0']],
                ],
            ]);
        }

        // Rekonsiliasi (Row 26-29)
        $sheet->getStyle('A26:A29')->getFont()->setBold(true);
        $sheet->getStyle('A29:B29')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5EBE6'],
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '5A4033']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '5A4033']],
            ],
        ]);

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0;(#,##0);0',
        ];
    }

    public function title(): string
    {
        return 'Laporan Arus Kas';
    }
}
