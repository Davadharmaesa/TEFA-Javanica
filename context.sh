#!/usr/bin/env bash
# ============================================================
# context.sh — Konteks Proyek TEFA-Javanica
# Dibaca ulang setiap kali dijalankan: semakin banyak kode
# yang berubah, semakin akurat informasinya.
# Jalankan: bash context.sh
# ============================================================

# --- Warna ANSI ---
BOLD="\e[1m"
CYAN="\e[36m"
GREEN="\e[32m"
YELLOW="\e[33m"
MAGENTA="\e[35m"
BLUE="\e[34m"
RED="\e[31m"
DIM="\e[2m"
RESET="\e[0m"

# --- Deteksi root proyek ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR" || exit 1

# --- Buat output ke file sementara agar bisa dibuka less ---
TMP_FILE=$(mktemp /tmp/javanica-context-XXXXXX.txt)

{
  echo ""
  echo "╔══════════════════════════════════════════════════════════════════╗"
  echo "║          🌿  TEFA-Javanica — Konteks Proyek Lengkap             ║"
  echo "║    Dibaca ulang otomatis setiap kali dijalankan                 ║"
  echo "╚══════════════════════════════════════════════════════════════════╝"
  echo ""
  echo "  Dibangkitkan pada  : $(date '+%A, %d %B %Y — %H:%M:%S WIB')"
  echo "  Lokasi proyek      : $SCRIPT_DIR"
  echo "  Branch aktif       : $(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo 'tidak ada git')"
  echo "  Commit terakhir    : $(git log -1 --format='%h — %s' 2>/dev/null || echo '-')"
  echo "  Total commit       : $(git rev-list --count HEAD 2>/dev/null || echo '0') commit"
  echo "  Kontributor aktif  : $(git shortlog -sn 2>/dev/null | awk '{sum += $1} END {print sum}') kontribusi dari $(git shortlog -s 2>/dev/null | wc -l) orang"
  echo ""

  # ── BAGIAN 1: RINGKASAN COMMIT ──────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  📜  RIWAYAT COMMIT (50 terakhir)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  git log --pretty=format:"  %C(yellow)%h%Creset  %C(cyan)%-12ar%Creset  %C(green)%-15an%Creset  %s" -50 2>/dev/null || echo "  (tidak ada git history)"
  echo ""
  echo ""

  # ── BAGIAN 2: STATISTIK FILE ──────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  📁  STATISTIK FILE PROYEK"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "  PHP Controllers  : $(find app/Http/Controllers -name "*.php" 2>/dev/null | wc -l) file"
  echo "  PHP Models       : $(find app/Models -name "*.php" 2>/dev/null | wc -l) file"
  echo "  PHP Services     : $(find app/Services -name "*.php" 2>/dev/null | wc -l) file"
  echo "  Blade Views      : $(find resources/views -name "*.blade.php" 2>/dev/null | wc -l) file"
  echo "  Database Migrate : $(find database/migrations -name "*.php" 2>/dev/null | wc -l) file"
  echo "  Routes           : $(find routes -name "*.php" 2>/dev/null | wc -l) file"
  echo "  Total PHP        : $(find . -name "*.php" -not -path "./vendor/*" 2>/dev/null | wc -l) file"
  echo "  Total Baris Kode : $(find . -name "*.php" -not -path "./vendor/*" -exec cat {} \; 2>/dev/null | wc -l) baris"
  echo ""

  # ── BAGIAN 3: DAFTAR MODEL ──────────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  🗂️  MODEL ELOQUENT (app/Models)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  find app/Models -name "*.php" 2>/dev/null | sort | while read -r f; do
    fname=$(basename "$f" .php)
    table=$(grep -oP "protected \\\$table = '\K[^']+" "$f" 2>/dev/null || echo "(auto)")
    fillable=$(grep -oP "protected \\\$fillable = \[.*?\]" "$f" 2>/dev/null | head -1 | sed "s/protected \\\$fillable = //")
    echo "  • ${fname}"
    [ -n "$table" ] && echo "      table    : $table"
    [ -n "$fillable" ] && echo "      fillable : $fillable"
    echo ""
  done

  # ── BAGIAN 4: DAFTAR CONTROLLER ─────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  ⚙️  HTTP CONTROLLERS (app/Http/Controllers)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  find app/Http/Controllers -name "*.php" 2>/dev/null | sort | while read -r f; do
    fname=$(basename "$f" .php)
    methods=$(grep -oP "public function \K\w+" "$f" 2>/dev/null | tr '\n' ', ' | sed 's/, $//')
    echo "  • $fname"
    [ -n "$methods" ] && echo "      methods: $methods"
    echo ""
  done

  # ── BAGIAN 5: ROUTE SUMMARY ─────────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  🛣️  ROUTES (routes/web.php & api.php)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  for route_file in routes/web.php routes/api.php; do
    if [ -f "$route_file" ]; then
      echo "  ── $route_file ──"
      grep -oP "Route::\w+\(.*?\)" "$route_file" 2>/dev/null | head -30 | sed 's/^/    /'
      echo ""
    fi
  done

  # ── BAGIAN 6: PERUBAHAN TERBARU ─────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  🔥  FILE YANG BERUBAH PALING BANYAK (hotspot)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  git log --name-only --pretty=format: 2>/dev/null \
    | grep "\.php$" \
    | grep -v "^vendor/" \
    | sort | uniq -c | sort -rn | head -15 \
    | awk '{printf "  %3d perubahan  →  %s\n", $1, $2}'
  echo ""

  # ── BAGIAN 7: KONTRIBUTOR ───────────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  👥  KONTRIBUTOR"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  git shortlog -sne 2>/dev/null | awk '{printf "  %4d commit  →  %s\n", $1, substr($0, index($0,$2))}'
  echo ""

  # ── BAGIAN 8: STATUS GIT SEKARANG ───────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  📌  STATUS WORKING TREE SEKARANG"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  git_status=$(git status --short 2>/dev/null)
  if [ -z "$git_status" ]; then
    echo "  ✅  Working tree bersih — tidak ada perubahan."
  else
    echo "$git_status" | sed 's/^/  /'
  fi
  echo ""

  # ── BAGIAN 9: DIFF TERBARU ──────────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  🔍  DIFF COMMIT TERAKHIR (ringkasan)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  git diff --stat HEAD~1 HEAD 2>/dev/null | sed 's/^/  /' || echo "  (hanya 1 commit tersedia)"
  echo ""

  # ── BAGIAN 10: COMPOSE & ENV ────────────────────────────────────────────
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  🐳  DOCKER / COMPOSE"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  if [ -f "compose.yaml" ]; then
    grep -E "(image:|ports:|volumes:|container_name:)" compose.yaml 2>/dev/null | sed 's/^/  /'
  else
    echo "  (tidak ada compose.yaml)"
  fi
  echo ""

  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  💡  CARA PAKAI"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "  bash context.sh          → jalankan & buka di pager (less)"
  echo "  bash context.sh > out.txt → simpan ke file teks"
  echo "  Tekan  q                  → keluar dari pager"
  echo "  Tekan  /kata              → cari kata dalam pager"
  echo "  Tekan  G                  → lompat ke bawah"
  echo "  Tekan  g                  → lompat ke atas"
  echo ""
  echo "  File ini dibaca ulang dari awal setiap kali dijalankan."
  echo "  Semakin banyak commit & kode → semakin detail outputnya."
  echo ""
  echo "══════════════════════════════════════════════════════════════════════"
  echo "  Selesai — $(git rev-list --count HEAD 2>/dev/null) commit terbaca, $(find . -name '*.php' -not -path './vendor/*' 2>/dev/null | wc -l) file PHP dianalisis."
  echo "══════════════════════════════════════════════════════════════════════"
  echo ""

} > "$TMP_FILE"

# Buka dengan `less` agar output tidak hilang dan bisa di-scroll
less -R "$TMP_FILE"

# Hapus file temp setelah ditutup
rm -f "$TMP_FILE"
