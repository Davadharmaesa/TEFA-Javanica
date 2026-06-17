import crypto from 'crypto';
if (!globalThis.crypto) {
    globalThis.crypto = crypto;
}

import qrcode from 'qrcode-terminal';
import http from 'http';
import pino from 'pino';
import path from 'path';
import { fileURLToPath } from 'url';

// Impor Baileys secara dinamis setelah mendefinisikan globalThis.crypto untuk Node.js versi lama (< 20)
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason } = await import('@whiskeysockets/baileys');

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = 3000;
let sock = null;
let isConnected = false;

async function connectToWhatsApp() {
    // Simpan data login di folder 'baileys_auth_info' agar tidak perlu scan ulang setiap dijalankan
    const authPath = path.join(__dirname, 'baileys_auth_info');
    const { state, saveCreds } = await useMultiFileAuthState(authPath);

    console.log('[INFO] Menghubungkan ke WhatsApp...');
    sock = makeWASocket.default ? makeWASocket.default({
        auth: state,
        logger: pino.default({ level: 'silent' }), // Menonaktifkan log bawaan baileys agar tampilan bersih
        printQRInTerminal: false // Kita gunakan qrcode-terminal sendiri untuk kontrol ukuran
    }) : makeWASocket({
        auth: state,
        logger: pino.default ? pino.default({ level: 'silent' }) : pino({ level: 'silent' }),
        printQRInTerminal: false
    });

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log('\n==================================================');
            console.log('[INFO] PINDAI QR CODE BERIKUT DENGAN WHATSAPP HP ANDA:');
            console.log('==================================================\n');
            qrcode.generate(qr, { small: false });
        }

        if (connection === 'close') {
            isConnected = false;
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log(`[WARNING] Koneksi terputus. Reconnecting: ${shouldReconnect}`);
            if (shouldReconnect) {
                connectToWhatsApp();
            } else {
                console.log('[ERROR] Sesi terputus/Log Out. Silakan hapus folder "baileys_auth_info" dan jalankan ulang.');
            }
        } else if (connection === 'open') {
            isConnected = true;
            console.log('\n==================================================');
            console.log('[SUCCESS] WhatsApp Berhasil Terhubung!');
            console.log('==================================================\n');
        }
    });

    sock.ev.on('creds.update', saveCreds);
}

// HTTP API Server
const server = http.createServer((req, res) => {
    // Enable CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, X-API-KEY, apikey');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    if (req.url === '/send-message' && req.method === 'POST') {
        let body = '';
        req.on('data', chunk => {
            body += chunk.toString();
        });
        req.on('end', async () => {
            try {
                const data = JSON.parse(body);

                if (!data.number || !data.message) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: false, error: 'Nomor dan pesan wajib diisi!' }));
                    return;
                }

                if (!sock || !isConnected) {
                    res.writeHead(503, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: false, error: 'WhatsApp Gateway belum online/terhubung!' }));
                    return;
                }

                // Format nomor HP agar sesuai JID WhatsApp (e.g. 62823xxx@s.whatsapp.net)
                let cleanNumber = data.number.replace(/[^0-9]/g, '');
                if (cleanNumber.startsWith('0')) {
                    cleanNumber = '62' + cleanNumber.substring(1);
                }
                const jid = `${cleanNumber}@s.whatsapp.net`;

                console.log(`[SENDING] Mengirim pesan ke ${jid}...`);
                await sock.sendMessage(jid, { text: data.message });
                console.log(`[SUCCESS] Pesan berhasil dikirim ke ${jid}`);

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, message: 'Pesan terkirim!' }));
            } catch (err) {
                console.error('[ERROR]', err);
                res.writeHead(500, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: false, error: err.message }));
            }
        });
    } else {
        res.writeHead(404, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: 'Endpoint tidak ditemukan' }));
    }
});

server.listen(PORT, () => {
    console.log(`\n[INFO] Gateway WA Baileys berjalan di port: ${PORT}`);
    connectToWhatsApp();
});
