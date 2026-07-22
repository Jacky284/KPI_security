<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    private function checkAdmin()
    {
        if (!in_array(Auth::user()?->role, ['Admin', 'Chief'])) {
            abort(403, 'Akses ditolak. Hanya administrator dan chief yang dapat mengakses halaman ini.');
        }
    }

    public function index()
    {
        $this->checkAdmin();

        $users = User::orderBy('regu', 'asc')
            ->orderByRaw("CASE WHEN role = 'Danru' THEN 1 WHEN role = 'Anggota' THEN 2 ELSE 3 END")
            ->orderBy('nama_lengkap', 'asc')
            ->get();
        $regus = \App\Models\Regu::orderBy('nama_regu', 'asc')->get();
        
        return Inertia::render('Admin/UserManagement', [
            'users' => $users,
            'regus' => $regus,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'role' => 'required|in:Admin,Danru,Chief,Klien,Anggota',
            'regu' => 'nullable|string|max:50',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'sisa_cuti' => 'nullable|integer|min:0',
        ]);

        User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'role' => $validated['role'],
            'regu' => ($validated['role'] === 'Danru' || $validated['role'] === 'Anggota') ? $validated['regu'] : null,
            'status_aktif' => 1,
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'sisa_cuti' => $validated['sisa_cuti'] ?? 12,
        ]);

        return redirect()->back()->with('success', 'User berhasil didaftarkan!');
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'role' => 'required|in:Admin,Danru,Chief,Klien,Anggota',
            'regu' => 'nullable|string|max:50',
            'username' => 'required|string|max:50|unique:users,username,'.$id.',id_user',
            'password' => 'nullable|string|min:6',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'sisa_cuti' => 'nullable|integer|min:0',
        ]);

        $user->nama_lengkap = $validated['nama_lengkap'];
        $user->role = $validated['role'];
        $user->regu = ($validated['role'] === 'Danru' || $validated['role'] === 'Anggota') ? $validated['regu'] : null;
        $user->username = $validated['username'];
        $user->tempat_lahir = $validated['tempat_lahir'] ?? null;
        $user->tanggal_lahir = $validated['tanggal_lahir'] ?? null;
        if (isset($validated['sisa_cuti'])) {
            $user->sisa_cuti = $validated['sisa_cuti'];
        }
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Data user berhasil diperbarui!');
    }

    public function toggleStatus($id)
    {
        $this->checkAdmin();

        $user = User::findOrFail($id);
        $user->status_aktif = !$user->status_aktif;
        $user->save();

        return redirect()->back()->with('success', 'Status user berhasil diperbarui!');
    }

    public function storeRegu(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'nama_regu' => 'required|string|max:50|unique:regus,nama_regu',
        ]);

        \App\Models\Regu::create([
            'nama_regu' => $validated['nama_regu']
        ]);

        return redirect()->back()->with('success', 'Regu berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $this->checkAdmin();

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }
}
