<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Catatan Pelanggaran Security</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #1a252f; }
        .header-subtitle { text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 15px; color: #7f8c8d; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 6px; font-size: 10px; border: none; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th, .main-table td { border: 1px solid #2c3e50; padding: 6px 8px; text-align: center; font-size: 9px; }
        .main-table th { background-color: #34495e; color: #ffffff; font-weight: bold; text-transform: uppercase; }
        .main-table tr:nth-child(even) { background-color: #f8f9fa; }
        
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold; }
        .badge-ringan { color: #d97706; font-weight: bold; }
        .badge-sedang { color: #ea580c; font-weight: bold; }
        .badge-berat { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $reguName = isset($filter_regu) && $filter_regu ? $filter_regu : (count($pelanggaran) > 0 ? collect($pelanggaran)->pluck('anggota.regu')->unique()->filter()->implode(', ') : 'SEMUA REGU');
    @endphp

    <div>
        <h1 class="header-title">DAFTAR CATATAN PELANGGARAN PERSONEL SECURITY</h1>
        <p class="header-subtitle">REKAPITULASI CATATAN INDISIPLINER</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">PERIODE</td>
            <td style="width: 35%;">: {{ strtoupper($bulan) }} {{ $tahun }} / MINGGU KE-{{ $minggu_ke }}</td>
            <td style="width: 15%; font-weight: bold;">REGU</td>
            <td style="width: 35%;">: {{ strtoupper($reguName) }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 11%;">TANGGAL</th>
                <th style="width: 18%; text-align: left;">NAMA PERSONEL</th>
                <th style="width: 10%;">REGU</th>
                <th style="width: 13%;">INDIKATOR</th>
                <th style="width: 10%;">TINGKAT</th>
                <th style="text-align: left;">DESKRIPSI KEJADIAN</th>
                <th style="width: 14%; text-align: left;">DANRU PENILAI</th>
                <th style="width: 10%;">TINDAK LANJUT</th>
            </tr>
        </thead>
        <tbody>
            @if(count($pelanggaran) > 0)
                @foreach($pelanggaran as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_penilaian)->format('d-m-Y') }}</td>
                    <td class="text-left font-bold">{{ $item->anggota ? $item->anggota->nama_lengkap : '-' }}</td>
                    <td>{{ $item->anggota ? $item->anggota->regu : '-' }}</td>
                    <td>{{ $item->kategori_indikator }}</td>
                    <td>
                        @if($item->tingkat_penilaian === 'Berat')
                            <span class="badge-berat">BERAT</span>
                        @elseif($item->tingkat_penilaian === 'Sedang')
                            <span class="badge-sedang">SEDANG</span>
                        @else
                            <span class="badge-ringan">RINGAN</span>
                        @endif
                    </td>
                    <td class="text-left">{{ $item->deskripsi_penilaian }}</td>
                    <td class="text-left">{{ $item->danruPenilai ? $item->danruPenilai->nama_lengkap : '-' }}</td>
                    <td class="font-bold">{{ $item->status_tindak_lanjut === 'Sudah' ? 'SUDAH' : 'BELUM' }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" style="padding: 15px; color: #7f8c8d; font-style: italic;">
                        Tidak ada catatan pelanggaran pada periode ini.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
