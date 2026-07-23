<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Sekuriti Mingguan</title>
    <style>
        @page { margin: 20px 30px; size: A4 landscape; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .w-full { width: 100%; }
        
        .header-title { font-size: 18px; font-weight: bold; margin: 0; text-align: center; color: #1a365d; letter-spacing: 0.5px; }
        .header-subtitle { font-size: 10px; margin: 2px 0 0 0; text-align: center; color: #4a5568; }
        
        .table-layout { width: 100%; border-collapse: collapse; margin-top: 4px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table th, .data-table td { border: 1px solid #1a365d; padding: 3px 5px; text-align: center; font-size: 8px; }
        .data-table th { background-color: #1a365d; color: white; font-weight: bold; text-transform: uppercase; }
        .data-table td.text-left { text-align: left; }
        
        .section-header { background-color: #1a365d; color: white; font-weight: bold; padding: 3px 6px; text-align: left; font-size: 9px; text-transform: uppercase; margin-bottom: 4px; }
        
        .signature-table { width: 100%; text-align: center; border: none; margin-top: 5px; }
        .signature-table td { border: none; padding: 5px; width: 50%; vertical-align: bottom; }
        .signature-img { max-height: 50px; margin: 5px 0; }
        .signature-line { border-bottom: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 40px; display: block; margin-bottom: 5px; }
        .signature-name { font-weight: bold; text-decoration: underline; margin-top: 2px; }
    </style>
</head>
<body>
    @php
        $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $allRegus = collect($performanceData)->pluck('regu')->unique()->filter()->map(function($r) {
            return preg_replace('/^regu\s*/i', '', trim($r));
        })->values();
        if ($allRegus->count() > 0) {
            $reguDisplay = 'REGU ' . $allRegus->implode(', ');
        } else {
            $reguDisplay = isset($laporanMingguan) && $laporanMingguan->regu ? strtoupper($laporanMingguan->regu) : 'REGU 1';
        }
        $danruName = isset($laporanMingguan) && $laporanMingguan->danru ? $laporanMingguan->danru->nama_lengkap : '';
        $dateObj = \Carbon\Carbon::now();
        $tanggalRekap = $dateObj->format('d') . ' ' . $bulanList[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
        $bulanText = isset($bulan) ? (is_numeric($bulan) ? $bulanList[$bulan-1] : $bulan) : $bulanList[date('n')-1];
    @endphp

    <div style="text-align: center; margin-bottom: 4px;">
        <h1 class="header-title">FORM MONITORING KPI PERSONEL SECURITY (MINGGUAN)</h1>
        <p class="header-subtitle">BERDASARKAN TARGET KPI BULANAN</p>
    </div>

    <table class="table-layout" style="margin-bottom: 0;">
        <tr>
            <td valign="top" style="width: 72%;">
                <table style="width: 100%; margin-bottom: 3px; border: none; font-size: 9px;">
                    <tr>
                        <td style="width: 25%; vertical-align: top; border: none; padding: 0;">
                            <div class="font-bold">PENILAIAN {{ strtoupper($reguDisplay) }}</div>
                        </td>
                        <td style="width: 37.5%; vertical-align: top; border: none; padding-left: 10px; text-align: left;">
                            <div class="font-bold">DINILAI OLEH</div>
                            <div style="margin-top: 2px;">DANRU : {{ strtoupper($danruName) ?: '______________________' }}</div>
                        </td>
                        <td style="width: 37.5%; vertical-align: top; border: none; padding-left: 10px; text-align: left;">
                            <div class="font-bold">DIKETAHUI OLEH</div>
                            <div style="margin-top: 2px;">CHIEF SECURITY : ______________________</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td valign="top" style="width: 28%;">
                <div style="position: absolute; top: 0; right: 0; width: 150px;">
                    <div class="font-bold" style="text-align: right; margin-bottom: 1px; font-size: 8px;">TANGGAL REKAP : {{ strtoupper($tanggalRekap) }}</div>
                    <table style="border: 1px solid #1a365d; font-size: 7.5px; border-collapse: collapse; width: 100%; margin-bottom: 3px;">
                        <tr><td colspan="2" style="background-color: #1a365d; color: white; padding: 1px 4px; font-weight: bold; text-align: center;">PERIODE PENILAIAN</td></tr>
                        <tr><td style="padding: 1px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; width: 40%; font-weight: bold; text-align: left;">BULAN</td><td style="padding: 1px 4px; border-bottom: 1px solid #1a365d; width: 60%; text-align: left;">: {{ strtoupper($bulanText) }}</td></tr>
                        <tr><td style="padding: 1px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; width: 40%; font-weight: bold; text-align: left;">MINGGU KE</td><td style="padding: 1px 4px; border-bottom: 1px solid #1a365d; width: 60%; text-align: left;">: {{ $minggu_ke }}</td></tr>
                        <tr><td style="padding: 1px 4px; border-right: 1px solid #1a365d; font-weight: bold; text-align: left;">TAHUN</td><td style="padding: 1px 4px; text-align: left;">: {{ $tahun ?? date('Y') }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width: 72%; padding-right: 5px;">
                <table class="data-table" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">NO</th>
                <th rowspan="2" style="width: 25%; text-align: left;">NAMA PERSONEL</th>
                <th colspan="4">INDIKATOR PENILAIAN (SKOR 0-5)</th>
                <th rowspan="2" style="width: 10%;">TOTAL NILAI<br>(0-20)</th>
                <th rowspan="2" style="width: 10%;">% MINGGUAN</th>
                <th rowspan="2" style="width: 15%;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 8%;">1. DISIPLIN KERJA</th>
                <th style="width: 8%;">2. PENAMPILAN & KERAPIHAN</th>
                <th style="width: 8%;">3. KEHADIRAN</th>
                <th style="width: 8%;">4. KOMUNIKASI & PELAYANAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performanceData as $index => $pd)
                @php
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
                <td>{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ strtoupper($pd['nama_lengkap']) }} {!! (isset($pd['role']) && $pd['role'] === 'Danru') ? '<span style="color:red; font-size:6px;">(DANRU)</span>' : '' !!}</td>
                <td>{{ $pd['scores']['Kedisiplinan'] ?? '-' }}</td>
                <td>{{ $pd['scores']['Kerapihan'] ?? '-' }}</td>
                <td>{{ $pd['scores']['Kehadiran'] ?? '-' }}</td>
                <td>{{ $pd['scores']['Komunikasi'] ?? '-' }}</td>
                <td class="font-bold">{{ $pd['total_score'] ?? '-' }}</td>
                <td class="font-bold">{{ $pd['percentage'] !== null ? round($pd['percentage']).'%' : '-' }}</td>
                <td>
                    @if(count($pd['violations']) > 0)
                        <ul style="margin: 0; padding-left: 10px; text-align: left;">
                            @foreach($pd['violations'] as $v)
                                <li>
                                    <strong>{{ $v['kategori_indikator'] }}</strong> ({{ $v['tingkat_penilaian'] }})
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span style="color: {{ $ketColor }}">{{ $keteranganText }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="padding: 10px; font-style: italic;">Tidak ada data personel untuk minggu ini.</td>
            </tr>
            @endforelse
            
            <!-- Pad rows if needed -->
            @if(count($performanceData) > 0 && count($performanceData) < 22)
                @for($i = count($performanceData) + 1; $i <= 22; $i++)
                    <tr>
                        <td style="color: transparent;">{{ $i }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="2" class="text-right" style="padding-right: 10px;">MAKSIMAL NILAI</td>
                <td>5</td>
                <td>5</td>
                <td>5</td>
                <td>5</td>
                <td>20</td>
                <td>100%</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
            </td>
            <td valign="top" style="width: 28%; padding-left: 5px;">
                <table style="width: 100%; border: 1px solid #1a365d; border-collapse: collapse; margin-bottom: 8px;">
                    <tr>
                        <td style="background-color: #1a365d; color: white; font-weight: bold; text-align: center; padding: 4px; font-size: 8px; border-bottom: 1px solid #1a365d;">
                            PARAMETER PENILAIAN (SKOR 1-5)
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px;">
                            <!-- Disiplin Kerja -->
                            <table class="data-table" style="width: 100%; margin-bottom: 6px;">
                                <thead>
                                    <tr><th colspan="2" style="background-color: #e2e8f0; color: #1a365d; text-align: center;">DISIPLIN KERJA</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td style="width: 15px; font-weight: bold;">5</td><td class="text-left">Tidak pernah melanggar SOP maupun tata tertib</td></tr>
                                    <tr><td style="font-weight: bold;">4</td><td class="text-left">Pelanggaran ringan 1 kali</td></tr>
                                    <tr><td style="font-weight: bold;">3</td><td class="text-left">Pelanggaran ringan 2 kali</td></tr>
                                    <tr><td style="font-weight: bold;">2</td><td class="text-left">Pelanggaran sedang</td></tr>
                                    <tr><td style="font-weight: bold;">1</td><td class="text-left">Pelanggaran berat</td></tr>
                                </tbody>
                            </table>

                            <!-- Penampilan -->
                            <table class="data-table" style="width: 100%; margin-bottom: 6px;">
                                <thead>
                                    <tr><th colspan="2" style="background-color: #e2e8f0; color: #1a365d; text-align: center;">PENAMPILAN & KERAPIHAN</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td style="width: 15px; font-weight: bold;">5</td><td class="text-left">Seragam lengkap, rapi setiap hari</td></tr>
                                    <tr><td style="font-weight: bold;">4</td><td class="text-left">Kurang rapi 1 kali</td></tr>
                                    <tr><td style="font-weight: bold;">3</td><td class="text-left">Kurang rapi 2 kali</td></tr>
                                    <tr><td style="font-weight: bold;">2</td><td class="text-left">Seragam tidak lengkap</td></tr>
                                    <tr><td style="font-weight: bold;">1</td><td class="text-left">Penampilan tidak sesuai standar</td></tr>
                                </tbody>
                            </table>

                            <!-- Kehadiran -->
                            <table class="data-table" style="width: 100%; margin-bottom: 6px;">
                                <thead>
                                    <tr><th colspan="2" style="background-color: #e2e8f0; color: #1a365d; text-align: center;">KEHADIRAN</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td style="width: 15px; font-weight: bold;">5</td><td class="text-left">Hadir penuh dan tepat waktu</td></tr>
                                    <tr><td style="font-weight: bold;">4</td><td class="text-left">Terlambat 1 kali</td></tr>
                                    <tr><td style="font-weight: bold;">3</td><td class="text-left">Terlambat 2 kali</td></tr>
                                    <tr><td style="font-weight: bold;">2</td><td class="text-left">Tidak hadir dengan izin</td></tr>
                                    <tr><td style="font-weight: bold;">1</td><td class="text-left">Mangkir / Alpha</td></tr>
                                </tbody>
                            </table>

                            <!-- Komunikasi -->
                            <table class="data-table" style="width: 100%; margin-bottom: 0;">
                                <thead>
                                    <tr><th colspan="2" style="background-color: #e2e8f0; color: #1a365d; text-align: center;">KOMUNIKASI & PELAYANAN</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td style="width: 15px; font-weight: bold;">5</td><td class="text-left">Pelayanan sangat baik, tidak ada komplain</td></tr>
                                    <tr><td style="font-weight: bold;">4</td><td class="text-left">Pelayanan baik</td></tr>
                                    <tr><td style="font-weight: bold;">3</td><td class="text-left">Ada komplain ringan</td></tr>
                                    <tr><td style="font-weight: bold;">2</td><td class="text-left">Sering mendapat teguran</td></tr>
                                    <tr><td style="font-weight: bold;">1</td><td class="text-left">Komplain berat</td></tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>


            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td valign="top" style="width: 40%; font-size: 8px; line-height: 1.4;">
                <div class="font-bold" style="color: #1a365d; margin-bottom: 3px;">CATATAN:</div>
                <table style="width: 100%; border: none; font-size: 8px; color: #4a5568;">
                    <tr><td valign="top" width="10">1.</td><td>Danru melakukan penilaian setiap minggu dan memastikan data terisi lengkap.</td></tr>
                    <tr><td valign="top">2.</td><td>Nilai akhir bulanan merupakan akumulasi rata-rata dari 4 minggu.</td></tr>
                    <tr><td valign="top">3.</td><td>Target KPI mengacu pada target bulanan yang wajib dicapai oleh setiap personel.</td></tr>
                </table>
            </td>
            <td valign="top" style="width: 60%;">
                <table class="signature-table">
                    <tr>
                        <td>
                            <div class="font-bold" style="font-size: 8.5px;">DIBUAT OLEH<br>DANRU</div>
                            @if(isset($laporanMingguan) && $laporanMingguan->ttd_danru_url)
                                <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanMingguan->ttd_danru_url)) }}" class="signature-img">
                                <div class="signature-name">{{ strtoupper($danruName) }}</div>
                            @else
                                <div class="signature-line"></div>
                            @endif
                            <div style="margin-top: 3px; font-size: 8px; color: #555;">TANGGAL {{ (isset($laporanMingguan) && $laporanMingguan->tgl_ttd_danru) ? \Carbon\Carbon::parse($laporanMingguan->tgl_ttd_danru)->translatedFormat('d F Y') : ((isset($laporanMingguan) && $laporanMingguan->ttd_danru_url) ? $tanggalRekap : '..............................') }}</div>
                        </td>
                        <td>
                            <div class="font-bold" style="font-size: 8.5px;">DIKETAHUI OLEH<br>CHIEF SECURITY</div>
                            @if(isset($laporanMingguan) && $laporanMingguan->ttd_chief_url)
                                <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanMingguan->ttd_chief_url)) }}" class="signature-img">
                                <div class="signature-name">CHIEF SECURITY</div>
                            @else
                                <div class="signature-line"></div>
                            @endif
                            <div style="margin-top: 3px; font-size: 8px; color: #555;">TANGGAL {{ (isset($laporanMingguan) && $laporanMingguan->tgl_ttd_chief) ? \Carbon\Carbon::parse($laporanMingguan->tgl_ttd_chief)->translatedFormat('d F Y') : ((isset($laporanMingguan) && $laporanMingguan->ttd_chief_url) ? $tanggalRekap : '..............................') }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
