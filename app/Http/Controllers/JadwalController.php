<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Regu;
use App\Models\JadwalBulanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    // Untuk role yang berhak mengatur jadwal (Admin, Danru, Chief)
    public function manage(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['Admin', 'Chief'])) {
            abort(403);
        }

        $bulan = $request->query('bulan', Carbon::now()->format('m'));
        $tahun = $request->query('tahun', Carbon::now()->format('Y'));

        // Ambil semua anggota dan danru, urutkan berdasarkan regu, lalu role (Danru lebih dulu), lalu nama
        $anggotas = User::whereIn('role', ['Anggota', 'Danru'])
            ->orderBy('regu')
            ->orderBy('role', 'desc')
            ->orderBy('nama_lengkap')
            ->get();
        
        $jadwals = JadwalBulanan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereIn('id_anggota', $anggotas->pluck('id_user'))
            ->get()
            ->keyBy('id_anggota');

        return Inertia::render('Jadwal/Manage', [
            'anggotas' => $anggotas,
            'jadwals' => $jadwals,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['Admin', 'Chief'])) {
            abort(403);
        }

        $validated = $request->validate([
            'bulan' => 'required|string',
            'tahun' => 'required|integer',
            'jadwal' => 'required|array', // key is id_anggota, value is array of "day": "shift"
        ]);

        foreach ($validated['jadwal'] as $id_anggota => $jadwal_harian) {
            JadwalBulanan::updateOrCreate(
                [
                    'id_anggota' => $id_anggota,
                    'bulan' => $validated['bulan'],
                    'tahun' => $validated['tahun']
                ],
                [
                    'id_danru_pembuat' => $user->id_user,
                    'jadwal_harian' => $jadwal_harian
                ]
            );
        }

        return redirect()->back()->with('success', 'Jadwal berhasil disimpan!');
    }

    // Untuk menampilkan jadwal di dashboard (per minggu)
    public function getJadwalMingguan(Request $request)
    {
        $user = Auth::user();
        $startOfWeek = $request->query('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        
        // Logika untuk fetch data di UI dashboard...
        // ... (This can be an API endpoint or part of LaporanController@dashboard)
    }


    public function exportBulanan(Request $request)
    {
        $user = Auth::user();
        $type = $request->query('type', 'pdf');
        $bulan = $request->query('bulan', \Carbon\Carbon::now()->format('m'));
        $tahun = $request->query('tahun', \Carbon\Carbon::now()->format('Y'));

        if ($type === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\JadwalBulananExport($bulan, $tahun), "jadwal_bulanan_{$bulan}_{$tahun}.xlsx");
        } else if ($type === 'pdf') {
            $export = new \App\Exports\JadwalBulananExport($bulan, $tahun);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.jadwal_bulanan', $export->view()->getData())
                ->setPaper('a4', 'landscape');
            return $pdf->stream("jadwal_bulanan_{$bulan}_{$tahun}.pdf");
        }

        return redirect()->back()->with('error', 'Tipe export tidak valid');
    }
}
