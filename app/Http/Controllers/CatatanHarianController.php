<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CatatanHarian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CatatanHarianController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Roles allowed: Admin, Chief, Danru
        if (!in_array($user?->role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak.');
        }

        $now = \Carbon\Carbon::now();
        $defaultBulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $defaultBulan = $defaultBulanMap[$now->month];
        $defaultTahun = $now->year;
        $defaultMingguKe = \App\Http\Controllers\LaporanController::getWeekNumberForDate($now);

        $bulan = $request->input('bulan', $defaultBulan);
        $tahun = (int)$request->input('tahun', $defaultTahun);
        $minggu_ke = (int)$request->input('minggu_ke', $defaultMingguKe);
        $filter_regu = $request->input('filter_regu');

        $query = CatatanHarian::with(['anggota:id_user,nama_lengkap,regu,role', 'danru:id_user,nama_lengkap'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('minggu_ke', $minggu_ke);

        if (strtolower(trim($user->role)) === 'danru' || strtolower(trim($user->role)) === 'chief') {
            $query->where('id_danru', $user->id_user);
        } else if (strtolower(trim($user->role)) === 'admin') {
            if ($filter_regu) {
                $query->whereHas('anggota', function ($q) use ($filter_regu) {
                    $q->where('regu', $filter_regu);
                });
            }
        }

        $catatan = $query->orderBy('tanggal', 'desc')->get();

        // Options for Anggota selection in modal
        $anggotaQuery = User::where('status_aktif', 1);
        if (strtolower(trim($user->role)) === 'danru') {
            $anggotaQuery->where('role', 'Anggota')->where('regu', trim($user->regu));
        } elseif (strtolower(trim($user->role)) === 'chief') {
            $anggotaQuery->whereIn('role', ['Danru', 'Anggota']);
        } else {
            $anggotaQuery->whereIn('role', ['Anggota', 'Danru']);
        }

        $anggota = $anggotaQuery->get(['id_user', 'nama_lengkap', 'regu', 'role'])->sort(function($a, $b) {
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

        $reguList = \App\Models\Regu::orderBy('nama_regu', 'asc')->pluck('nama_regu')->values();

        return Inertia::render('CatatanHarian/Index', [
            'catatan' => $catatan,
            'anggota' => $anggota,
            'userRole' => $user->role,
            'selectedBulan' => $bulan,
            'selectedTahun' => $tahun,
            'selectedMinggu' => $minggu_ke,
            'filterRegu' => $filter_regu,
            'reguList' => $reguList,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Roles allowed: Admin, Chief, Danru
        if (!in_array($user?->role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'id_anggota' => 'required|exists:users,id_user',
            'tanggal' => 'required|date',
            'jam_kejadian' => 'nullable|string',
            'shift' => 'required|in:Pagi,Malam',
            'pos_lokasi' => 'nullable|string',
            'indikator' => 'required|string',
            'deskripsi' => 'required|string',
            'arahan' => 'nullable|string',
            'status_tindak_lanjut' => 'required|in:Sudah,Belum',
            'keterangan' => 'nullable|string',
        ]);

        $date = \Carbon\Carbon::parse($validated['tanggal']);
        
        $bulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulanStr = $bulanMap[$date->month];
        $mingguKe = \App\Http\Controllers\LaporanController::getWeekNumberForDate($date);

        $jamKejadian = $validated['jam_kejadian'] ?? \Carbon\Carbon::now()->format('H:i');

        CatatanHarian::create([
            'id_danru' => $user->id_user,
            'id_anggota' => $validated['id_anggota'],
            'tanggal' => $validated['tanggal'],
            'jam_kejadian' => $jamKejadian,
            'shift' => $validated['shift'],
            'pos_lokasi' => $validated['pos_lokasi'] ?? null,
            'minggu_ke' => $mingguKe,
            'bulan' => $bulanStr,
            'tahun' => $date->year,
            'indikator' => $validated['indikator'],
            'deskripsi' => $validated['deskripsi'],
            'arahan' => $validated['arahan'],
            'status_tindak_lanjut' => $validated['status_tindak_lanjut'],
            'keterangan' => $validated['keterangan'],
        ]);

        return redirect()->back()->with('success', 'Catatan harian berhasil disimpan!');
    }

    public function updateTindakLanjut(Request $request, $id)
    {
        $user = Auth::user();

        if (!in_array($user?->role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak. Hanya Admin, Chief, atau Danru yang dapat menandai tindak lanjut.');
        }

        $catatan = CatatanHarian::findOrFail($id);

        $validated = $request->validate([
            'status_tindak_lanjut' => 'required|in:Sudah,Belum',
        ]);

        $catatan->update([
            'status_tindak_lanjut' => $validated['status_tindak_lanjut'],
        ]);

        return redirect()->back()->with('success', 'Status tindak lanjut berhasil diperbarui!');
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        // Roles allowed: Admin, Chief, Danru
        if (!in_array($user?->role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak.');
        }

        $now = \Carbon\Carbon::now();
        $defaultBulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $defaultBulan = $defaultBulanMap[$now->month];
        $defaultTahun = $now->year;
        $defaultMingguKe = \App\Http\Controllers\LaporanController::getWeekNumberForDate($now);

        $bulan = $request->input('bulan', $defaultBulan);
        $tahun = (int)$request->input('tahun', $defaultTahun);
        $minggu_ke = (int)$request->input('minggu_ke', $defaultMingguKe);
        $filter_regu = $request->input('filter_regu');

        $query = CatatanHarian::with(['anggota:id_user,nama_lengkap,regu,role', 'danru:id_user,nama_lengkap'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('minggu_ke', $minggu_ke);

        if (strtolower(trim($user->role)) === 'danru' || strtolower(trim($user->role)) === 'chief') {
            $query->where('id_danru', $user->id_user);
        } else if (strtolower(trim($user->role)) === 'admin') {
            if ($filter_regu) {
                $query->whereHas('anggota', function ($q) use ($filter_regu) {
                    $q->where('regu', $filter_regu);
                });
            }
        }

        $catatan = $query->orderBy('tanggal', 'desc')->get();
        $tanggalRekap = \Carbon\Carbon::now()->translatedFormat('d F Y');
        
        $chunks = $catatan->chunk(10);
        if ($chunks->isEmpty()) {
            $chunks = collect([collect([])]);
        }
        $totalPages = $chunks->count();
        
        $html = view('exports.catatan_harian', [
            'catatan' => $catatan,
            'chunks' => $chunks,
            'totalPages' => $totalPages,
            'logoBase64' => config('pdf_logo.base64'),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'minggu_ke' => $minggu_ke,
            'userRole' => $user->role,
            'tanggalRekap' => $tanggalRekap,
            'userName' => $user->nama_lengkap,
            'userTtdUrl' => $user->ttd_url,
            'regu' => $user->role === 'Danru' ? $user->regu : $filter_regu
        ])->render();
        
        $fileName = 'Catatan_Harian_' . ($user->role === 'Danru' ? $user->regu . '_' : '') . $bulan . '_' . $tahun . '_Mg' . $minggu_ke . '.pdf';
        
        $pdfContent = \Spatie\Browsershot\Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->showBackground()
            ->margins(0, 0, 0, 0)
            ->pdf();
            
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }
}
