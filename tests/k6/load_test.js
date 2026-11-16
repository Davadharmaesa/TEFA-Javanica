import http from 'k6/http';
import { check, sleep } from 'k6';

// Konfigurasi target URL (Bisa di-override lewat environment variable: k6 run -e TARGET_URL=... load_test.js)
const BASE_URL = __ENV.TARGET_URL || 'https://tajbi.poliwangi.ac.id/jbi24024';

export const options = {
  // Skenario pengujian bertahap (Ramping Up - Peak - Cool Down)
  stages: [
    { duration: '10s', target: 10 }, // Ramp-up: naik ke 10 virtual users dalam 10 detik
    { duration: '20s', target: 20 }, // Steady state: bertahan di 20 virtual users selama 20 detik
    { duration: '10s', target: 0 },  // Ramp-down: turun kembali ke 0 user
  ],

  // Batas toleransi (Thresholds) untuk memastikan server sehat
  thresholds: {
    // 95% request harus selesai di bawah 2 detik (2000ms)
    http_req_duration: ['p(95)<2000'],
    // Tingkat kegagalan request harus di bawah 5%
    http_req_failed: ['rate<0.05'],
  },
};

export default function () {
  // 1. Mengunjungi halaman utama / login
  const res = http.get(`${BASE_URL}/`, {
    headers: {
      'User-Agent': 'k6-load-test/1.0',
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    },
  });

  // 2. Validasi respon server
  check(res, {
    'status is 200': (r) => r.status === 200,
    'halaman memuat form login': (r) => r.body && r.body.includes('Sign In'),
  });

  // Jeda acak 1 - 2 detik antar request (mensimulasikan perilaku pengunjung manusia yang wajar)
  sleep(1 + Math.random());
}
