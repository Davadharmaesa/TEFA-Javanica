<?php

namespace App\Http\Controllers;

use App\Models\LogPekerja;
use App\Models\GreenBeanStock;
use App\Models\PesananKeluar;
use App\Models\HppCalculation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Exports\LaporanKeuanganExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        // Default ke awal dan akhir bulan berjalan jika tidak ada filter
        if (!$start || !$end) {
            $start = now()->startOfMonth()->toDateString();
            $end = now()->endOfMonth()->toDateString();
        }

        $data = $this->calculateFinancials($start, $end);

        return view('reports.index', $data);
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date', now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', now()->endOfMonth()->toDateString());

        $data = $this->calculateFinancials($start, $end);

        $filename = "Laporan_Keuangan_{$start}_{$end}.xlsx";

        return Excel::download(
            new LaporanKeuanganExport($data),
            $filename
        );
    }

    private function calculateFinancials(string $start, string $end): array
    {
        // -------------------------------------------------------------
        // 1. DATA TRANSAKSI PENJUALAN PERIODE INI
        // -------------------------------------------------------------
        $transactions = PesananKeluar::where(function ($q) use ($start, $end) {
            $q->whereBetween('tanggal_pesanan', [$start, $end])
              ->orWhere(function ($sub) use ($start, $end) {
                  $sub->whereNull('tanggal_pesanan')
                      ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
              });
        });

        // Akrual: Total seluruh transaksi penjualan di periode ini
        $penjualanKotor = (float) (clone $transactions)->sum('total_pembayaran');
        // Kas: Penjualan yang sudah lunas dan DP yang diterima
        $totalLunas = (clone $transactions)->where('pesanan_keluar.status_pembayaran', 'lunas')->sum('pesanan_keluar.total_pembayaran');

        $totalDP = 0;
        if (Schema::hasColumn('pesanan_keluar', 'metode_pembayaran') && Schema::hasColumn('pesanan_keluar', 'nominal_dp')) {
            $totalDP = (clone $transactions)->where('metode_pembayaran', 'dp')->where('status_pembayaran', '!=', 'lunas')->sum('nominal_dp');
        }

        $totalSisaPiutang = 0;
        if (Schema::hasColumn('pesanan_keluar', 'sisa_pembayaran')) {
            $totalSisaPiutang = (clone $transactions)->sum('sisa_pembayaran');
        }

        // -------------------------------------------------------------
        // 2. DATA BIAYA BAHAN BAKU & OPERASIONAL PERIODE INI
        // -------------------------------------------------------------
        // Bahan baku Green Bean
        $greenBeanPurchases = GreenBeanStock::where(function ($q) use ($start, $end) {
            $q->whereBetween('tanggal_beli', [$start, $end])
              ->orWhere(function ($sub) use ($start, $end) {
                  $sub->whereNull('tanggal_beli')
                      ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
              });
        })->get();

        $totalBelanjaGB = 0;
        foreach ($greenBeanPurchases as $gb) {
            $totalBelanjaGB += ($gb->total_harga > 0) ? (float) $gb->total_harga : (($gb->stok_gram / 1000) * $gb->harga_beli);
        }

        // Data kalkulasi HPP dari batch roasting yang selesai pada periode ini
        $hpps = HppCalculation::whereHas('roastingBatch', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal_roasting', [$start, $end]);
        })->get();

        $totalKemasan = (float) $hpps->sum('biaya_kemasan');
        $totalListrik = (float) $hpps->sum('biaya_listrik');
        $totalPenyusutan = (float) $hpps->sum('biaya_penyusutan');

        // Upah Tenaga Kerja dari Log Pekerja
        $totalUpah = (float) LogPekerja::where(function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
              ->orWhereHas('batch', function ($b) use ($start, $end) {
                  $b->whereBetween('tanggal_roasting', [$start, $end]);
              });
        })->sum('total_upah');

        // =============================================================
        // 3. LAPORAN LABA & RUGI (STANDAR AKUNTANSI - BASIS AKRUAL)
        // =============================================================
        // 3.1 Pendapatan Penjualan
        $returPenjualan = 0;
        $diskonPenjualan = 0;
        $potonganPenjualan = $returPenjualan + $diskonPenjualan;
        $penjualanBersih = $penjualanKotor - $potonganPenjualan;

        // 3.2 Harga Pokok Penjualan (HPP)
        $hppBahanBaku = (float) $totalBelanjaGB;
        $hppKemasan = (float) $totalKemasan;
        $totalHpp = $hppBahanBaku + $hppKemasan;

        // 3.3 Laba Kotor
        $labaKotor = $penjualanBersih - $totalHpp;

        // 3.4 Beban Operasi
        $bebanGaji = (float) $totalUpah;
        $totalBebanPenjualan = $bebanGaji;

        $bebanListrik = (float) $totalListrik;
        $bebanPenyusutan = (float) $totalPenyusutan; // Penyusutan masuk di Laba Rugi
        $totalBebanAdministrasi = $bebanListrik + $bebanPenyusutan;

        $jumlahBebanOperasi = $totalBebanPenjualan + $totalBebanAdministrasi;

        // 3.5 Laba Bersih Operasional
        $labaBersihOperasional = $labaKotor - $jumlahBebanOperasi;

        // 3.6 Pendapatan & Beban Non-Operasi
        $pendapatanNonOperasi = 0;
        $bebanNonOperasi = 0;
        $totalNonOperasi = $pendapatanNonOperasi - $bebanNonOperasi;

        // 3.7 Laba Bersih
        $labaBersih = $labaBersihOperasional + $totalNonOperasi;

        // =============================================================
        // 4. LAPORAN ARUS KAS (STANDAR AKUNTANSI - BASIS KAS LANGSUNG)
        // =============================================================
        $penerimaanPelanggan = (float) ($totalLunas + $totalDP);
        $pembayaranPemasok = (float) ($totalBelanjaGB + $totalKemasan);
        $pengeluaranOperasional = (float) ($totalUpah + $totalListrik); // Tanpa penyusutan (beban non-kas)

        $kasBersihOperasional = $penerimaanPelanggan - $pembayaranPemasok - $pengeluaranOperasional;
        $kasBersihInvestasi = 0;
        $kasBersihPendanaan = 0;
        $kenaikanKas = $kasBersihOperasional + $kasBersihInvestasi + $kasBersihPendanaan;
        $totalRevaluasiBank = 0;

        // Saldo Kas Awal: Akumulasi kas sebelum tanggal $start
        $priorTransactions = PesananKeluar::where(function ($q) use ($start) {
            $q->where('tanggal_pesanan', '<', $start)
              ->orWhere(function ($sub) use ($start) {
                  $sub->whereNull('tanggal_pesanan')
                      ->where('created_at', '<', $start . ' 00:00:00');
              });
        });
        $priorLunas = (clone $priorTransactions)->where('status_pembayaran', 'lunas')->sum('total_pembayaran');
        $priorDP = 0;
        if (Schema::hasColumn('pesanan_keluar', 'metode_pembayaran') && Schema::hasColumn('pesanan_keluar', 'nominal_dp')) {
            $priorDP = (clone $priorTransactions)->where('metode_pembayaran', 'dp')->where('status_pembayaran', '!=', 'lunas')->sum('nominal_dp');
        }
        $priorInflow = (float) ($priorLunas + $priorDP);

        $priorGB = GreenBeanStock::where(function ($q) use ($start) {
            $q->where('tanggal_beli', '<', $start)
              ->orWhere(function ($sub) use ($start) {
                  $sub->whereNull('tanggal_beli')
                      ->where('created_at', '<', $start . ' 00:00:00');
              });
        })->get();
        $priorBelanjaGB = 0;
        foreach ($priorGB as $gb) {
            $priorBelanjaGB += ($gb->total_harga > 0) ? (float) $gb->total_harga : (($gb->stok_gram / 1000) * $gb->harga_beli);
        }

        $priorUpah = (float) LogPekerja::where(function ($q) use ($start) {
            $q->where('created_at', '<', $start . ' 00:00:00')
              ->orWhereHas('batch', function ($b) use ($start) {
                  $b->where('tanggal_roasting', '<', $start);
              });
        })->sum('total_upah');

        $priorHpps = HppCalculation::whereHas('roastingBatch', function ($q) use ($start) {
            $q->where('tanggal_roasting', '<', $start);
        })->get();
        $priorListrik = (float) $priorHpps->sum('biaya_listrik');
        $priorKemasan = (float) $priorHpps->sum('biaya_kemasan');

        $priorOutflow = (float) ($priorBelanjaGB + $priorUpah + $priorListrik + $priorKemasan);
        $saldoKasAwal = $priorInflow - $priorOutflow;
        $saldoKasAkhir = $saldoKasAwal + $kenaikanKas + $totalRevaluasiBank;

        // Rincian Akun
        $detailOperasional = [
            'penerimaan_pelanggan' => [
                'total' => $penerimaanPelanggan,
                'rincian' => [
                    'Penjualan Lunas' => $totalLunas,
                    'Pembayaran Uang Muka (DP)' => $totalDP,
                ]
            ],
            'pembayaran_pemasok' => [
                'total' => $pembayaranPemasok,
                'rincian' => [
                    'Belanja Bahan Baku (Green Bean)' => $totalBelanjaGB,
                    'Biaya Kemasan (Packaging)' => $totalKemasan,
                ]
            ],
            'pengeluaran_operasional' => [
                'total' => $pengeluaranOperasional,
                'rincian' => [
                    'Upah Tenaga Kerja' => $totalUpah,
                    'Biaya Listrik Operasional' => $totalListrik,
                ]
            ],
        ];

        return [
            'start' => $start,
            'end' => $end,
            'totalLunas' => $totalLunas,
            'totalDP' => $totalDP,
            'totalSisaPiutang' => $totalSisaPiutang,

            // Variabel Laba Rugi
            'penjualanKotor' => $penjualanKotor,
            'returPenjualan' => $returPenjualan,
            'diskonPenjualan' => $diskonPenjualan,
            'potonganPenjualan' => $potonganPenjualan,
            'penjualanBersih' => $penjualanBersih,
            'hppBahanBaku' => $hppBahanBaku,
            'hppKemasan' => $hppKemasan,
            'totalHpp' => $totalHpp,
            'labaKotor' => $labaKotor,
            'bebanGaji' => $bebanGaji,
            'totalBebanPenjualan' => $totalBebanPenjualan,
            'bebanListrik' => $bebanListrik,
            'bebanPenyusutan' => $bebanPenyusutan,
            'totalBebanAdministrasi' => $totalBebanAdministrasi,
            'jumlahBebanOperasi' => $jumlahBebanOperasi,
            'labaBersihOperasional' => $labaBersihOperasional,
            'pendapatanNonOperasi' => $pendapatanNonOperasi,
            'bebanNonOperasi' => $bebanNonOperasi,
            'totalNonOperasi' => $totalNonOperasi,
            'labaBersih' => $labaBersih,

            // Variabel Arus Kas
            'penerimaanPelanggan' => $penerimaanPelanggan,
            'asetLancarLainnya' => 0,
            'pembayaranPemasok' => $pembayaranPemasok,
            'kartuKreditLiabilitas' => 0,
            'pendapatanLainnya' => 0,
            'pengeluaranOperasional' => $pengeluaranOperasional,
            'kasBersihOperasional' => $kasBersihOperasional,
            'perolehanAset' => 0,
            'investasiLainnya' => 0,
            'kasBersihInvestasi' => 0,
            'pinjaman' => 0,
            'ekuitasModal' => 0,
            'kasBersihPendanaan' => 0,
            'kenaikanKas' => $kenaikanKas,
            'totalRevaluasiBank' => 0,
            'saldoKasAwal' => $saldoKasAwal,
            'saldoKasAkhir' => $saldoKasAkhir,
            'detailOperasional' => $detailOperasional,
        ];
    }
}
