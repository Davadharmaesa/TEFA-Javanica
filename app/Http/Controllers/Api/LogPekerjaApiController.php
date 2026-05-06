<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoastingBatch;
use App\Models\User;
use App\Models\Biaya;
use App\Models\LogPekerja;
use Carbon\Carbon;

class LogPekerjaApiController extends Controller
{
    public function getActiveBatches()
    {
        // Get batches created today
        $today = Carbon::today();
        $batches = RoastingBatch::with(['greenBean'])
            ->whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->get();
            
        $formatted = $batches->map(function($batch) {
            return [
                'id' => $batch->id,
                'kode' => $batch->kode_batch,
                'produk' => $batch->greenBean->nama_biji ?? 'Unknown',
            ];
        });
        
        return response()->json($formatted);
    }

    public function getPekerja()
    {
        // Get users that are not Admin
        $users = User::whereIn('role', ['Operator', 'Mahasiswa Magang', 'Staff'])->get();
        
        $formatted = $users->map(function($user) {
            return [
                'id' => $user->id,
                'nama' => $user->name,
                'role' => $user->role,
            ];
        });

        return response()->json($formatted);
    }

    public function getTugas()
    {
        // Get tasks from Biaya where kategori is Tenaga Kerja
        $tugas = Biaya::where('kategori', 'Tenaga Kerja')->get();
        return response()->json($tugas);
    }

    public function getHistory()
    {
        $today = Carbon::today();
        $logs = LogPekerja::with(['batch', 'batch.greenBean', 'pekerja', 'komponenBiaya'])
            ->whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->get();

        $formatted = $logs->map(function($log) {
            return [
                'id' => $log->id,
                'kode_batch' => $log->batch->kode_batch ?? 'DELETED',
                'produk' => $log->batch->greenBean->nama_biji ?? '',
                'nama_pekerja' => $log->pekerja->name ?? 'Unknown',
                'role_pekerja' => $log->pekerja->role ?? '',
                'tugas' => $log->komponenBiaya->nama ?? 'Unknown',
                'jumlah' => $log->jumlah_kerja,
                'satuan' => $log->komponenBiaya->satuan ?? '',
                'total_upah' => $log->total_upah,
                'waktu' => $log->created_at->format('H:i'),
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:roasting_batch,id',
            'pekerja_id' => 'required|exists:users,id',
            'komponen_id' => 'required|exists:biaya,id',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $tugas = Biaya::findOrFail($validated['komponen_id']);
        $total_upah = $tugas->harga * $validated['jumlah'];

        $log = LogPekerja::create([
            'roasting_batch_id' => $validated['batch_id'],
            'pekerja_id' => $validated['pekerja_id'],
            'biaya_id' => $validated['komponen_id'],
            'jumlah_kerja' => $validated['jumlah'],
            'total_upah' => $total_upah,
        ]);

        return response()->json(['message' => 'Log pekerja berhasil dicatat', 'data' => $log]);
    }

    public function destroy($id)
    {
        $log = LogPekerja::findOrFail($id);
        $log->delete();
        return response()->json(['message' => 'Log pekerja berhasil dihapus']);
    }
}
