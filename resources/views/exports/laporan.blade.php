<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Sekuriti Mingguan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #2c3e50; margin: 0; padding: 10px 20px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .header-title { font-size: 20px; color: #2c3e50; font-weight: bold; margin: 0 0 5px 0; }
        .header-subtitle { font-size: 13px; color: #555; margin: 0 0 20px 0; }
        
        .info-table { width: 100%; margin-bottom: 20px; font-size: 10px; border-collapse: collapse; }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th, .main-table td { border: 1px solid #7f8c8d; padding: 6px 8px; text-align: center; font-size: 10px; }
        .main-table th { background-color: #ecf0f1; color: #2c3e50; font-weight: bold; text-transform: uppercase; }
        
        .footer-section { width: 100%; margin-top: 30px; font-size: 10px; }
        .signature-table { width: 100%; text-align: center; border: none; }
        .signature-table td { border: none; padding: 10px; width: 50%; vertical-align: bottom; }
        .signature-img { max-height: 70px; margin: 10px 0; }
        .signature-line { border-bottom: 1px solid #2c3e50; width: 60%; margin: 0 auto; display: block; padding-top: 60px; }
        .signature-name { font-weight: bold; text-decoration: underline; margin-top: 5px; }
    </style>
</head>
<body>
    @php
        $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $reguName = collect($performanceData)->pluck('regu')->unique()->filter()->implode(', ') ?: '-';
        $danruName = isset($laporanMingguan) && $laporanMingguan->danru ? $laporanMingguan->danru->nama_lengkap : '';
        $dateObj = isset($laporanMingguan) ? \Carbon\Carbon::parse($laporanMingguan->created_at) : \Carbon\Carbon::now();
        $tanggalRekap = $dateObj->format('d') . ' ' . $bulanList[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
    @endphp

    <div class="text-center">
        <h1 class="header-title">FORM MONITORING KPI PERSONEL SECURITY (MINGGUAN)</h1>
        <p class="header-subtitle">BERDASARKAN TARGET KPI BULANAN</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">PERIODE</td>
            <td style="width: 35%;">: {{ strtoupper($bulan) }} {{ $tahun }} / MINGGU KE-{{ $minggu_ke }}</td>
            <td style="width: 15%; font-weight: bold;">DINILAI OLEH</td>
            <td style="width: 35%;">: {{ strtoupper($danruName) }} (DANRU)</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">REGU</td>
            <td>: {{ strtoupper($reguName) }}</td>
            <td style="font-weight: bold;">DIKETAHUI OLEH</td>
            <td>: CHIEF SECURITY</td>
        </tr>
    </table>

    <table class="main-table">
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
                <th style="width: 8%;">1. DISIPLIN<br>KERJA</th>
                <th style="width: 8%;">2. PENAMPILAN<br>& KERAPIHAN</th>
                <th style="width: 8%;">3. KEHADIRAN</th>
                <th style="width: 8%;">4. KOMUNIKASI<br>& PELAYANAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performanceData as $index => $pd)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ strtoupper($pd['nama_lengkap']) }}</td>
                <td>{{ $pd['scores']['Kedisiplinan'] ?? '-' }}</td>
                <td>{{ $pd['scores']['Kerapihan'] ?? '-' }}</td>
                <td>{{ $pd['scores']['Kehadiran'] ?? '-' }}</td>
                <td>{{ $pd['scores']['Komunikasi'] ?? '-' }}</td>
                <td class="font-bold">{{ $pd['total_score'] ?? '-' }}</td>
                <td class="font-bold">{{ $pd['percentage'] !== null ? $pd['percentage'].'%' : '-' }}</td>
                <td class="text-left" style="font-size: 8px;">
                    @if(count($pd['violations']) > 0)
                        <ul style="margin: 0; padding-left: 10px;">
                            @foreach($pd['violations'] as $v)
                                <li>
                                    <strong>{{ $v['kategori_indikator'] }}</strong> ({{ $v['tingkat_pelanggaran'] }})<br>
                                    <em>{{ $v['deskripsi_kejadian'] }}</em><br>
                                    <span style="color: {{ $v['status_tindak_lanjut'] == 'Sudah' ? 'green' : 'red' }};">
                                        [Tindak Lanjut: {{ $v['status_tindak_lanjut'] }}]
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="padding: 20px; font-style: italic;">Tidak ada data personel untuk minggu ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #ecf0f1; font-weight: bold;">
                <td colspan="2" class="text-right">MAKSIMAL NILAI</td>
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

    <div class="footer-section">
        <div style="margin-bottom: 20px;">
            <div class="font-bold">CATATAN:</div>
            <ol style="margin-top: 5px; padding-left: 15px; line-height: 1.5; color: #555;">
                <li>Danru melakukan penilaian setiap minggu dan memastikan data terisi lengkap.</li>
                <li>Nilai akhir bulanan merupakan akumulasi rata-rata dari 4 minggu.</li>
                <li>Target KPI mengacu pada target bulanan yang wajib dicapai oleh setiap personel.</li>
            </ol>
        </div>

        <table class="signature-table">
            <tr>
                <td>
                    <div class="font-bold">DIBUAT OLEH,<br>DANRU</div>
                    @if(isset($laporanMingguan) && $laporanMingguan->ttd_danru_url)
                        <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanMingguan->ttd_danru_url)) }}" class="signature-img">
                        <div class="signature-name">{{ strtoupper($danruName) }}</div>
                    @else
                        <span class="signature-line"></span>
                        <div class="signature-name">DANRU</div>
                    @endif
                    <div style="margin-top: 5px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanMingguan) && $laporanMingguan->tgl_ttd_danru) ? \Carbon\Carbon::parse($laporanMingguan->tgl_ttd_danru)->translatedFormat('d F Y') : ((isset($laporanMingguan) && $laporanMingguan->ttd_danru_url) ? $tanggalRekap : '_________________') }}</div>
                </td>
                <td>
                    <div class="font-bold">MENGETAHUI,<br>CHIEF SECURITY</div>
                    @if(isset($laporanMingguan) && $laporanMingguan->ttd_chief_url)
                        <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanMingguan->ttd_chief_url)) }}" class="signature-img">
                        <div class="signature-name">CHIEF SECURITY</div>
                    @else
                        <span class="signature-line"></span>
                        <div class="signature-name">CHIEF SECURITY</div>
                    @endif
                    <div style="margin-top: 5px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanMingguan) && $laporanMingguan->tgl_ttd_chief) ? \Carbon\Carbon::parse($laporanMingguan->tgl_ttd_chief)->translatedFormat('d F Y') : ((isset($laporanMingguan) && $laporanMingguan->ttd_chief_url) ? $tanggalRekap : '_________________') }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
