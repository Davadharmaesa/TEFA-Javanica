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

class LabaRugiSheetExport implements FromCollection, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $d = $this->data;
        $periodeStr = 'Dari ' . \Carbon\Carbon::parse($d['start'])->translatedFormat('d F Y') . ' Sampai ' . \Carbon\Carbon::parse($d['end'])->translatedFormat('d F Y');

        $rows = [];
        $rows[] = ['Laporan Laba & Rugi', '', '', ''];
        $rows[] = ['TEFA JAVANICA', '', '', ''];
        $rows[] = [$periodeStr, '', '', ''];
        $rows[] = ['', '', '', ''];

        // Header Tabel (Row 5)
        $rows[] = ['Akun & Keterangan', 'Rincian', 'Subtotal', 'Total'];

        // 1. Pendapatan Penjualan
        $rows[] = ['Pendapatan dari Penjualan', '', '', ''];
        $rows[] = ['  Penjualan', '', $d['penjualanKotor'], ''];
        $rows[] = ['  -Retur dan Pot. Penjualan', $d['returPenjualan'], '', ''];
        $rows[] = ['  Diskon Penjualan', $d['diskonPenjualan'], $d['potonganPenjualan'], ''];
        $rows[] = ['Penjualan Bersih', '', '', $d['penjualanBersih']];

        // 2. HPP
        $rows[] = ['Harga Pokok Penjualan', '', '', ''];
        $rows[] = ['  Biaya Bahan Baku (Green Bean)', $d['hppBahanBaku'], '', ''];
        $rows[] = ['  Biaya Bahan Kemasan (Packaging)', $d['hppKemasan'], '', ''];
        $rows[] = ['Total Harga Pokok Penjualan', '', '', -$d['totalHpp']];

        // 3. Laba Kotor
        $rows[] = ['Laba Kotor', '', '', $d['labaKotor']];

        // 4. Beban Operasi
        $rows[] = ['Beban Operasi :', '', '', ''];
        $rows[] = ['Beban Penjualan / Operasional', '', '', ''];
        $rows[] = ['  Biaya Gaji / Upah Tenaga Kerja', $d['bebanGaji'], '', ''];
        $rows[] = ['  Total Beban Penjualan', '', $d['totalBebanPenjualan'], ''];

        $rows[] = ['Beban Administrasi & Umum', '', '', ''];
        $rows[] = ['  Biaya Listrik Mesin Roasting', $d['bebanListrik'], '', ''];
        $rows[] = ['  Biaya Penyusutan Mesin', $d['bebanPenyusutan'], '', ''];
        $rows[] = ['  Total Beban Administrasi', '', $d['totalBebanAdministrasi'], ''];

        $rows[] = ['Jumlah Beban Operasi', '', '', -$d['jumlahBebanOperasi']];

        // 5. Laba Bersih Operasional
        $rows[] = ['Laba Bersih Operasional', '', '', $d['labaBersihOperasional']];

        // 6. Non-Operasi
        $rows[] = ['Pendapatan dan Beban Non Operasi', '', '', ''];
        $rows[] = ['  Pendapatan dan Beban Lainnya', 0, '', ''];
        $rows[] = ['Jumlah Pendapatan dan Beban Lainnya', '', 0, 0];

        // 7. Laba Bersih
        $rows[] = ['Laba Bersih', '', '', $d['labaBersih']];

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        // Judul Dokumen (A1:D3)
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('311A0D');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('C77054');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('7A6050');

        // Header Tabel (Row 5)
        $sheet->getStyle('A5:D5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'C77054'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('B5:D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Styling Kategori Utama
        $sectionRows = [6, 10, 11, 14, 15, 16, 17, 24, 25, 26, 29];
        foreach ($sectionRows as $r) {
            $sheet->getStyle("A{$r}:D{$r}")->getFont()->setBold(true);
        }

        // Subheader background
        foreach ([6, 11, 16, 26] as $r) {
            $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5EDE8'],
                ],
            ]);
        }

        // Final Laba Bersih (Row 29)
        $sheet->getStyle('A29:D29')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'C77054']],
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
            'B' => '#,##0;(#,##0);"-"',
            'C' => '#,##0;(#,##0);"-"',
            'D' => '#,##0;(#,##0);"-"',
        ];
    }

    public function title(): string
    {
        return 'Laporan Laba & Rugi';
    }
}
