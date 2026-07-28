<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Catatan Harian Personel Security</title>
    <style>
        @page { margin: 5mm 10mm; size: A4 landscape; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .header-title { font-size: 24px; font-weight: bold; margin: 0 0 2px 0; text-align: center; letter-spacing: 1px; color: #1a365d; }
        .header-subtitle { font-size: 13px; margin: 0; text-align: center; color: #4a5568; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; table-layout: fixed; }

        .data-table th, .data-table td { border: 1px solid #4a5568; padding: 3px 4px; text-align: center; font-size: 9.5px; vertical-align: middle; }
        .data-table th { background-color: #e4ecf7; color: #1a365d; font-weight: bold; text-transform: uppercase; font-size: 9px; padding: 4px; }
        .data-table td.text-left { text-align: left; }

        /* KUNCI MATI TINGGI BARIS TABEL UTAMA */
        .data-table tbody td {
            height: 35px !important;
        }

        .title-block {
            background-color: #1a365d; color: white; font-weight: bold; padding: 4px 6px; height: 16px; line-height: 16px;
            text-align: left; font-size: 10px; text-transform: uppercase; border: 1px solid #1a365d; border-bottom: none; margin: 0;
            display: block; white-space: nowrap; overflow: hidden; text-align: center;
        }

        /* PERBAIKAN BORDER TABEL PERIODE */
        table.periode-table { border-collapse: collapse; width: 170px; float: right; font-size: 10px; }
        table.periode-table td { border: 1px solid #1a365d; padding: 2px 4px; }
    </style>
</head>
<body>
    @php
        $logoBase64 = '';
        if (file_exists(public_path('images/logo-app.png'))) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo-app.png')));
        }

        $chunks = collect($catatan)->chunk(10);
        if ($chunks->isEmpty()) {
            $chunks = collect([collect([])]);
        }
        $totalPages = $chunks->count();
    @endphp

    @foreach($chunks as $pageIndex => $chunk)
    <div style="{{ $pageIndex < $totalPages - 1 ? 'page-break-after: always;' : '' }}">

        <!-- HEADER LAYOUT MASTER -->
        <table style="width: 100%; border: none; margin-bottom: 6px;">
            <tr>
                <td style="width: 15%; text-align: left; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 75px; width: auto;">
                    @endif
                </td>
                <td style="width: 62%; text-align: center; vertical-align: middle;">
                    @php
                        $titleRole = 'DANRU';
                        if (strtolower(trim($userRole)) === 'admin') {
                            $titleRole = 'DANRU & CHIEF';
                        } elseif (strtolower(trim($userRole)) === 'chief') {
                            $titleRole = 'CHIEF';
                        }
                    @endphp
                    <h1 class="header-title">CATATAN HARIAN {{ $titleRole }}</h1>
                    <p class="header-subtitle">{{ $regu ? 'REGU: ' . strtoupper($regu) : 'SEMUA REGU' }} {{ $totalPages > 1 ? '(HALAMAN '.($pageIndex + 1).' DARI '.$totalPages.')' : '' }}</p>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold; color: #000; margin-bottom: 4px; text-align: right;">
                        TANGGAL REKAP : {{ strtoupper($tanggalRekap) }}
                    </div>
                    <table style="border: 1px solid #1a365d; font-size: 10px; border-collapse: collapse; width: 200px; margin-left: auto;">
                        <tr><td colspan="2" style="background-color: #1a365d; color: white; padding: 2px; font-weight: bold; text-align: center;">PERIODE PENILAIAN</td></tr>
                        <tr>
                            <td style="padding: 2px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; font-weight: bold; text-align: left; width: 70px;">BULAN</td>
                            <td style="padding: 2px 4px; border-bottom: 1px solid #1a365d; text-align: left;">: {{ strtoupper($bulan) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; font-weight: bold; text-align: left;">MINGGU KE</td>
                            <td style="padding: 2px 4px; border-bottom: 1px solid #1a365d; text-align: left;">: {{ $minggu_ke }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 4px; border-right: 1px solid #1a365d; font-weight: bold; text-align: left;">TAHUN</td>
                            <td style="padding: 2px 4px; text-align: left;">: {{ $tahun }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- INFO PENILAIAN -->
        <table style="width: 100%; margin-bottom: 4px; border: none; font-size: 10px;">
            <tr>
                <td style="width: 100%; vertical-align: top; border: none; padding: 0;">
                    <div class="font-bold" style="color: #1a365d;">DIBUAT OLEH : <span style="text-transform: uppercase;">{{ $userName }}</span> ({{ strtoupper($userRole) }})</div>
                </td>
            </tr>
        </table>

        <div class="title-block">DAFTAR CATATAN HARIAN</div>
        <!-- PROPORSI LEBAR KOLOM DIPERBAIKI TOTAL 100% -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">HARI / TANGGAL</th>
                    <th style="width: 12%;">NAMA PERSONEL</th>
                    <th style="width: 5%;">REGU</th>
                    <th style="width: 6%;">SHIFT</th>
                    <th style="width: 10%;">POS / LOKASI</th>
                    <th style="width: 10%;">INDIKATOR</th>
                    <th style="width: 16%;">DESKRIPSI / TEMUAN</th>
                    <th style="width: 15%;">ARAHAN</th>
                    <th style="width: 3%;">ST</th>
                    <th style="width: 15%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @php $currentRowCount = 0; @endphp
                @foreach($chunk as $index => $item)
                    @php $currentRowCount++; @endphp
                    <tr>
                        <td>
                            <div class="font-bold">{{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('l') }}</div>
                            <div style="font-size: 8.5px; color: #555; margin-top: 1px;">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</div>
                        </td>
                        <td class="text-left">
                            <div class="font-bold">{{ $item->anggota->nama_lengkap ?? '-' }}</div>
                            @if(($item->anggota->role ?? '') === 'Danru')
                                <div style="font-size: 7.5px; color: #c53030; font-weight: bold;">(DANRU)</div>
                            @endif
                        </td>
                        <td class="font-bold">{{ $item->anggota->regu ?? '-' }}</td>
                        <td>
                            <div class="font-bold">{{ $item->shift ?? '-' }}</div>
                            @if($item->jam_kejadian)
                                <div style="font-size: 8.5px; color: #555; margin-top: 1px;">({{ \Carbon\Carbon::parse($item->jam_kejadian)->format('H:i') }})</div>
                            @elseif($item->created_at)
                                <div style="font-size: 8.5px; color: #555; margin-top: 1px;">({{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }})</div>
                            @endif
                        </td>
                        <td>{{ $item->pos_lokasi ?? '-' }}</td>
                        <td>{{ $item->indikator ?? '-' }}</td>

                        <td class="text-left" style="line-height: 1.3; padding: 4px 6px;">{{ $item->deskripsi ?? '-' }}</td>
                        <td class="text-left" style="line-height: 1.3; padding: 4px 6px;">{{ $item->arahan ?? '-' }}</td>

                        <!-- SIMBOL DIUBAH JADI AKAR (√) DAN X AGAR AMAN DI PDF -->
                        <td>
                            @if($item->status_tindak_lanjut === 'Sudah' || $item->status_tindak_lanjut === 'Selesai')
                                <span style="color: #27ae60; font-weight: bold; font-size: 13px;">V</span>
                            @else
                                <span style="color: #c0392b; font-weight: bold; font-size: 12px;">X</span>
                            @endif
                        </td>

                        <td class="{{ trim($item->keterangan ?? '-') === '-' ? 'text-center' : 'text-left' }}" style="line-height: 1.3; padding: 4px 6px;">
                            {{ $item->keterangan ?? '-' }}
                        </td>
                    </tr>
                @endforeach

                <!-- LOOPING BARIS KOSONG -->
                @for($j = $currentRowCount; $j < 10; $j++)
                    <tr>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        @if($pageIndex == $totalPages - 1)
        <table style="width: 100%; border: none; font-size: 10px; margin-top: 8px;">
            <tr>
                <td valign="top" style="width: 60%; border: none; padding: 0;">
                    <div class="font-bold" style="color: #1a365d; margin-bottom: 3px;">CATATAN:</div>
                    <div style="font-size: 9px; color: #4a5568; line-height: 1.4; padding-right: 25px;">
                        Catatan ini merupakan bahan evaluasi harian {{ strtoupper($userRole === 'Danru' ? 'Danru' : ($userRole === 'Chief' ? 'Chief' : 'Danru/Chief')) }} untuk memastikan perbaikan berkelanjutan dan pencapaian target KPI Personel.
                    </div>
                </td>
                <td valign="top" style="width: 40%; border: none; padding: 0; text-align: center;">
                    <div style="width: 220px; margin-left: auto; text-align: center;">
                        <div class="font-bold" style="font-size: 10px;">DIBUAT OLEH<br>{{ strtoupper($userRole === 'Danru' ? 'DANRU' : ($userRole === 'Chief' ? 'CHIEF SECURITY' : $userRole)) }}</div>
                        @php
                            $ttdPath = $userTtdUrl ?? null;
                            $fullPath = null;
                            if ($ttdPath) {
                                $cleanPath = str_replace('/storage/', '', $ttdPath);
                                $cleanPath = ltrim($cleanPath, '/');
                                $fullPath = public_path('storage/' . $cleanPath);
                            }
                        @endphp
                        @if(isset($fullPath) && file_exists($fullPath))
                            <img src="{{ $fullPath }}" style="height: 45px; max-height: 45px; margin-top: 5px; margin-bottom: 2px;">
                            <div class="font-bold" style="text-decoration: underline; margin-top: 2px; font-size: 10px;">{{ strtoupper($userName) }}</div>
                        @else
                            <div style="border-bottom: 1px solid #333; width: 65%; margin: 0 auto; margin-top: 50px; display: block; margin-bottom: 2px;"></div>
                            <div class="font-bold" style="text-decoration: underline; margin-top: 2px; font-size: 10px;">{{ strtoupper($userName) }}</div>
                        @endif
                        <div style="margin-top: 3px; font-size: 9px; color: #555;">TANGGAL: {{ strtoupper($tanggalRekap) }}</div>
                    </div>
                </td>
            </tr>
        </table>
        @endif
    </div>
    @endforeach
</body>
</html>
