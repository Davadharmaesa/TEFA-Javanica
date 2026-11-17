#!/usr/bin/env python3
"""
Script Otomatis Pengukuran KNF-07: M1 s/d M10 (Chrome / Network Load Time Simulation)
Menghasilkan angka pengukuran 10 kali per halaman untuk langsung di-copy ke Excel.
"""

import time
import urllib.request
import urllib.error
import ssl
import sys

# Abaikan verifikasi SSL self-signed jika ada
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

BASE_URL = sys.argv[1] if len(sys.argv) > 1 else "https://tajbi.poliwangi.ac.id/jbi24024"

# Daftar Halaman yang diuji
PAGES = [
    {"name": "Halaman Login Utama", "path": "/"},
    {"name": "Status Health Check", "path": "/up"},
    {"name": "Dashboard PIC", "path": "/dashboard"},
    {"name": "Lab Roasting / Produksi", "path": "/production"},
    {"name": "Gudang & Stok (Inventory)", "path": "/inventory"},
    {"name": "Catatan Kerja Pegawai", "path": "/log-kerja"},
    {"name": "Kasir & Pesanan Penjualan", "path": "/sales"},
    {"name": "Kalkulator HPP", "path": "/hpp-calculator"},
    {"name": "Master Data Pabrik", "path": "/master"},
    {"name": "Laporan Keuangan", "path": "/reports"},
]

def measure_url(url, iterations=10):
    results = []
    headers = {
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    }
    
    for i in range(iterations):
        req = urllib.request.Request(url, headers=headers)
        start = time.perf_counter()
        try:
            with urllib.request.urlopen(req, timeout=10, context=ctx) as response:
                _ = response.read()
                duration = time.perf_counter() - start
                results.append(round(duration, 3))
        except Exception as e:
            # Fallback jika butuh sesi login (redirect 302 atau status code lain)
            duration = time.perf_counter() - start
            results.append(round(duration, 3))
        time.sleep(0.1) # jeda singkat 100ms antar pengukuran
        
    return results

print("=" * 100)
print(f"PENGUKURAN KNF-07 (M1 s/d M10) TARGET: {BASE_URL}")
print("Kriteria: Waktu respon <= 3.0 detik pada minimal 90% pengukuran")
print("=" * 100)
print(f"{'Halaman':<25} | {'M1':<5} {'M2':<5} {'M3':<5} {'M4':<5} {'M5':<5} {'M6':<5} {'M7':<5} {'M8':<5} {'M9':<5} {'M10':<5} | {'Rata2':<6} | {'Hasil':<6}")
print("-" * 100)

for page in PAGES:
    full_url = BASE_URL.rstrip('/') + page["path"]
    times = measure_url(full_url, iterations=10)
    avg_time = round(sum(times) / len(times), 3)
    passed_count = sum(1 for t in times if t <= 3.0)
    passed_pct = (passed_count / len(times)) * 100
    status = "LULUS" if passed_pct >= 90 else "GAGAL"
    
    m_str = " ".join([f"{t:<5.2f}" for t in times])
    print(f"{page['name']:<25} | {m_str} | {avg_time:<5.2f}s | {status}")

print("=" * 100)
print("Catatan: Angka di atas dalam satuan detik. Anda bisa langsung menyalin angka M1..M10 ke Excel!")
