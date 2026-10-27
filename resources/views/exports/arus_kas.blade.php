<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<table>
    <!-- Judul Laporan -->
    <tr>
        <th colspan="3" style="text-align: center; font-size: 14pt; font-weight: bold; color: #1F4E78;">
            LAPORAN ARUS KAS (CASH FLOW)
        </th>
    </tr>
    <tr>
        <th colspan="3" style="text-align: center; font-size: 12pt; font-weight: bold; color: #333333;">
            TEFA JAVANICA ROASTERY
        </th>
    </tr>
    <tr>
        <th colspan="3" style="text-align: center; font-size: 10pt; font-style: italic; color: #666666;">
            Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
        </th>
    </tr>
    <tr>
        <th colspan="3" style="text-align: center; font-size: 8pt; color: #888888;">
            (Dibuat pada: {{ now()->translatedFormat('d F Y H:i') }} WIB)
        </th>
    </tr>
    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- Header Kolom Tabel Utama -->
    <tr>
        <th style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: left; border: 1px solid #1F4E78; width: 45px;">
            NAMA AKUN / KETERANGAN
        </th>
        <th style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #1F4E78; width: 25px;">
            SUBTOTAL (RP)
        </th>
        <th style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #1F4E78; width: 25px;">
            TOTAL (RP)
        </th>
    </tr>

    <!-- SEKSI 1: ARUS KAS MASUK (INFLOW) -->
    <tr>
        <td colspan="3" style="background-color: #D9E1F2; font-weight: bold; color: #1F4E78; border-top: 1px solid #B4C6E7; border-bottom: 1px solid #B4C6E7;">
            ARUS KAS MASUK (CASH INFLOW)
        </td>
    </tr>
    @forelse($cashInflowByCategory as $kategori => $amount)
    <tr>
        <td style="padding-left: 20px; border-left: 1px solid #D9D9D9; border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;">
            {{ $kategori }}
        </td>
        <td style="text-align: right; border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;">
            Rp {{ number_format($amount, 0, ',', '.') }}
        </td>
        <td style="border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;"></td>
    </tr>
    @empty
    <tr>
        <td colspan="3" style="font-style: italic; color: #888888; padding-left: 20px; border: 1px solid #D9D9D9;">
            Tidak ada penerimaan kas masuk pada periode ini.
        </td>
    </tr>
    @endforelse
    <!-- Subtotal Kas Masuk -->
    <tr>
        <td style="font-weight: bold; color: #2E75B6; border-top: 1px solid #1F4E78; border-bottom: 1px solid #1F4E78; border-left: 1px solid #D9D9D9; background-color: #F8F9FA;">
            TOTAL ARUS KAS MASUK
        </td>
        <td style="border-top: 1px solid #1F4E78; border-bottom: 1px solid #1F4E78; background-color: #F8F9FA;"></td>
        <td style="text-align: right; font-weight: bold; color: #2E75B6; border-top: 1px solid #1F4E78; border-bottom: 1px solid #1F4E78; border-right: 1px solid #D9D9D9; background-color: #F8F9FA;">
            Rp {{ number_format($totalCashInflow, 0, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- SEKSI 2: ARUS KAS KELUAR (OUTFLOW) -->
    <tr>
        <td colspan="3" style="background-color: #D9E1F2; font-weight: bold; color: #1F4E78; border-top: 1px solid #B4C6E7; border-bottom: 1px solid #B4C6E7;">
            ARUS KAS KELUAR (CASH OUTFLOW)
        </td>
    </tr>
    @forelse($cashOutflowByCategory as $kategori => $amount)
    <tr>
        <td style="padding-left: 20px; border-left: 1px solid #D9D9D9; border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;">
            {{ $kategori }}
        </td>
        <td style="text-align: right; border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;">
            Rp {{ number_format($amount, 0, ',', '.') }}
        </td>
        <td style="border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;"></td>
    </tr>
    @empty
    <tr>
        <td colspan="3" style="font-style: italic; color: #888888; padding-left: 20px; border: 1px solid #D9D9D9;">
            Tidak ada pengeluaran kas pada periode ini.
        </td>
    </tr>
    @endforelse
    <!-- Subtotal Kas Keluar -->
    <tr>
        <td style="font-weight: bold; color: #C00000; border-top: 1px solid #C00000; border-bottom: 1px solid #C00000; border-left: 1px solid #D9D9D9; background-color: #F8F9FA;">
            TOTAL ARUS KAS KELUAR
        </td>
        <td style="border-top: 1px solid #C00000; border-bottom: 1px solid #C00000; background-color: #F8F9FA;"></td>
        <td style="text-align: right; font-weight: bold; color: #C00000; border-top: 1px solid #C00000; border-bottom: 1px solid #C00000; border-right: 1px solid #D9D9D9; background-color: #F8F9FA;">
            Rp {{ number_format($totalCashOutflow, 0, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- BARIS AKHIR: NET CASH FLOW -->
    <tr>
        <td colspan="2" style="font-size: 11pt; font-weight: bold; background-color: {{ $netCashFlow >= 0 ? '#E2EFDA' : '#FCE4D6' }}; color: {{ $netCashFlow >= 0 ? '#385723' : '#C00000' }}; border: 1px solid {{ $netCashFlow >= 0 ? '#A9D18E' : '#F4B084' }};">
            ARUS KAS BERSIH (NET CASH FLOW)
        </td>
        <td style="text-align: right; font-size: 11pt; font-weight: bold; background-color: {{ $netCashFlow >= 0 ? '#E2EFDA' : '#FCE4D6' }}; color: {{ $netCashFlow >= 0 ? '#385723' : '#C00000' }}; border: 1px solid {{ $netCashFlow >= 0 ? '#A9D18E' : '#F4B084' }};">
            Rp {{ number_format($netCashFlow, 0, ',', '.') }}
        </td>
    </tr>

    @if($totalSisaPiutang > 0)
    <tr>
        <td colspan="3"></td>
    </tr>
    <tr>
        <td colspan="3" style="background-color: #FFF2CC; font-weight: bold; color: #B25E00; border: 1px solid #FFE699;">
            CATATAN TAMBAHAN / INFORMASI PIUTANG
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-left: 20px; border-left: 1px solid #D9D9D9; border-bottom: 1px solid #D9D9D9;">
            Sisa Piutang Pelanggan Berjalan (Belum Tertagih Masuk Kas)
        </td>
        <td style="text-align: right; font-weight: bold; color: #B25E00; border-right: 1px solid #D9D9D9; border-bottom: 1px solid #D9D9D9;">
            Rp {{ number_format($totalSisaPiutang, 0, ',', '.') }}
        </td>
    </tr>
    @endif
</table>
</body>
</html>
