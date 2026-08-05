<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Sekuriti Mingguan</title>
    <style>
        @page { margin: 5mm 10mm; size: A4 landscape; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* FONT MASTER DISUNTIKKAN */
        .header-title { font-size: 24px; font-weight: bold; margin: 0 0 2px 0; text-align: center; color: #1a365d; letter-spacing: 1px; }
        .header-subtitle { font-size: 13px; margin: 0; text-align: center; color: #4a5568; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .data-table th, .data-table td { border: 1px solid #1a365d; padding: 2px 4px; text-align: center; font-size: 9px; vertical-align: middle; }
        .data-table th { background-color: #e4ecf7; color: #1a365d; font-weight: bold; text-transform: uppercase; }
        .data-table td.text-left { text-align: left; }

        /* KUNCI MUTLAK TINGGI BARIS TABEL UTAMA (2 BARIS PAS) */
        .table-utama tbody td {
            height: 30px !important;
        }

        .section-header { background-color: #1a365d; color: white; font-weight: bold; padding: 4px 6px; text-align: left; font-size: 10px; text-transform: uppercase; margin-bottom: 0; border: 1px solid #1a365d; }

        .signature-table { width: 100%; text-align: center; border: none; margin-top: 5px; }
        .signature-table td { border: none; padding: 0 5px; width: 50%; vertical-align: top; }
        .signature-img { height: 45px; max-height: 45px; margin-top: 2px; margin-bottom: 2px; }
    </style>
</head>
<body>


    @foreach($chunks as $pageIndex => $chunkData)
        <div style="{{ $pageIndex < $totalPages - 1 ? 'page-break-after: always;' : '' }}">

            <!-- HEADER LAYOUT MASTER (3 KOLOM) -->
            <table style="width: 100%; border: none; margin-bottom: 6px;">
                <tr>
                    <td style="width: 15%; text-align: left; vertical-align: middle;">
                            <img src="{{ public_path('images/logo-low.png') }}" style="height: 75px; width: auto;">
                    </td>
                    <td style="width: 62%; text-align: center; vertical-align: middle;">
                        <h1 class="header-title">FORM MONITORING KPI PERSONEL SECURITY (MINGGUAN)</h1>
                        <p class="header-subtitle">BERDASARKAN TARGET KPI BULANAN {{ $totalPages > 1 ? '(HALAMAN '.($pageIndex + 1).' DARI '.$totalPages.')' : '' }}</p>
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: top;">
                        <div style="font-size: 10px; font-weight: bold; color: #000; margin-bottom: 4px; text-align: right;">
                            TANGGAL REKAP : {{ strtoupper($tanggalRekap) }}
                        </div>
                        <table style="border: 1px solid #1a365d; font-size: 10px; border-collapse: collapse; width: 200px; margin-left: auto;">
                            <tr><td colspan="2" style="background-color: #1a365d; color: white; padding: 2px; font-weight: bold; text-align: center;">PERIODE PENILAIAN</td></tr>
                            <tr>
                                <td style="padding: 2px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; font-weight: bold; text-align: left; width: 70px;">BULAN</td>
                                <td style="padding: 2px 4px; border-bottom: 1px solid #1a365d; text-align: left;">: {{ strtoupper($bulanText) }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; font-weight: bold; text-align: left;">MINGGU KE</td>
                                <td style="padding: 2px 4px; border-bottom: 1px solid #1a365d; text-align: left;">: {{ $minggu_ke ?? 1 }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 4px; border-right: 1px solid #1a365d; font-weight: bold; text-align: left;">TAHUN</td>
                                <td style="padding: 2px 4px; text-align: left;">: {{ $tahun ?? date('Y') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- INFO PENILAIAN -->
            <table style="width: 100%; margin-bottom: 6px; border: none; font-size: 10px;">
                <tr>
                    <td style="width: 28%; vertical-align: top; border: none; padding: 0;">
                        <div class="font-bold" style="color: #1a365d;">PENILAIAN {{ $reguDisplay }}</div>
                    </td>
                    <td style="width: 26%; vertical-align: top; border: none; padding: 0;">
                        <div class="font-bold" style="color: #1a365d;">DINILAI OLEH</div>
                        <div style="margin-top: 2px;">PENILAI : {{ strtoupper($danruName) }}</div>
                    </td>
                    <td style="width: 46%; vertical-align: top; border: none; padding: 0;">
                        <div class="font-bold" style="color: #1a365d;">DIKETAHUI OLEH</div>
                        @php
                            $chiefNameTop = \App\Models\User::where('role', 'Chief')->first()->nama_lengkap ?? '';
                        @endphp
                        <div style="margin-top: 2px;">CHIEF SECURITY : {{ $chiefNameTop ? strtoupper($chiefNameTop) : '______________________' }}</div>
                    </td>
                </tr>
            </table>

            <!-- LAYOUT BERDAMPINGAN -->
            <table style="width: 100%; border: none; margin-bottom: 2px; table-layout: fixed;">
                <tr>
                    <!-- LEFT COLUMN (72%) -->
                    <td valign="top" style="width: 72%; padding-right: 8px;">
                        <table class="data-table table-utama" style="margin-bottom: 0;">
                            <thead>
                                <tr style="height: 18px;">
                                    <th colspan="9" style="background-color: #1a365d; color: #ffffff; text-align: center; font-weight: bold; padding: 4px 6px; font-size: 10px;">PENILAIAN MINGGUAN PERSONIL</th>
                                </tr>
                                <tr style="height: 18px;">
                                    <th rowspan="2" style="width: 4%;">NO</th>
                                    <th rowspan="2" style="width: 19%;">NAMA PERSONEL</th>
                                    <th colspan="4">INDIKATOR PENILAIAN (SKOR 0-5)</th>
                                    <th rowspan="2" style="width: 9%;">TOTAL NILAI<br>(0-20)</th>
                                    <th rowspan="2" style="width: 9%;">% MINGGUAN</th>
                                    <th rowspan="2" style="width: 29%;">KETERANGAN</th>
                                </tr>
                                <tr style="height: 18px;">
                                    <th style="width: 7.5%;">DISIPLIN KERJA</th>
                                    <th style="width: 7.5%;">PENAMPILAN & KERAPIHAN</th>
                                    <th style="width: 7.5%;">KEHADIRAN</th>
                                    <th style="width: 7.5%;">KOMUNIKASI & PELAYANAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DI-LOOPING MAX 10 BARIS -->
                                @for($i = 0; $i < 10; $i++)
                                    @php $rowNum = ($pageIndex * 10) + $i + 1; @endphp
                                    @if(isset($chunkData[$i]))
                                        @php
                                            $pd = $chunkData[$i];
                                            $pct = $pd['percentage'];
                                            $ketColor = '#c53030';
                                            $keteranganText = '-';
                                            if ($pct !== null) {
                                                if ($pct > 80) { $keteranganText = 'Sangat baik'; $ketColor = '#2f855a'; }
                                                elseif ($pct > 60) { $keteranganText = 'Baik'; $ketColor = '#3182ce'; }
                                                elseif ($pct > 40) { $keteranganText = 'Cukup baik'; $ketColor = '#d69e2e'; }
                                                elseif ($pct > 20) { $keteranganText = 'Buruk'; $ketColor = '#dd6b20'; }
                                                else { $keteranganText = 'Sangat buruk'; $ketColor = '#c53030'; }
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $rowNum }}</td>
                                            <td class="text-left font-bold">{{ strtoupper($pd['nama_lengkap']) }} {!! (isset($pd['role']) && $pd['role'] === 'Danru') ? '<span style="color:red; font-size:6px;">(DANRU)</span>' : '' !!}</td>
                                            <td class="font-bold">{{ $pd['scores']['Disiplin Kerja'] ?? '-' }}</td>
                                            <td class="font-bold">{{ $pd['scores']['Penampilan & Kerapihan'] ?? '-' }}</td>
                                            <td class="font-bold">{{ $pd['scores']['Kehadiran'] ?? '-' }}</td>
                                            <td class="font-bold">{{ $pd['scores']['Komunikasi & Pelayanan'] ?? '-' }}</td>
                                            <td class="font-bold">{{ $pd['total_score'] ?? '-' }}</td>
                                            <td class="font-bold">{{ $pd['percentage'] !== null ? round($pd['percentage']).'%' : '-' }}</td>
                                            <td>
                                                @if(count($pd['violations'] ?? []) > 0)
                                                    <ul style="margin: 0; padding-left: 10px; text-align: left; font-size: 8px; line-height: 1.1;">
                                                        @foreach($pd['violations'] as $v)
                                                            <li style="margin-bottom: 1px;"><span style="color: #333;">• <strong>{{ $v['kategori_indikator'] }}</strong> ({{ $v['tingkat_penilaian'] }})</span></li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span style="color: {{ $ketColor }}; font-weight: bold;">{{ $keteranganText }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>{{ $rowNum }}</td>
                                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                        </tr>
                                    @endif
                                @endfor
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #e2e8f0; font-weight: bold;">
                                    <td colspan="2" class="text-right" style="padding-right: 10px;">MAKSIMAL NILAI</td>
                                    <td>5</td><td>5</td><td>5</td><td>5</td><td>20</td><td>100%</td><td></td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Catatan & Signatures (Halaman Terakhir) -->
                        @if($pageIndex == $totalPages - 1)
                            <table style="width: 100%; border: none; font-size: 10px; margin-top: 4px;">
                                <tr>
                                    <td valign="top" style="width: 40%; border: none; padding: 0;">
                                        <div class="font-bold" style="color: #1a365d; margin-bottom: 2px;">CATATAN:</div>
                                        <table style="width: 100%; border: none; font-size: 9px; line-height: 1.4;">
                                            <tr><td valign="top" width="12">1.</td><td>Danru melakukan penilaian setiap minggu.</td></tr>
                                            <tr><td valign="top">2.</td><td>Nilai akhir bulanan merupakan akumulasi rata-rata 4 minggu.</td></tr>
                                            <tr><td valign="top">3.</td><td>Target KPI mengacu pada target bulanan.</td></tr>
                                        </table>
                                    </td>
                                    <td valign="top" style="width: 60%; border: none; padding: 0;">
                                        <table class="signature-table" style="width: 100%; border: none;">
                                            <tr>
                                                <td style="width: 50%; vertical-align: top; text-align: center;">
                                                    <div class="font-bold" style="font-size: 9px;">DIBUAT OLEH<br>PENILAI</div>
                                                    @if(isset($laporanMingguan) && $laporanMingguan->ttd_danru_url)
                                                        <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanMingguan->ttd_danru_url)) }}" class="signature-img">
                                                        <div style="font-weight: bold; text-decoration: underline; font-size: 10px;">{{ strtoupper($danruName) ?: '______________________' }}</div>
                                                    @else
                                                        <div style="border-bottom: 1px solid #333; width: 60%; margin: 0 auto; margin-top: 45px; display: block;"></div>
                                                    @endif
                                                    <div style="margin-top: 2px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanMingguan) && $laporanMingguan->tgl_ttd_danru) ? \Carbon\Carbon::parse($laporanMingguan->tgl_ttd_danru)->translatedFormat('d/m/Y') : '.........................' }}</div>
                                                </td>
                                                <td style="width: 50%; vertical-align: top; text-align: center;">
                                                    <div class="font-bold" style="font-size: 9px;">DIKETAHUI OLEH<br>CHIEF SECURITY</div>
                                                    @if(isset($laporanMingguan) && $laporanMingguan->ttd_chief_url)
                                                        <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanMingguan->ttd_chief_url)) }}" class="signature-img">
                                                        @php
                                                            $chiefName = \App\Models\User::where('role', 'Chief')->first()->nama_lengkap ?? 'CHIEF SECURITY';
                                                        @endphp
                                                        <div style="font-weight: bold; text-decoration: underline; font-size: 10px;">{{ $chiefNameTop ? strtoupper($chiefNameTop) : strtoupper($chiefName) }}</div>
                                                    @else
                                                        <div style="border-bottom: 1px solid #333; width: 60%; margin: 0 auto; margin-top: 45px; display: block;"></div>
                                                    @endif
                                                    <div style="margin-top: 2px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanMingguan) && $laporanMingguan->tgl_ttd_chief) ? \Carbon\Carbon::parse($laporanMingguan->tgl_ttd_chief)->translatedFormat('d/m/Y') : '.........................' }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>

                    <!-- RIGHT COLUMN (28%) -->
                    <td valign="top" style="width: 28%;">
                        <div class="section-header" style="text-align: center; border-bottom: none;">PARAMETER PENILAIAN (SKOR 1-5)</div>
                        <table class="data-table" style="margin-bottom: 0;">
                            <tbody>
                                <tr><td colspan="2" class="font-bold text-center" style="background-color: #e4ecf7; color: #1a365d; padding: 4px;">DISIPLIN KERJA</td></tr>
                                <tr><td class="font-bold" style="width: 15px;">5</td><td class="text-left">Tidak pernah melanggar SOP maupun tata tertib</td></tr>
                                <tr><td class="font-bold">4</td><td class="text-left">Pelanggaran ringan 1 kali</td></tr>
                                <tr><td class="font-bold">3</td><td class="text-left">Pelanggaran ringan 2 kali</td></tr>
                                <tr><td class="font-bold">2</td><td class="text-left">Pelanggaran sedang</td></tr>
                                <tr><td class="font-bold">1</td><td class="text-left">Pelanggaran berat</td></tr>

                                <tr><td colspan="2" class="font-bold text-center" style="background-color: #e4ecf7; color: #1a365d; border-top: 2px solid #1a365d; padding: 4px;">PENAMPILAN & KERAPIHAN</td></tr>
                                <tr><td class="font-bold">5</td><td class="text-left">Seragam lengkap, rapi setiap hari</td></tr>
                                <tr><td class="font-bold">4</td><td class="text-left">Kurang rapi 1 kali</td></tr>
                                <tr><td class="font-bold">3</td><td class="text-left">Kurang rapi 2 kali</td></tr>
                                <tr><td class="font-bold">2</td><td class="text-left">Seragam tidak lengkap</td></tr>
                                <tr><td class="font-bold">1</td><td class="text-left">Penampilan tidak sesuai standar</td></tr>

                                <tr><td colspan="2" class="font-bold text-center" style="background-color: #e4ecf7; color: #1a365d; border-top: 2px solid #1a365d; padding: 4px;">KEHADIRAN</td></tr>
                                <tr><td class="font-bold">5</td><td class="text-left">Hadir penuh dan tepat waktu</td></tr>
                                <tr><td class="font-bold">4</td><td class="text-left">Terlambat 1 kali</td></tr>
                                <tr><td class="font-bold">3</td><td class="text-left">Terlambat 2 kali</td></tr>
                                <tr><td class="font-bold">2</td><td class="text-left">Tidak hadir dengan izin</td></tr>
                                <tr><td class="font-bold">1</td><td class="text-left">Mangkir / Alpha</td></tr>

                                <tr><td colspan="2" class="font-bold text-center" style="background-color: #e4ecf7; color: #1a365d; border-top: 2px solid #1a365d; padding: 4px;">KOMUNIKASI & PELAYANAN</td></tr>
                                <tr><td class="font-bold">5</td><td class="text-left">Pelayanan sangat baik, tidak komplain</td></tr>
                                <tr><td class="font-bold">4</td><td class="text-left">Pelayanan baik</td></tr>
                                <tr><td class="font-bold">3</td><td class="text-left">Ada komplain ringan</td></tr>
                                <tr><td class="font-bold">2</td><td class="text-left">Sering mendapat teguran</td></tr>
                                <tr><td class="font-bold">1</td><td class="text-left">Komplain berat</td></tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>
