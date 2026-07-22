<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap KPI Bulanan Sekuriti</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #2c3e50; margin: 0; padding: 10px 20px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .header-title { font-size: 20px; color: #2c3e50; font-weight: bold; margin: 0 0 5px 0; text-align: center; }
        .header-subtitle { font-size: 13px; color: #555; margin: 0 0 20px 0; text-align: center; }
        
        .info-table { width: 100%; margin-bottom: 20px; font-size: 10px; border-collapse: collapse; }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        
        .section-title { font-size: 12px; font-weight: bold; margin-bottom: 8px; margin-top: 15px; color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 3px; display: inline-block; }
        
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .main-table th, .main-table td { border: 1px solid #7f8c8d; padding: 6px 8px; text-align: center; font-size: 10px; }
        .main-table th { background-color: #ecf0f1; color: #2c3e50; font-weight: bold; }
        
        .footer-section { width: 100%; margin-top: 30px; font-size: 10px; }
        .signature-table { width: 100%; text-align: center; border: none; }
        .signature-table td { border: none; padding: 10px; width: 33.33%; vertical-align: bottom; }
        .signature-img { max-height: 70px; margin: 10px 0; }
        .signature-line { border-bottom: 1px solid #2c3e50; width: 60%; margin: 0 auto; display: block; padding-top: 60px; }
        .signature-name { font-weight: bold; text-decoration: underline; margin-top: 5px; }
    </style>
</head>
<body>
    @php
        $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $dateObj = isset($laporanBulananObj) ? \Carbon\Carbon::parse($laporanBulananObj->created_at) : \Carbon\Carbon::now();
        $tanggalRekap = $dateObj->format('d') . ' ' . $bulanList[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
        $chiefName = "CHIEF SECURITY";
        $danruName = isset($laporanBulananObj) && $laporanBulananObj->danruPembuat ? $laporanBulananObj->danruPembuat->nama_lengkap : 'DANRU';
        $bulanText = isset($bulan) ? $bulan : $bulanList[date('n')-1];
        $reguName = isset($laporanBulananObj) && $laporanBulananObj->regu 
            ? $laporanBulananObj->regu 
            : (isset($detailedMonthlyData['perPerson']) && count($detailedMonthlyData['perPerson']) > 0 
                ? (collect($detailedMonthlyData['perPerson'])->pluck('regu')->unique()->filter()->implode(', ') ?: 'SEMUA REGU')
                : 'SEMUA REGU');
    @endphp

    <div>
        <h1 class="header-title">REKAP KPI BULANAN PERSONEL SECURITY</h1>
        <p class="header-subtitle">BERDASARKAN TARGET KPI BULANAN</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">PERIODE</td>
            <td style="width: 35%;">: {{ strtoupper($bulanText) }} {{ $tahun }}</td>
            <td style="width: 15%; font-weight: bold;">DIBUAT OLEH</td>
            <td style="width: 35%;">: {{ strtoupper($danruName) }} (DANRU)</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">REGU</td>
            <td>: {{ strtoupper($reguName) }}</td>
            <td style="font-weight: bold;">DIKETAHUI OLEH</td>
            <td>: CHIEF & PENGGUNA JASA</td>
        </tr>
    </table>

    <div style="width: 100%; display: table;">
        <!-- Section A -->
        <div style="display: table-cell; width: 55%; padding-right: 15px; vertical-align: top;">
            <div class="section-title">A. REKAP NILAI MINGGUAN PER PERSONEL</div>
            <table class="main-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 5%;">NO</th>
                        <th rowspan="2" style="width: 30%; text-align: left;">NAMA PERSONEL</th>
                        <th colspan="4" style="border-bottom: 1px solid #7f8c8d;">NILAI MINGGUAN (%)</th>
                        <th rowspan="2" style="width: 15%;">RATA-RATA<br>BULANAN (%)</th>
                        <th rowspan="2" style="width: 20%;">KETERANGAN<br>(PENCAPAIAN)</th>
                    </tr>
                    <tr>
                        <th style="width: 7.5%;">M 1</th>
                        <th style="width: 7.5%;">M 2</th>
                        <th style="width: 7.5%;">M 3</th>
                        <th style="width: 7.5%;">M 4</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($detailedMonthlyData['perPerson']) && count($detailedMonthlyData['perPerson']) > 0)
                        @foreach($detailedMonthlyData['perPerson'] as $index => $person)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-left font-bold">{{ strtoupper($person['nama_lengkap']) }}</td>
                            <td>{{ isset($person['weekly_scores']['M1']) ? number_format($person['weekly_scores']['M1'], 0).'%' : '-' }}</td>
                            <td>{{ isset($person['weekly_scores']['M2']) ? number_format($person['weekly_scores']['M2'], 0).'%' : '-' }}</td>
                            <td>{{ isset($person['weekly_scores']['M3']) ? number_format($person['weekly_scores']['M3'], 0).'%' : '-' }}</td>
                            <td>{{ isset($person['weekly_scores']['M4']) ? number_format($person['weekly_scores']['M4'], 0).'%' : '-' }}</td>
                            <td class="font-bold">{{ $person['avg_percentage'] !== null ? number_format($person['avg_percentage'], 1).'%' : '-' }}</td>
                            <td style="color: {{ $person['penilaian'] == 'Tercapai' ? 'green' : 'red' }}; font-weight: bold;">
                                {{ $person['penilaian'] }}
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="8" style="padding: 15px; font-style: italic;">Tidak ada data.</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background-color: #ecf0f1;">
                        <td colspan="6" class="text-right font-bold">RATA-RATA KESELURUHAN</td>
                        @php
                            $totalAvg = 0;
                            if(isset($detailedMonthlyData['perPerson']) && count($detailedMonthlyData['perPerson']) > 0){
                                $totalAvg = collect($detailedMonthlyData['perPerson'])->avg('avg_percentage');
                            }
                        @endphp
                        <td class="font-bold">{{ number_format($totalAvg, 1) }}%</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Section B & C -->
        <div style="display: table-cell; width: 45%; vertical-align: top;">
            <div class="section-title">B. REKAP CAPAIAN KPI BULANAN</div>
            <table class="main-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">NO</th>
                        <th style="width: 40%; text-align: left;">INDIKATOR KPI</th>
                        <th style="width: 20%;">RATA-RATA<br>BULANAN</th>
                        <th style="width: 15%;">TARGET</th>
                        <th style="width: 20%;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($detailedMonthlyData['perIndicator']) && count($detailedMonthlyData['perIndicator']) > 0)
                        @foreach($detailedMonthlyData['perIndicator'] as $idx => $ind)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="text-left font-bold">{{ strtoupper($ind['indikator']) }}</td>
                            <td class="font-bold">{{ number_format($ind['achieved_percentage'], 1) }}%</td>
                            <td>&gt;= {{ $ind['target'] }}%</td>
                            <td style="color: {{ $ind['keterangan'] == 'Tercapai' ? 'green' : 'red' }}; font-weight: bold;">
                                {{ $ind['keterangan'] }}
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="5" style="padding: 15px; font-style: italic;">Tidak ada data.</td></tr>
                    @endif
                </tbody>
            </table>

            <div class="section-title" style="margin-top: 15px;">C. KETERANGAN STATUS</div>
            <div style="border: 1px solid #7f8c8d; padding: 10px; background: #ecf0f1; font-weight: bold; border-radius: 4px;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none; width: 40%; color: green; text-align: right;">Tercapai</td>
                        <td style="border: none; width: 10%; text-align: center;">:</td>
                        <td style="border: none; width: 50%; text-align: left;">Nilai &gt;= Target</td>
                    </tr>
                    <tr>
                        <td style="border: none; color: red; text-align: right;">Tidak Tercapai</td>
                        <td style="border: none; text-align: center;">:</td>
                        <td style="border: none; text-align: left;">Nilai &lt; Target</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer section -->
    <div class="footer-section">
        <div style="margin-bottom: 20px;">
            <div class="font-bold">CATATAN:</div>
            <ol style="margin-top: 5px; padding-left: 15px; line-height: 1.5; color: #555;">
                <li>Nilai mingguan diperoleh dari Monitoring KPI Mingguan yang dinilai oleh Danru.</li>
                <li>Nilai akhir bulanan merupakan akumulasi rata-rata dari 4 minggu.</li>
                <li>Target KPI mengacu pada target bulanan yang telah ditetapkan.</li>
            </ol>
        </div>
        
        <table class="signature-table">
            <tr>
                <!-- Danru -->
                <td>
                    <div class="font-bold">DIBUAT OLEH,<br>DANRU</div>
                    @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_danru_url)
                        <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanBulananObj->ttd_danru_url)) }}" class="signature-img">
                        <div class="signature-name">{{ strtoupper($danruName) }}</div>
                    @else
                        <span class="signature-line"></span>
                        <div class="signature-name">DANRU</div>
                    @endif
                    <div style="margin-top: 5px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanBulananObj) && $laporanBulananObj->tgl_ttd_danru) ? \Carbon\Carbon::parse($laporanBulananObj->tgl_ttd_danru)->translatedFormat('d F Y') : ((isset($laporanBulananObj) && $laporanBulananObj->ttd_danru_url) ? $tanggalRekap : '_________________') }}</div>
                </td>
                <!-- Chief -->
                <td>
                    <div class="font-bold">MENGETAHUI,<br>CHIEF SECURITY</div>
                    @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_chief_url)
                        <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanBulananObj->ttd_chief_url)) }}" class="signature-img">
                        <div class="signature-name">{{ $chiefName }}</div>
                    @else
                        <span class="signature-line"></span>
                        <div class="signature-name">CHIEF SECURITY</div>
                    @endif
                    <div style="margin-top: 5px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanBulananObj) && $laporanBulananObj->tgl_ttd_chief) ? \Carbon\Carbon::parse($laporanBulananObj->tgl_ttd_chief)->translatedFormat('d F Y') : ((isset($laporanBulananObj) && $laporanBulananObj->ttd_chief_url) ? $tanggalRekap : '_________________') }}</div>
                </td>
                <!-- Pengguna Jasa -->
                <td>
                    <div class="font-bold">DIKETAHUI OLEH,<br>PENGGUNA JASA</div>
                    @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_klien_url)
                        <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanBulananObj->ttd_klien_url)) }}" class="signature-img">
                        <div class="signature-name">PENGGUNA JASA</div>
                    @else
                        <span class="signature-line"></span>
                        <div class="signature-name">PENGGUNA JASA</div>
                    @endif
                    <div style="margin-top: 5px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanBulananObj) && $laporanBulananObj->tgl_ttd_klien) ? \Carbon\Carbon::parse($laporanBulananObj->tgl_ttd_klien)->translatedFormat('d F Y') : ((isset($laporanBulananObj) && $laporanBulananObj->ttd_klien_url) ? $tanggalRekap : '_________________') }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
