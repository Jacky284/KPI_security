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
        if (Auth::user()?->role !== 'Admin') {
            abort(403, 'Akses ditolak. Hanya administrator yang dapat mengakses halaman ini.');
        }
    }

    public function index()
    {
        $this->checkAdmin();

        $users = User::orderBy('created_at', 'desc')->get();
        return Inertia::render('Admin/UserManagement', [
            'users' => $users,
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
        ]);

        User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'role' => $validated['role'],
            'regu' => ($validated['role'] === 'Danru' || $validated['role'] === 'Anggota') ? $validated['regu'] : null,
            'status_aktif' => 1,
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'User berhasil didaftarkan!');
    }

    public function toggleStatus($id)
    {
        $this->checkAdmin();

        $user = User::findOrFail($id);
        $user->status_aktif = !$user->status_aktif;
        $user->save();

        return redirect()->back()->with('success', 'Status user berhasil diperbarui!');
    }
}
