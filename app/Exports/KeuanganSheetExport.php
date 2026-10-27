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

class KeuanganSheetExport implements FromCollection, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $start;
    protected $end;
    protected $penerimaanPelanggan;
    protected $pembayaranPemasok;
    protected $pengeluaranOperasional;
    protected $kasBersihOperasional;
    protected $kasBersihInvestasi;
    protected $kasBersihPendanaan;
    protected $kenaikanKas;
    protected $saldoKasAwal;
    protected $saldoKasAkhir;

    public function __construct(
        $start,
        $end,
        $penerimaanPelanggan,
        $pembayaranPemasok,
        $pengeluaranOperasional,
        $kasBersihOperasional,
        $kasBersihInvestasi,
        $kasBersihPendanaan,
        $kenaikanKas,
        $saldoKasAwal,
        $saldoKasAkhir
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->penerimaanPelanggan = $penerimaanPelanggan;
        $this->pembayaranPemasok = $pembayaranPemasok;
        $this->pengeluaranOperasional = $pengeluaranOperasional;
        $this->kasBersihOperasional = $kasBersihOperasional;
        $this->kasBersihInvestasi = $kasBersihInvestasi;
        $this->kasBersihPendanaan = $kasBersihPendanaan;
        $this->kenaikanKas = $kenaikanKas;
        $this->saldoKasAwal = $saldoKasAwal;
        $this->saldoKasAkhir = $saldoKasAkhir;
    }

    public function collection()
    {
        $periodeStr = strtoupper(\Carbon\Carbon::parse($this->start)->translatedFormat('d/F/Y')) . ' - ' . strtoupper(\Carbon\Carbon::parse($this->end)->translatedFormat('d/F/Y'));
        $kolomTanggal = \Carbon\Carbon::parse($this->start)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($this->end)->format('d/m/Y');

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
        $rows[] = ['  Penerimaan dari pelanggan', $this->penerimaanPelanggan];
        $rows[] = ['  Aset lancar lainnya', 0];
        $rows[] = ['  Pembayaran ke pemasok', -$this->pembayaranPemasok];
        $rows[] = ['  Kartu kredit dan liabilitas jangka pendek lainnya', 0];
        $rows[] = ['  Pendapatan lainnya', 0];
        $rows[] = ['  Pengeluaran operasional', -$this->pengeluaranOperasional];
        $rows[] = ['Kas Bersih dari Aktivitas Operasional', $this->kasBersihOperasional];
        $rows[] = ['', ''];

        // 2. Aktivitas Investasi
        $rows[] = ['Arus Kas dari Aktivitas Investasi', ''];
        $rows[] = ['  Perolehan/Penjualan aset', 0];
        $rows[] = ['  Aktivitas investasi lainnya', 0];
        $rows[] = ['Kas Bersih dari Aktivitas Investasi', $this->kasBersihInvestasi];
        $rows[] = ['', ''];

        // 3. Aktivitas Pendanaan
        $rows[] = ['Arus Kas dari Aktivitas Pendanaan', ''];
        $rows[] = ['  Pembayaran/Penerimaan pinjaman', 0];
        $rows[] = ['  Ekuitas/Modal', 0];
        $rows[] = ['Kas Bersih dari Aktivitas Pendanaan', $this->kasBersihPendanaan];
        $rows[] = ['', ''];

        // 4. Rekonsiliasi Saldo Kas
        $rows[] = ['Kenaikan (penurunan) kas', $this->kenaikanKas];
        $rows[] = ['Total revaluasi bank', 0];
        $rows[] = ['Saldo kas awal', $this->saldoKasAwal];
        $rows[] = ['Saldo kas akhir', $this->saldoKasAkhir];

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        // Merge & Center Title Header
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('A4:B4');

        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9);

        // Header Tabel (Row 6)
        $sheet->getStyle('A6:B6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7065A3'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('B6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Subheader Kategori (Row 7, 16, 21)
        foreach ([7, 16, 21] as $row) {
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EBE7F3'],
                ],
            ]);
        }

        // Subtotal Kas Bersih (Row 14, 19, 24)
        foreach ([14, 19, 24] as $row) {
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']],
                ],
            ]);
        }

        // Saldo Kas Akhir (Row 29)
        $sheet->getStyle('A29:B29')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '404040']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '404040']],
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