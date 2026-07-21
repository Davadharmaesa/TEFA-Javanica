<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan halaman kelola user.
     */
    public function index()
    {
        $users = User::all();
        
        // Menghitung statistik sederhana
        $totalUsers = $users->count();
        $totalPic = $users->where('role', 'PIC')->count();
        $totalOperator = $users->where('role', 'Operator')->count();
        $totalMagang = $users->where('role', 'Mahasiswa Magang')->count();

        return view('users.index', compact('users', 'totalUsers', 'totalPic', 'totalOperator', 'totalMagang'));
    }

    /**
     * Simpan user baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|string|in:PIC,Operator,Mahasiswa Magang',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User ' . $validated['name'] . ' berhasil ditambahkan!');
    }

    /**
     * Perbarui data user di database.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => 'required|string|in:PIC,Operator,Mahasiswa Magang',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User ' . $user->name . ' berhasil diperbarui!');
    }

    /**
     * Hapus user dari database.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Mencegah user menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak diperbolehkan menghapus akun Anda sendiri yang sedang aktif!');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User ' . $userName . ' berhasil dihapus!');
    }

    /**
     * Get WhatsApp connection status and QR code from Gateway.
     */
    public function getWaStatus()
    {
        $url = config('services.whatsapp.url', 'http://localhost:3000');
        $apiKey = config('services.whatsapp.api_key', '');

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders(array_filter([
                'X-API-KEY' => $apiKey,
                'apikey' => $apiKey,
            ]))->timeout(3)->get("{$url}/status");

            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['success' => false, 'error' => 'Gagal mengambil status dari Gateway'], 502);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Gateway tidak merespon (Offline)'], 504);
        }
    }

    /**
     * Logout WhatsApp session from Gateway.
     */
    public function logoutWa()
    {
        $url = config('services.whatsapp.url', 'http://localhost:3000');
        $apiKey = config('services.whatsapp.api_key', '');

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders(array_filter([
                'X-API-KEY' => $apiKey,
                'apikey' => $apiKey,
            ]))->timeout(5)->post("{$url}/logout");

            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['success' => false, 'error' => 'Gagal melakukan logout pada Gateway'], 502);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Gateway tidak merespon (Offline)'], 504);
        }
    }
}
