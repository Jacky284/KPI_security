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
            $query->where('role', 'Danru');
        } else {
            // Admin can see both
            $query->whereIn('role', ['Anggota', 'Danru']);
        }

        $anggota = $query->get(['id_user', 'nama_lengkap', 'regu', 'role'])->sort(function($a, $b) {
            $aRegu = $a->regu ?: 'Z';
            $bRegu = $b->regu ?: 'Z';
            $reguCompare = strnatcasecmp($aRegu, $bRegu);
            if ($reguCompare === 0) {
                $isADanru = $a->role === 'Danru' ? 0 : 1;
                $isBDanru = $b->role === 'Danru' ? 0 : 1;
                if ($isADanru !== $isBDanru) return $isADanru - $isBDanru;
                return strnatcasecmp($a->nama_lengkap, $b->nama_lengkap);
            }
            return $reguCompare;
        })->values();

        $jadwals = collect();

        return Inertia::render('Pelanggaran/InputPelanggaran', [
            'anggota' => $anggota,
            'jadwals' => $jadwals,
            'userRole' => $user->role,
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
            'tanggal_penilaian' => 'required|date',
            'shift' => 'required|in:Pagi,Malam,Non-Shift',
            'minggu_ke' => 'required|integer|min:1|max:6',
            'bulan' => 'required|string|max:20',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kategori_indikator' => 'required|string',
            'tingkat_penilaian' => 'required|string',
            'deskripsi_penilaian' => 'required|string',
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

        // Cek agar maksimal 1 penilaian per kategori per anggota dalam 1 minggu
        $existing = CatatanPelanggaran::where('id_anggota', $validated['id_anggota'])
            ->where('kategori_indikator', $validated['kategori_indikator'])
            ->where('minggu_ke', $validated['minggu_ke'])
            ->where('tahun', $validated['tahun'])
            ->where('bulan', $validated['bulan'])
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors(['kategori_indikator' => 'Anggota ini sudah memiliki penilaian untuk kategori ini pada minggu tersebut.']);
        }

        CatatanPelanggaran::create([
            'id_anggota' => $validated['id_anggota'],
            'id_penilai' => $user->id_user,
            'tanggal_penilaian' => $validated['tanggal_penilaian'],
            'shift' => $validated['shift'],
            'minggu_ke' => $validated['minggu_ke'],
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
            'kategori_indikator' => $validated['kategori_indikator'],
            'tingkat_penilaian' => $validated['tingkat_penilaian'],
            'deskripsi_penilaian' => $validated['deskripsi_penilaian'],
        ]);

        return redirect()->back()->with('success', 'Catatan pelanggaran berhasil disimpan!');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $now = \Carbon\Carbon::now();
        $defaultBulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $defaultBulan = $defaultBulanMap[$now->month];
        $defaultTahun = $now->year;

        $bulan = $request->input('bulan', $defaultBulan);
        $tahun = (int)$request->input('tahun', $defaultTahun);
        $filter_regu = $request->input('filter_regu');

        $query = CatatanPelanggaran::with(['anggota:id_user,nama_lengkap,regu,role', 'danruPenilai:id_user,nama_lengkap'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if (strtolower(trim($user->role)) === 'danru') {
            $query->whereHas('anggota', function ($q) use ($user) {
                $q->where('regu', trim($user->regu));
            });
        } else if (strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') {
            $query->whereHas('anggota', function ($q) {
                $q->whereIn('role', ['Anggota', 'Danru']);
            });
            if ($filter_regu) {
                $query->whereHas('anggota', function ($q) use ($filter_regu) {
                    $q->where('regu', $filter_regu);
                });
            }
        }

        $pelanggaran = $query->orderBy('tanggal_penilaian', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->get();

        $reguList = \App\Models\Regu::orderBy('nama_regu', 'asc')->pluck('nama_regu')->values();

        return Inertia::render('Pelanggaran/DaftarPelanggaran', [
            'pelanggaran' => $pelanggaran,
            'userRole' => $user->role,
            'selectedBulan' => $bulan,
            'selectedTahun' => $tahun,
            'filterRegu' => $filter_regu,
            'reguList' => $reguList,
        ]);
    }

    public function updateTindakLanjut(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user?->role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak. Hanya Admin, Chief, atau Danru yang dapat menandai tindak lanjut.');
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

    public function exportPelanggaran(Request $request)
    {
        $user = Auth::user();
        $type = $request->query('type', 'pdf');
        $jenis = $request->query('jenis', 'anggota');
        $bulan = $request->query('bulan', 'Juli');
        $tahun = (int)$request->query('tahun', 2026);
        $filter_regu = $request->query('filter_regu');

        $query = CatatanPelanggaran::with(['anggota:id_user,nama_lengkap,regu,role', 'danruPenilai:id_user,nama_lengkap'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if ($jenis === 'danru') {
            $query->whereHas('anggota', function ($q) {
                $q->where('role', 'Danru');
            });
        } else {
            $query->whereHas('anggota', function ($q) {
                $q->where('role', '!=', 'Danru');
            });
        }

        if (strtolower(trim($user->role)) === 'danru') {
            $query->whereHas('anggota', function ($q) use ($user) {
                $q->where('regu', trim($user->regu));
            });
        } else if (strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') {
            if ($filter_regu) {
                $query->whereHas('anggota', function ($q) use ($filter_regu) {
                    $q->where('regu', $filter_regu);
                });
            }
        }

        $pelanggaran = $query->orderBy('tanggal_penilaian', 'desc')->get();

        if ($type === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\PelanggaranExport($pelanggaran, $bulan, $tahun), 
                "catatan_pelanggaran_{$jenis}_{$bulan}_{$tahun}.xlsx"
            );
        } else if ($type === 'pdf') {
            $tglRekap = \Carbon\Carbon::now()->translatedFormat('d F Y');
            $reguName = $filter_regu ? $filter_regu : ($pelanggaran->count() > 0 ? $pelanggaran->pluck('anggota.regu')->unique()->filter()->implode(', ') : 'SEMUA REGU');
            
            $chunks = $pelanggaran->chunk(10);
            if ($chunks->isEmpty()) {
                $chunks = collect([collect([])]);
            }
            $totalPages = $chunks->count();

            $fileName = "catatan_pelanggaran_{$jenis}_{$bulan}_{$tahun}.pdf";
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.daftar_penilaian', [
                'logoBase64' => config('pdf_logo.base64'),
                'pelanggaran' => $pelanggaran,
                'chunks' => $chunks,
                'totalPages' => $totalPages,
                'tglRekap' => $tglRekap,
                'reguName' => $reguName,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'filter_regu' => $filter_regu,
                'jenis' => $jenis,
                'userName' => $user->nama_lengkap ?? '-',
                'userRole' => $user->role ?? 'PENILAI',
                'userTtdUrl' => $user->ttd_url ?? null,
            ])->setPaper('a4', 'landscape');
            
            return $pdf->stream($fileName);
        }

        return redirect()->back()->with('error', 'Tipe export tidak valid');
    }
}
