<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Query active security officers (Anggota)
        $query = User::where('role', 'Anggota')->where('status_aktif', 1);

        // If Danru, only load members of their own regu
        if ($user->role === 'Danru') {
            $query->where('regu', $user->regu);
        }

        $anggota = $query->orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Dashboard/Index', [
            'anggota' => $anggota,
            'currentUser' => [
                'nama_lengkap' => $user->nama_lengkap,
                'role' => $user->role,
                'regu' => $user->regu,
            ],
        ]);
    }
}
