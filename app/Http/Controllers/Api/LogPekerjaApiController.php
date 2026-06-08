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
        // Get the latest 50 batches to allow logging older/recent batches
        $batches = RoastingBatch::with(['greenBean'])
            ->orderBy('id', 'desc')
            ->take(50)
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
        // Get the latest 20 logs to allow tracking recent history
        $logs = LogPekerja::with(['batch', 'batch.greenBean', 'komponenBiaya'])
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        $formatted = $logs->map(function($log) {
            return [
                'id' => $log->id,
                'batch_id' => $log->roasting_batch_id,
                'nama_pekerja' => $log->nama_pekerja,
                'komponen_id' => $log->biaya_id,
                'kode_batch' => $log->batch->kode_batch ?? 'DELETED',
                'produk' => $log->batch->greenBean->nama_biji ?? '',
                'role_pekerja' => 'Pekerja Manual',
                'tugas' => $log->komponenBiaya->nama ?? 'Unknown',
                'jumlah' => $log->jumlah_kerja,
                'satuan' => $log->komponenBiaya->satuan ?? '',
                'total_upah' => $log->total_upah,
                'waktu' => $log->created_at->format('d/m H:i'),
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:roasting_batch,id',
            'nama_pekerja' => 'required|string|max:255',
            'komponen_id' => 'required|exists:biaya,id',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $tugas = Biaya::findOrFail($validated['komponen_id']);
        $total_upah = $tugas->harga * $validated['jumlah'];

        $log = LogPekerja::create([
            'roasting_batch_id' => $validated['batch_id'],
            'nama_pekerja' => $validated['nama_pekerja'],
            'biaya_id' => $validated['komponen_id'],
            'jumlah_kerja' => $validated['jumlah'],
            'total_upah' => $total_upah,
        ]);

        return response()->json(['message' => 'Log pekerja berhasil dicatat', 'data' => $log]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:roasting_batch,id',
            'nama_pekerja' => 'required|string|max:255',
            'komponen_id' => 'required|exists:biaya,id',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $log = LogPekerja::findOrFail($id);
        $tugas = Biaya::findOrFail($validated['komponen_id']);
        $total_upah = $tugas->harga * $validated['jumlah'];

        $log->update([
            'roasting_batch_id' => $validated['batch_id'],
            'nama_pekerja' => $validated['nama_pekerja'],
            'biaya_id' => $validated['komponen_id'],
            'jumlah_kerja' => $validated['jumlah'],
            'total_upah' => $total_upah,
        ]);

        return response()->json(['message' => 'Log pekerja berhasil diperbarui', 'data' => $log]);
    }

    public function destroy($id)
    {
        $log = LogPekerja::findOrFail($id);
        $log->delete();
        return response()->json(['message' => 'Log pekerja berhasil dihapus']);
    }
}
