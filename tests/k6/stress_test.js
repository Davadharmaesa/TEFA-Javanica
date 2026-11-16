import http from 'k6/http';
import { check, sleep } from 'k6';

// Konfigurasi target URL (Bisa di-override lewat environment variable: k6 run -e TARGET_URL=... stress_test.js)
const BASE_URL = __ENV.TARGET_URL || 'https://tajbi.poliwangi.ac.id/jbi24024';

export const options = {
  // Skenario Stress Test untuk mencari batas maksimal kemampuan kontainer LXC
  stages: [
    { duration: '15s', target: 20 },  // Pemanasan: naik ke 20 pengguna
    { duration: '30s', target: 50 },  // Beban normal-tinggi: 50 pengguna
    { duration: '30s', target: 100 }, // Beban puncak (Spike): 100 pengguna serentak
    { duration: '15s', target: 0 },   // Pendinginan: turun kembali ke 0
  ],

  thresholds: {
    http_req_duration: ['p(95)<3000'], // 95% request di bawah 3 detik
    http_req_failed: ['rate<0.10'],    // Toleransi error di bawah 10%
  },
};

export default function () {
  const res = http.get(`${BASE_URL}/`, {
    headers: {
      'User-Agent': 'k6-stress-test/1.0',
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    },
  });

  check(res, {
    'status is 200': (r) => r.status === 200,
  });

  sleep(1);
}
