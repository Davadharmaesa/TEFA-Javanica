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
            LAPORAN LABA RUGI
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

    <!-- SEKSI 1: PENDAPATAN USAHA -->
    <tr>
        <td colspan="3" style="background-color: #D9E1F2; font-weight: bold; color: #1F4E78; border-top: 1px solid #B4C6E7; border-bottom: 1px solid #B4C6E7;">
            PENDAPATAN USAHA (PENJUALAN)
        </td>
    </tr>
    @forelse($pendapatanByCategory as $kategori => $amount)
    <tr>
        <td style="padding-left: 20px; border-left: 1px solid #D9D9D9; border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;">
            Penjualan {{ $kategori }}
        </td>
        <td style="text-align: right; border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;">
            Rp {{ number_format($amount, 0, ',', '.') }}
        </td>
        <td style="border-right: 1px solid #D9D9D9; border-bottom: 1px solid #F2F2F2;"></td>
    </tr>
    @empty
    <tr>
        <td colspan="3" style="font-style: italic; color: #888888; padding-left: 20px; border: 1px solid #D9D9D9;">
            Tidak ada pendapatan penjualan pada periode ini.
        </td>
    </tr>
    @endforelse
    <!-- Subtotal Pendapatan -->
    <tr>
        <td style="font-weight: bold; color: #2E75B6; border-top: 1px solid #1F4E78; border-bottom: 1px solid #1F4E78; border-left: 1px solid #D9D9D9; background-color: #F8F9FA;">
            TOTAL PENDAPATAN USAHA
        </td>
        <td style="border-top: 1px solid #1F4E78; border-bottom: 1px solid #1F4E78; background-color: #F8F9FA;"></td>
        <td style="text-align: right; font-weight: bold; color: #2E75B6; border-top: 1px solid #1F4E78; border-bottom: 1px solid #1F4E78; border-right: 1px solid #D9D9D9; background-color: #F8F9FA;">
            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- SEKSI 2: BEBAN POKOK PRODUKSI & BIAYA OPERASIONAL -->
    <tr>
        <td colspan="3" style="background-color: #D9E1F2; font-weight: bold; color: #1F4E78; border-top: 1px solid #B4C6E7; border-bottom: 1px solid #B4C6E7;">
            BEBAN POKOK PRODUKSI &amp; BIAYA OPERASIONAL
        </td>
    </tr>
    @forelse($bebanLabaRugiByCategory as $kategori => $amount)
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
            Tidak ada beban operasional pada periode ini.
        </td>
    </tr>
    @endforelse
    <!-- Subtotal Beban -->
    <tr>
        <td style="font-weight: bold; color: #C00000; border-top: 1px solid #C00000; border-bottom: 1px solid #C00000; border-left: 1px solid #D9D9D9; background-color: #F8F9FA;">
            TOTAL BEBAN POKOK &amp; OPERASIONAL
        </td>
        <td style="border-top: 1px solid #C00000; border-bottom: 1px solid #C00000; background-color: #F8F9FA;"></td>
        <td style="text-align: right; font-weight: bold; color: #C00000; border-top: 1px solid #C00000; border-bottom: 1px solid #C00000; border-right: 1px solid #D9D9D9; background-color: #F8F9FA;">
            Rp {{ number_format($totalBeban, 0, ',', '.') }}
        </td>
    </tr>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- BARIS AKHIR: LABA / (RUGI) BERSIH -->
    <tr>
        <td colspan="2" style="font-size: 11pt; font-weight: bold; background-color: {{ $labaBersih >= 0 ? '#E2EFDA' : '#FCE4D6' }}; color: {{ $labaBersih >= 0 ? '#385723' : '#C00000' }}; border: 1px solid {{ $labaBersih >= 0 ? '#A9D18E' : '#F4B084' }};">
            LABA / (RUGI) BERSIH OPERASIONAL
        </td>
        <td style="text-align: right; font-size: 11pt; font-weight: bold; background-color: {{ $labaBersih >= 0 ? '#E2EFDA' : '#FCE4D6' }}; color: {{ $labaBersih >= 0 ? '#385723' : '#C00000' }}; border: 1px solid {{ $labaBersih >= 0 ? '#A9D18E' : '#F4B084' }};">
            Rp {{ number_format($labaBersih, 0, ',', '.') }}
        </td>
    </tr>
</table>
</body>
</html>
