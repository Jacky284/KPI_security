<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Bulanan Sekuriti</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333333; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #171717; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #171717; }
        .header p { margin: 5px 0 0 0; font-size: 11px; color: #525252; }
        .filter-info { margin-bottom: 15px; font-size: 10px; }
        .filter-info span { font-weight: bold; color: #171717; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #d4d4d4; padding: 6px 4px; }
        th { background-color: #f5f5f5; font-size: 10px; font-weight: bold; text-transform: uppercase; color: #171717; }
        td { font-size: 11px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .row-hover:nth-child(even) { background-color: #fafafa; }
        .footer { margin-top: 30px; font-size: 9px; color: #737373; text-align: right; }
        .col-name { width: 180px; text-align: left; }
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
            
            $bulanMap = [
                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
                'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
                'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
            ];
            $bulanAngka = is_numeric($bulan) ? (int)$bulan : ($bulanMap[$bulan] ?? date('n'));
            $daysInMonth = \Carbon\Carbon::createFromDate($tahun, $bulanAngka, 1)->daysInMonth;
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

    <div style="margin-bottom: 30px;">
        <div style="font-weight: bold; margin-bottom: 8px; font-size: 12px; color: #171717;">Bagian 1: Tanggal 1 - 15</div>
        <table>
            <thead>
                <tr>
                    <th class="text-left col-name">Nama Anggota</th>
                    @for ($i = 1; $i <= 15; $i++)
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
                        <td class="text-left col-name font-bold">{{ $anggota->nama_lengkap }}</td>
                        @for ($i = 1; $i <= 15; $i++)
                            <td class="text-center">
                                @if(isset($harian[$i]))
                                    @if($harian[$i] == 'Pagi') P @elseif($harian[$i] == 'Malam') M @else - @endif
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="text-center" style="padding: 30px; color: #a3a3a3; font-style: italic;">
                            Tidak ada data jadwal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        <div style="font-weight: bold; margin-bottom: 8px; font-size: 12px; color: #171717;">Bagian 2: Tanggal 16 - {{ $daysInMonth }}</div>
        <table>
            <thead>
                <tr>
                    <th class="text-left col-name">Nama Anggota</th>
                    @for ($i = 16; $i <= $daysInMonth; $i++)
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
                        <td class="text-left col-name font-bold">{{ $anggota->nama_lengkap }}</td>
                        @for ($i = 16; $i <= $daysInMonth; $i++)
                            <td class="text-center">
                                @if(isset($harian[$i]))
                                    @if($harian[$i] == 'Pagi') P @elseif($harian[$i] == 'Malam') M @else - @endif
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + ($daysInMonth - 15) }}" class="text-center" style="padding: 30px; color: #a3a3a3; font-style: italic;">
                            Tidak ada data jadwal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Keterangan: P = Pagi, M = Malam, - = Libur</p>
        <p>Dokumen ini dicetak secara otomatis oleh sistem.<br>
        Dicetak oleh: <strong>{{ auth()->check() ? auth()->user()->nama_lengkap : 'Sistem' }}</strong></p>
    </div>

</body>
</html>
