<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap KPI Bulanan Sekuriti</title>
    <style>
        @page { margin: 5mm 10mm; size: A4 landscape; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* FONT MASTER DISUNTIKKAN */
        .header-title { font-size: 24px; font-weight: bold; margin: 0 0 2px 0; text-align: center; letter-spacing: 1px; color: #1a365d; }
        .header-subtitle { font-size: 13px; margin: 0; text-align: center; color: #4a5568; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .data-table th, .data-table td { border: 1px solid #4a5568; padding: 3px 4px; text-align: center; font-size: 10px; vertical-align: middle; }
        .data-table th { background-color: #e4ecf7; color: #1a365d; font-weight: bold; text-transform: uppercase; }
        .data-table td.text-left { text-align: left; }

        .title-block {
            background-color: #1a365d; color: white; font-weight: bold; padding: 4px 6px; height: 16px; line-height: 16px;
            text-align: left; font-size: 10px; text-transform: uppercase; border: 1px solid #1a365d; border-bottom: none; margin: 0;
            display: block; white-space: nowrap; overflow: hidden;
        }

        .signature-table { width: 100%; text-align: center; border: none; margin-top: 4px; }
        .signature-table td { border: none; padding: 0 5px; width: 50%; vertical-align: top; }
        .signature-img { height: 45px; max-height: 45px; margin-top: 2px; margin-bottom: 2px; }
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
                    <h1 class="header-title">FORM PENILAIAN KPI KOMANDAN REGU (DANRU) BULANAN</h1>
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
                    <div class="font-bold" style="color: #1a365d;">PENILAIAN DANRU</div>
                </td>
                <td style="width: 26%; vertical-align: top; border: none; padding: 0;">
                    <div class="font-bold" style="color: #1a365d;">DIBUAT OLEH</div>
                    <div style="margin-top: 2px;">PENILAI : {{ isset($laporanBulananObj) && $laporanBulananObj->danruPembuat ? strtoupper($laporanBulananObj->danruPembuat->nama_lengkap) : '______________________' }}</div>
                </td>
                <td style="width: 46%; vertical-align: top; border: none; padding: 0;">
                    <div class="font-bold" style="color: #1a365d;">DIKETAHUI OLEH</div>
                    <div style="margin-top: 2px;">PENGGUNA JASA : ______________________</div>
                </td>
            </tr>
        </table>

        <div class="title-block" style="text-align: center;">A. REKAP NILAI DANRU</div>
        <table class="data-table" style="margin-top: 0; margin-bottom: 6px; table-layout: fixed;">
            <thead>
                <tr style="height: 18px;">
                    <th rowspan="2" style="width: 5%;">NO</th>
                    <th rowspan="2" style="width: 20%;">NAMA DANRU</th>
                    <th rowspan="2" style="width: 8%;">REGU</th>
                    <th colspan="3">NILAI INDIKATOR (1-5)</th>
                    <th rowspan="2" style="width: 10%;">RATA-RATA SKOR</th>
                    <th rowspan="2" style="width: 10%;">PERSENTASE</th>
                    <th rowspan="2" style="width: 15%;">KETERANGAN</th>
                </tr>
                <tr style="height: 18px;">
                    <th style="width: 10.6%;">PENGAWASAN PERSONIL</th>
                    <th style="width: 10.6%;">KETEPATAN PELAPORAN</th>
                    <th style="width: 10.8%;">PENYELESAIAN MASALAH</th>
                </tr>
            </thead>
            <tbody>
                @php $currentRowCount = 0; @endphp
                @foreach($chunk as $index => $person)
                    @php $currentRowCount++; @endphp
                    <tr style="height: 24px;">
                        <td>{{ ($pageIndex * 3) + $index + 1 }}</td>
                        <td class="text-left font-bold">{{ $person['nama_lengkap'] }}</td>
                        <td>{{ $person['regu'] }}</td>
                        <td>{{ isset($person['indicator_scores']['Pengawasan Personel']) ? $person['indicator_scores']['Pengawasan Personel'] : '-' }}</td>
                        <td>{{ isset($person['indicator_scores']['Ketepatan Pelaporan']) ? $person['indicator_scores']['Ketepatan Pelaporan'] : '-' }}</td>
                        <td>{{ isset($person['indicator_scores']['Penyelesaian Masalah']) ? $person['indicator_scores']['Penyelesaian Masalah'] : '-' }}</td>
                        <td class="font-bold">{{ $person['avg_score'] !== null ? number_format($person['avg_score'], 1) : '-' }}</td>
                        <td class="font-bold">{{ $person['avg_percentage'] !== null ? round($person['avg_percentage']) . '%' : '-' }}</td>
                        @php
                            $score = $person['avg_percentage'];
                            $keterangan = '-';
                            $color = '#000';
                            if ($score !== null) {
                                if ($score > 80) { $keterangan = 'Sangat baik'; $color = '#2f855a'; }
                                elseif ($score > 60) { $keterangan = 'Baik'; $color = '#3182ce'; }
                                elseif ($score > 40) { $keterangan = 'Cukup baik'; $color = '#d69e2e'; }
                                elseif ($score > 20) { $keterangan = 'Buruk'; $color = '#dd6b20'; }
                                else { $keterangan = 'Sangat buruk'; $color = '#c53030'; }
                            }
                        @endphp
                        <td class="font-bold">
                            @if($score !== null)
                                <span style="color: {{ $color }}">{{ $keterangan }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach

                @for($j = $currentRowCount; $j < 3; $j++)
                    <tr style="height: 24px;">
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="6" class="text-right" style="padding-right: 15px;">RATA-RATA KESELURUHAN</td>
                    <td>{{ $validPersonCount > 0 ? number_format($grandTotalScore / $validPersonCount / 100 * 5, 1) : '-' }}</td>
                    <td style="color: #1a365d;">{{ $validPersonCount > 0 ? round($grandTotalScore / $validPersonCount) . '%' : '-' }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        @if($pageIndex == $totalPages - 1)
        <!-- 2-Column Container: B&D (Kiri), C&E (Kanan) -->
        <table style="width: 100%; border: none; margin-bottom: 4px; table-layout: fixed;">
            <tr>
                <!-- LEFT COLUMN -->
                <td valign="top" style="width: 63%; padding-right: 10px;">
                    <div class="title-block" style="text-align: center;">B. DESKRIPSI INDIKATOR</div>
                    <table class="data-table" style="margin-top: 0; margin-bottom: 6px;">
                        <thead>
                            <tr style="height: 24px;">
                                <th style="width: 30%;">INDIKATOR KPI</th>
                                <th>PENJELASAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="height: 24px;"><td class="text-left font-bold">Pengawasan Personel</td><td class="text-left">Pengawasan terhadap Kehadiran, kedisiplinan, dan pelaksanaan tugas personel</td></tr>
                            <tr style="height: 24px;"><td class="text-left font-bold">Ketepatan Pelaporan</td><td class="text-left">Ketepatan waktu dan kelengkapan laporan harian serta kejadian</td></tr>
                            <tr style="height: 24px;"><td class="text-left font-bold">Penyelesaian Masalah</td><td class="text-left">Kemampuan dalam Mengidentifikasi, menindaklanjuti, dan menyelesaikan masalah</td></tr>
                        </tbody>
                    </table>

                    <div class="title-block" style="text-align: center;">D. REKAP CAPAIAN KPI BULANAN (PER INDIKATOR)</div>
                    <table class="data-table" style="margin-top: 0; margin-bottom: 0;">
                        <thead>
                            <tr style="height: 24px;">
                                <th style="width: 8%;">NO</th>
                                <th>INDIKATOR KPI</th>
                                <th style="width: 20%;">RATA-RATA (%)</th>
                                <th style="width: 15%;">TARGET</th>
                                <th style="width: 20%;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($detailedMonthlyData['perIndicator']))
                                @foreach($detailedMonthlyData['perIndicator'] as $index => $indicator)
                                    <tr style="height: 24px;">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-left font-bold">{{ $indicator['indikator'] }}</td>
                                        <td class="font-bold">{{ round($indicator['achieved_percentage']) }}%</td>
                                        <td class="font-bold">{!! $indicator['target_text'] !!}</td>
                                        @php $ketColor = $indicator['keterangan'] === 'Tercapai' ? '#2f855a' : '#c53030'; @endphp
                                        <td class="font-bold"><span style="color: {{ $ketColor }}">{{ $indicator['keterangan'] }}</span></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr style="height: 24px;"><td colspan="5">Tidak ada data indikator</td></tr>
                            @endif
                        </tbody>
                    </table>
                </td>

                <!-- RIGHT COLUMN -->
                <td valign="top" style="width: 37%;">
                    <div class="title-block" style="text-align: center;">C. PEDOMAN SKOR</div>
                    <table class="data-table" style="margin-top: 0; margin-bottom: 6px;">
                        <thead>
                            <tr style="height: 24px;">
                                <th style="width: 45%;">SKOR / PERSENTASE</th>
                                <th>KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="height: 24px;"><td class="font-bold">5 (100%)</td><td class="text-left">Sangat baik</td></tr>
                            <tr style="height: 24px;"><td class="font-bold">4 (80%)</td><td class="text-left">Baik</td></tr>
                            <tr style="height: 24px;"><td class="font-bold">3 (60%)</td><td class="text-left">Cukup baik</td></tr>
                            <tr style="height: 24px;"><td class="font-bold">2 (40%)</td><td class="text-left">Kurang baik</td></tr>
                            <tr style="height: 24px;"><td class="font-bold">1 (20%)</td><td class="text-left">Buruk</td></tr>
                        </tbody>
                    </table>

                    <div class="title-block" style="text-align: center; border-bottom: 1px solid #1a365d;">E. KETERANGAN STATUS</div>
                    <table style="width: 100%; border: 1px solid #4a5568; border-top: none; border-collapse: collapse; background-color: #ffffff;">
                        <tr>
                            <td style="padding: 6px 0; text-align: center;">
                                <table style="margin: 0 auto; border: none; font-size: 10px; background-color: transparent;">
                                    <tr>
                                        <td style="text-align: right; color: #2f855a; font-weight: bold; padding: 4px 10px 4px 0; border: none;">Tercapai</td>
                                        <td style="text-align: left; padding: 4px 0 4px 10px; border: none; color: #333;">: Memenuhi target (%)</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right; color: #c53030; font-weight: bold; padding: 4px 10px 4px 0; border: none;">Tidak Tercapai</td>
                                        <td style="text-align: left; padding: 4px 0 4px 10px; border: none; color: #333;">: Di bawah target (%)</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- CATATAN & TTD -->
        <table style="width: 100%; border: none; font-size: 10px; margin-top: 4px; table-layout: fixed;">
            <tr>
                <td valign="top" style="width: 50%;">
                    <div class="font-bold" style="color: #1a365d; margin-bottom: 3px;">CATATAN:</div>
                    <table style="width: 100%; border: none; font-size: 9px; line-height: 1.4;">
                        <tr><td valign="top" width="12">1.</td><td>Form ini digunakan untuk menilai pencapaian KPI Danru.</td></tr>
                        <tr><td valign="top">2.</td><td>Nilai per indikator akan di rekap setiap bulan.</td></tr>
                        <tr><td valign="top">3.</td><td>Target KPI mengacu pada target bulanan yang telah ditetapkan.</td></tr>
                    </table>
                </td>
                <td valign="top" style="width: 50%;">
                    <table class="signature-table">
                        <tr>
                            <td style="vertical-align: top; text-align: center;">
                                <div class="font-bold" style="font-size: 9px;">DIBUAT OLEH<br>PENILAI</div>
                                @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_chief_url)
                                    <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanBulananObj->ttd_chief_url)) }}" class="signature-img">
                                    <div style="font-weight: bold; text-decoration: underline; font-size: 10px;">{{ strtoupper($laporanBulananObj->danruPembuat->nama_lengkap ?? 'CHIEF SECURITY') }}</div>
                                @else
                                    <div style="border-bottom: 1px solid #333; width: 60%; margin: 0 auto; margin-top: 45px; display: block;"></div>
                                @endif
                                <div style="margin-top: 2px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanBulananObj) && $laporanBulananObj->tgl_ttd_chief) ? \Carbon\Carbon::parse($laporanBulananObj->tgl_ttd_chief)->translatedFormat('d/m/Y') : '.........................' }}</div>
                            </td>
                            <td style="vertical-align: top; text-align: center;">
                                <div class="font-bold" style="font-size: 9px;">DIKETAHUI OLEH<br>PENGGUNA JASA</div>
                                @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_klien_url)
                                    <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanBulananObj->ttd_klien_url)) }}" class="signature-img">
                                    <div style="font-weight: bold; text-decoration: underline; font-size: 10px;">PENGGUNA JASA</div>
                                @else
                                    <div style="border-bottom: 1px solid #333; width: 60%; margin: 0 auto; margin-top: 45px; display: block;"></div>
                                @endif
                                <div style="margin-top: 2px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanBulananObj) && $laporanBulananObj->tgl_ttd_klien) ? \Carbon\Carbon::parse($laporanBulananObj->tgl_ttd_klien)->translatedFormat('d/m/Y') : '.........................' }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        @endif
    </div>
    @endforeach
</body>
</html>
