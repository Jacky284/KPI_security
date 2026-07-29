<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Bulanan Sekuriti</title>
    <style>
        @page { margin: 5mm 10mm; size: A4 landscape; }

        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .header-title { font-size: 24px; font-weight: bold; margin: 0 0 2px 0; text-align: center; letter-spacing: 1px; color: #1a365d; }
        .header-subtitle { font-size: 13px; margin: 0; text-align: center; color: #4a5568; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #4a5568; padding: 4px 4px; }
        th { background-color: #e4ecf7; color: #1a365d; font-size: 10px; font-weight: bold; text-transform: uppercase; text-align: center; }
        td { font-size: 11px; vertical-align: middle; }
        .row-hover:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 30px; font-size: 9px; color: #737373; text-align: right; }
        .col-name { width: 180px; text-align: left; }
        .col-regu { width: 60px; }
    </style>
</head>
<body>

    @php
        $logoBase64 = '';
        if (file_exists(public_path('images/logo-app.png'))) {
            $logoBase64 = config('pdf_logo.base64');
        }
        
        $reguName = collect($anggotas)->pluck('regu')->unique()->filter()->implode(', ') ?: 'Semua Regu';
        $bulanMap = [
            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
            'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
            'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
        ];
        $bulanAngka = is_numeric($bulan) ? (int)$bulan : ($bulanMap[$bulan] ?? date('n'));
        $daysInMonth = \Carbon\Carbon::createFromDate($tahun, $bulanAngka, 1)->daysInMonth;
        
        $tanggalRekap = \Carbon\Carbon::now()->format('d M Y');
    @endphp

    <!-- HEADER LAYOUT MASTER (3 KOLOM) -->
    <table style="width: 100%; border: none; margin-bottom: 15px;">
        <tr>
            <td style="width: 15%; text-align: left; vertical-align: middle; border: none;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="height: 75px; width: auto;">
                @endif
            </td>
            <td style="width: 62%; text-align: center; vertical-align: middle; border: none;">
                <h1 class="header-title">JADWAL BULANAN SEKURITI</h1>
                <p class="header-subtitle">REGU: {{ strtoupper($reguName) }}</p>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: top; border: none;">
                <div style="font-size: 10px; font-weight: bold; color: #000; margin-bottom: 4px; text-align: right;">
                    TANGGAL DICETAK : {{ strtoupper($tanggalRekap) }}
                </div>
                <table style="border: 1px solid #1a365d; font-size: 10px; border-collapse: collapse; width: 200px; margin-left: auto; margin-bottom: 0;">
                    <tr><td colspan="2" style="background-color: #1a365d; color: white; padding: 2px; font-weight: bold; text-align: center;">PERIODE JADWAL</td></tr>
                    <tr>
                        <td style="padding: 2px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; font-weight: bold; text-align: left; width: 70px;">BULAN</td>
                        <td style="padding: 2px 4px; border-bottom: 1px solid #1a365d; text-align: left;">: {{ strtoupper($bulan) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 4px; border-right: 1px solid #1a365d; font-weight: bold; text-align: left;">TAHUN</td>
                        <td style="padding: 2px 4px; text-align: left;">: {{ $tahun }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

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
