<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Bulanan Sekuriti</title>
    <style>
        @page { size: A3 landscape; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333333; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #171717; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #171717; }
        .header p { margin: 5px 0 0 0; font-size: 11px; color: #525252; }
        .filter-info { margin-bottom: 15px; font-size: 10px; }
        .filter-info span { font-weight: bold; color: #171717; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th, td { border: 1px solid #d4d4d4; padding: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        th { background-color: #f5f5f5; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #171717; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .row-hover:nth-child(even) { background-color: #fafafa; }
        .footer { margin-top: 30px; font-size: 9px; color: #737373; text-align: right; }
        .col-name { width: 120px; }
        .col-regu { width: 60px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>JADWAL BULANAN SEKURITI</h1>
        <p>Bulan {{ $bulan }} | Tahun {{ $tahun }}</p>
    </div>

    <div class="filter-info">
        @php
            $reguName = $anggotas->pluck('regu')->unique()->filter()->implode(', ') ?: 'Semua Regu';
        @endphp
        <table style="width: 50%; border: none; margin-bottom: 0;">
            <tr>
                <td style="border: none; padding: 2px 0;">REGU</td>
                <td style="border: none; padding: 2px 0;">: <span style="text-transform: uppercase;">{{ $reguName }}</span></td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px 0;">Waktu Dicetak</td>
                <td style="border: none; padding: 2px 0;">: <span>{{ \Carbon\Carbon::now()->format('d M Y, H:i') }} WIB</span></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left col-name">Nama Anggota</th>
                @for ($i = 1; $i <= 31; $i++)
                    <th class="text-center">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($anggotas as $anggota)
                @php
                    $harian = isset($jadwals[$anggota->id_user]) ? $jadwals[$anggota->id_user]->jadwal_harian : [];
                @endphp
                <tr class="row-hover">
                    <td class="text-left col-name">{{ $anggota->nama_lengkap }}</td>
                    @for ($i = 1; $i <= 31; $i++)
                        <td class="text-center">
                            @if(isset($harian[$i]))
                                @if($harian[$i] == 'Pagi') P @elseif($harian[$i] == 'Malam') M @else L @endif
                            @else
                                L
                            @endif
                        </td>
                    @endfor
                </tr>
            @empty
                <tr>
                    <td colspan="32" class="text-center" style="padding: 30px; color: #a3a3a3; font-style: italic;">
                        Tidak ada data jadwal.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Keterangan: P = Pagi, M = Malam, L = Libur</p>
        <p>Dokumen ini dicetak secara otomatis oleh sistem.<br>
        Dicetak oleh: <strong>{{ auth()->check() ? auth()->user()->nama_lengkap : 'Sistem' }}</strong></p>
    </div>

</body>
</html>
