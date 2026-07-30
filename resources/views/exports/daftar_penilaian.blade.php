<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Catatan Pelanggaran Security</title>
    <style>
        @page { margin: 5mm 10mm; size: A4 landscape; }
        body { font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }

        .text-center { text-align: center; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold; }

        /* FONT MASTER DISUNTIKKAN */
        .header-title { font-size: 24px; font-weight: bold; margin: 0 0 2px 0; text-align: center; letter-spacing: 1px; color: #1a365d; text-transform: uppercase; }
        .header-subtitle { font-size: 13px; margin: 0 0 15px 0; text-align: center; color: #4a5568; font-weight: bold; }

        .main-table { width: 100%; border-collapse: collapse; margin-top: 0; table-layout: fixed; }
        .main-table th, .main-table td { border: 1px solid #4a5568; padding: 3px 4px; text-align: center; font-size: 9.5px; vertical-align: middle; }
        .main-table th { background-color: #e4ecf7; color: #1a365d; font-weight: bold; text-transform: uppercase; padding: 4px; font-size: 9px; }
        .main-table tr:nth-child(even) { background-color: #f8f9fa; }

        /* KUNCI MATI TINGGI BARIS TABEL UTAMA (ANTI STRETCHING) */
        .main-table tbody td {
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

        .badge-ringan { color: #d97706; font-weight: bold; }
        .badge-sedang { color: #ea580c; font-weight: bold; }
        .badge-berat { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>


    @foreach($chunks as $pageIndex => $chunk)
    <div style="{{ $pageIndex < $totalPages - 1 ? 'page-break-after: always;' : '' }}">

        <!-- HEADER LAYOUT MASTER (3 KOLOM) -->
        <table style="width: 100%; border: none; margin-bottom: 6px;">
            <tr>
                <td style="width: 15%; text-align: left; vertical-align: middle;">
                    <img src="{{ $logoBase64 }}" style="height: 75px; width: auto;">
                </td>
                <td style="width: 62%; text-align: center; vertical-align: middle;">
                    <h1 class="header-title">DAFTAR CATATAN PELANGGARAN {{ isset($jenis) && $jenis === 'danru' ? 'KOMANDAN REGU (DANRU)' : 'PERSONEL SECURITY' }}</h1>
                    <p class="header-subtitle">REKAPITULASI CATATAN INDISIPLINER {{ isset($jenis) && $jenis === 'danru' ? 'DANRU' : 'ANGGOTA' }} {{ $totalPages > 1 ? '(HALAMAN '.($pageIndex + 1).' DARI '.$totalPages.')' : '' }}</p>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold; color: #000; margin-bottom: 4px; text-align: right;">
                        TANGGAL REKAP : {{ strtoupper($tglRekap) }}
                    </div>
                    <table style="border: 1px solid #1a365d; font-size: 10px; border-collapse: collapse; width: 200px; margin-left: auto;">
                        <tr><td colspan="2" style="background-color: #1a365d; color: white; padding: 2px; font-weight: bold; text-align: center;">PERIODE PENILAIAN</td></tr>
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

        <!-- INFO PENILAIAN & REGU -->
        <table style="width: 100%; margin-bottom: 4px; border: none; font-size: 10px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    <div class="font-bold" style="color: #1a365d;">DIBUAT OLEH : <span style="text-transform: uppercase;">{{ $userName ?? '-' }}</span> ({{ strtoupper($userRole ?? 'PENILAI') }})</div>
                </td>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0; text-align: right;">
                    <div class="font-bold" style="color: #1a365d;">REGU : {{ strtoupper($reguName) }}</div>
                </td>
            </tr>
        </table>

        <div class="title-block">DAFTAR CATATAN PELANGGARAN</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 4%;">NO</th>
                    <th style="width: 10%;">HARI / TANGGAL</th>
                    <th style="width: 18%; text-align: left;">{{ isset($jenis) && $jenis === 'danru' ? 'NAMA DANRU' : 'NAMA PERSONEL' }}</th>
                    <th style="width: 6%;">REGU</th>
                    <th style="width: 14%; text-align: left;">{{ isset($jenis) && $jenis === 'danru' ? 'CHIEF PENILAI' : 'DANRU PENILAI' }}</th>
                    <th style="width: 11%;">INDIKATOR</th>
                    <th style="width: 8%;">TINGKAT</th>
                    <th style="width: 3%;">ST</th>
                    <th style="width: 26%; text-align: left;">DESKRIPSI KEJADIAN</th>
                </tr>
            </thead>
            <tbody>
                @php $currentRowCount = 0; @endphp
                @foreach($chunk as $index => $item)
                    @php $currentRowCount++; @endphp
                    <tr>
                        <td>{{ ($pageIndex * 10) + $index + 1 }}</td>
                        <td>
                            <div class="font-bold">{{ \Carbon\Carbon::parse($item->tanggal_penilaian)->locale('id')->translatedFormat('l') }}</div>
                            <div style="font-size: 8.5px; color: #555; margin-top: 1px;">{{ \Carbon\Carbon::parse($item->tanggal_penilaian)->format('d-m-Y') }}</div>
                        </td>
                        <td class="text-left font-bold">{{ $item->anggota ? $item->anggota->nama_lengkap : '-' }}</td>
                        <td class="font-bold">{{ $item->anggota ? $item->anggota->regu : '-' }}</td>
                        <td class="text-left font-bold">{{ $item->danruPenilai ? $item->danruPenilai->nama_lengkap : '-' }}</td>
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
                        <td>
                            @if($item->status_tindak_lanjut === 'Sudah' || $item->status_tindak_lanjut === 'Selesai')
                                <span style="color: #27ae60; font-weight: bold; font-size: 13px;">V</span>
                            @else
                                <span style="color: #c0392b; font-weight: bold; font-size: 12px;">X</span>
                            @endif
                        </td>
                        <td class="text-left" style="line-height: 1.3; padding: 4px 6px;">{{ $item->deskripsi_penilaian }}</td>
                    </tr>
                @endforeach

                <!-- LOOPING BARIS KOSONG AGAR TINGGI HALAMAN KONSISTEN MAX 10 BARIS -->
                @for($j = $currentRowCount; $j < 10; $j++)
                    <tr>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- CATATAN & TTD HANYA DI HALAMAN TERAKHIR -->
        @if($pageIndex == $totalPages - 1)
        <table style="width: 100%; border: none; font-size: 10px; margin-top: 8px;">
            <tr>
                <td valign="top" style="width: 60%; border: none; padding: 0;">
                    <div class="font-bold" style="color: #1a365d; margin-bottom: 3px;">CATATAN:</div>
                    <div style="font-size: 9px; color: #4a5568; line-height: 1.4; padding-right: 25px;">
                        Catatan ini merupakan bahan evaluasi rekapitulasi indisipliner untuk memastikan perbaikan berkelanjutan dan pencapaian target KPI. Dikeluarkan oleh {{ strtoupper(($userRole ?? '') === 'Danru' ? 'Danru' : (($userRole ?? '') === 'Chief' ? 'Chief Security' : 'Penilai')) }}.
                    </div>
                </td>
                <td valign="top" style="width: 40%; border: none; padding: 0; text-align: center;">
                    <div style="width: 220px; margin-left: auto; text-align: center;">
                        <div class="font-bold" style="font-size: 10px;">DIBUAT OLEH<br>{{ strtoupper(($userRole ?? '') === 'Danru' ? 'DANRU' : (($userRole ?? '') === 'Chief' ? 'CHIEF SECURITY' : ($userRole ?? 'PENILAI'))) }}</div>
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
                            <div class="font-bold" style="text-decoration: underline; margin-top: 2px; font-size: 10px;">{{ strtoupper($userName ?? '-') }}</div>
                        @else
                            <div style="border-bottom: 1px solid #333; width: 65%; margin: 0 auto; margin-top: 50px; display: block; margin-bottom: 2px;"></div>
                            <div class="font-bold" style="text-decoration: underline; margin-top: 2px; font-size: 10px;">{{ strtoupper($userName ?? '-') }}</div>
                        @endif
                        <div style="margin-top: 3px; font-size: 9px; color: #555;">TANGGAL: {{ strtoupper($tglRekap) }}</div>
                    </div>
                </td>
            </tr>
        </table>
        @endif

    </div>
    @endforeach
</body>
</html>
