<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CatatanPelanggaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PelanggaranController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        if (!in_array($user?->role, ['Admin', 'Danru'])) {
            abort(403, 'Akses ditolak. Hanya Admin atau Danru yang dapat menginput catatan pelanggaran.');
        }

        $query = User::where('role', 'Anggota')->where('status_aktif', 1);

        // Danru can only evaluate members of their own regu
        if ($user->role === 'Danru') {
            $query->where('regu', $user->regu);
        }

        $anggota = $query->get(['id_user', 'nama_lengkap', 'regu']);

        return Inertia::render('Pelanggaran/InputPelanggaran', [
            'anggota' => $anggota,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user?->role, ['Admin', 'Danru'])) {
            abort(403, 'Akses ditolak. Hanya Admin atau Danru yang dapat menginput catatan pelanggaran.');
        }

        $validated = $request->validate([
            'id_anggota' => 'required|exists:users,id_user',
            'tanggal_kejadian' => 'required|date',
            'minggu_ke' => 'required|integer|min:1|max:5',
            'bulan' => 'required|string|max:20',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kategori_indikator' => 'required|in:Kedisiplinan,Kehadiran,Kerapihan,Komunikasi',
            'tingkat_pelanggaran' => 'required|in:Ringan,Sedang,Berat',
            'deskripsi_kejadian' => 'required|string',
        ]);

        CatatanPelanggaran::create([
            'id_anggota' => $validated['id_anggota'],
            'id_danru_penilai' => $user->id_user,
            'tanggal_kejadian' => $validated['tanggal_kejadian'],
            'minggu_ke' => $validated['minggu_ke'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'kategori_indikator' => $validated['kategori_indikator'],
            'tingkat_pelanggaran' => $validated['tingkat_pelanggaran'],
            'deskripsi_kejadian' => $validated['deskripsi_kejadian'],
        ]);

        return redirect()->back()->with('success', 'Catatan pelanggaran berhasil disimpan!');
    }
}
