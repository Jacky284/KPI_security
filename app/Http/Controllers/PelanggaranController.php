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
        if (!in_array($user?->role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak. Hanya Admin, Chief, atau Danru yang dapat menginput catatan pelanggaran.');
        }

        $query = User::where('status_aktif', 1);

        // Danru can only evaluate members of their own regu
        if (strtolower(trim($user->role)) === 'danru') {
            $query->where('role', 'Anggota')->where('regu', trim($user->regu));
        } elseif (strtolower(trim($user->role)) === 'chief') {
            // Chief evaluates Danru
            $query->where('role', 'Danru');
        } else {
            // Admin can see both
            $query->whereIn('role', ['Anggota', 'Danru']);
        }

        $anggota = $query->get(['id_user', 'nama_lengkap', 'regu']);

        $jadwals = \App\Models\JadwalBulanan::whereIn('id_anggota', $anggota->pluck('id_user'))->get();

        return Inertia::render('Pelanggaran/InputPelanggaran', [
            'anggota' => $anggota,
            'jadwals' => $jadwals,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user?->role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak. Hanya Admin, Chief, atau Danru yang dapat menginput catatan pelanggaran.');
        }

        $validated = $request->validate([
            'id_anggota' => 'required|exists:users,id_user',
            'tanggal_kejadian' => 'required|date',
            'minggu_ke' => 'required|integer|min:1|max:6',
            'bulan' => 'required|string|max:20',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kategori_indikator' => 'required|in:Kedisiplinan,Kehadiran,Kerapihan,Komunikasi',
            'tingkat_pelanggaran' => 'required|in:Ringan,Sedang,Berat',
            'deskripsi_kejadian' => 'required|string',
        ]);

        if (strtolower(trim($user->role)) === 'danru') {
            $anggota = User::where('id_user', $validated['id_anggota'])->where('role', 'Anggota')->first();
            if (!$anggota || $anggota->regu !== trim($user->regu)) {
                abort(403, 'Akses ditolak. Anda mencoba menginput pelanggaran untuk anggota di luar regu Anda.');
            }
        } elseif (strtolower(trim($user->role)) === 'chief') {
            $anggota = User::where('id_user', $validated['id_anggota'])->where('role', 'Danru')->first();
            if (!$anggota) {
                abort(403, 'Akses ditolak. Chief hanya dapat menginput pelanggaran untuk Danru.');
            }
        }

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

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = CatatanPelanggaran::with(['anggota:id_user,nama_lengkap,regu', 'danruPenilai:id_user,nama_lengkap']);

        if (strtolower(trim($user->role)) === 'danru') {
            $query->whereHas('anggota', function ($q) use ($user) {
                $q->where('regu', trim($user->regu));
            });
        }

        $pelanggaran = $query->orderBy('tanggal_kejadian', 'desc')->get();

        return Inertia::render('Pelanggaran/DaftarPelanggaran', [
            'pelanggaran' => $pelanggaran,
            'userRole' => $user->role,
        ]);
    }

    public function updateTindakLanjut(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user?->role, ['Admin', 'Chief'])) {
            abort(403, 'Akses ditolak. Hanya Admin atau Chief Security yang dapat menandai tindak lanjut.');
        }

        $validated = $request->validate([
            'status_tindak_lanjut' => 'required|in:Sudah,Belum',
        ]);

        $pelanggaran = CatatanPelanggaran::findOrFail($id);
        $pelanggaran->update([
            'status_tindak_lanjut' => $validated['status_tindak_lanjut'],
        ]);

        return redirect()->back()->with('success', 'Status tindak lanjut berhasil diperbarui!');
    }
}
