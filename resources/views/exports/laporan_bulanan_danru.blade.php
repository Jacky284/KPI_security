<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap KPI Bulanan Sekuriti</title>
    <style>
        @page { margin: 20px; size: A4 landscape; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .w-full { width: 100%; }
        
        .header-title { font-size: 16px; font-weight: bold; margin: 0; text-align: center; letter-spacing: 1px; color: #1a365d; }
        .header-subtitle { font-size: 11px; margin: 2px 0 10px 0; text-align: center; color: #4a5568; }
        
        .table-layout { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 10px 0; margin-top: 5px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .data-table th, .data-table td { border: 1px solid #4a5568; padding: 3px 5px; text-align: center; font-size: 8px; }
        .data-table th { background-color: #e2e8f0; color: #1a365d; font-weight: bold; text-transform: uppercase; }
        .data-table td.text-left { text-align: left; }
        
        .section-header { background-color: #1a365d; color: white; font-weight: bold; padding: 4px; text-align: left; font-size: 9px; text-transform: uppercase; }
        
        .signature-table { width: 100%; text-align: center; border: none; margin-top: 5px; }
        .signature-table td { border: none; padding: 5px; width: 50%; vertical-align: bottom; }
        .signature-line { border-bottom: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 40px; display: block; margin-bottom: 5px; }
    </style>
</head>
<body>
    @php
        $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $dateObj = \Carbon\Carbon::now();
        $tanggalRekap = $dateObj->format('d') . ' ' . $bulanList[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
        $bulanText = isset($bulan) ? (is_numeric($bulan) ? $bulanList[$bulan-1] : $bulan) : $bulanList[date('n')-1];
        
        $totalWeeks = isset($detailedMonthlyData['totalWeeks']) ? $detailedMonthlyData['totalWeeks'] : 4;
    @endphp

    <div style="position: absolute; top: 0; right: 0; width: 150px;">
        <div class="font-bold" style="text-align: right; margin-bottom: 1px; font-size: 8px;">TANGGAL REKAP : {{ strtoupper($tanggalRekap) }}</div>
        <table style="border: 1px solid #1a365d; font-size: 7.5px; border-collapse: collapse; width: 100%; margin-bottom: 3px;">
            <tr><td colspan="2" style="background-color: #1a365d; color: white; padding: 1px 4px; font-weight: bold; text-align: center;">PERIODE PENILAIAN</td></tr>
            <tr><td style="padding: 1px 4px; border-right: 1px solid #1a365d; border-bottom: 1px solid #1a365d; width: 40%; font-weight: bold; text-align: left;">BULAN</td><td style="padding: 1px 4px; border-bottom: 1px solid #1a365d; width: 60%; text-align: left;">: {{ strtoupper($bulanText) }}</td></tr>
            <tr><td style="padding: 1px 4px; border-right: 1px solid #1a365d; font-weight: bold; text-align: left;">TAHUN</td><td style="padding: 1px 4px; text-align: left;">: {{ $tahun ?? date('Y') }}</td></tr>
        </table>
    </div>

    <div style="text-align: center; margin-bottom: 4px;">
        <h1 class="header-title" style="font-size: 18px; margin: 0;">REKAP KPI BULANAN PERSONEL SECURITY</h1>
        <p class="header-subtitle" style="margin: 2px 0 0 0;">BERDASARKAN TARGET KPI BULANAN</p>
    </div>

    <table class="table-layout">
        <tr>
            <td valign="top" style="width: 68%;">
                <table style="width: 100%; margin-bottom: 2px; border: none; font-size: 9px;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                            <div class="font-bold">DIBUAT OLEH</div>
                            <div style="margin-top: 2px;">CHIEF SECURITY : ______________________</div>
                        </td>
                        <td style="width: 50%; vertical-align: top; border: none; padding-left: 15px; text-align: left;">
                            <div class="font-bold">DIKETAHUI OLEH</div>
                            <div style="margin-top: 2px;">PENGGUNA JASA : ______________________</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td valign="top" style="width: 32%;">
            </td>
        </tr>
        <tr>
            <td valign="top" style="width: 68%;">
                <div class="section-header">A. REKAP NILAI MINGGUAN PER PERSONEL</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 5%;">NO</th>
                            <th rowspan="2" style="width: 20%;">NAMA DANRU</th>
                            <th colspan="3">NILAI INDIKATOR (1-5)</th>
                            <th rowspan="2" style="width: 10%; font-size: 8px;">RATA-RATA SKOR</th>
                            <th rowspan="2" style="width: 10%; font-size: 8px;">PERSENTASE</th>
                            <th rowspan="2" style="width: 15%;">KETERANGAN</th>
                        </tr>
                        <tr>
                            <th style="width: 13%; font-size: 8px;">PENGAWASAN</th>
                            <th style="width: 13%; font-size: 8px;">PELAPORAN</th>
                            <th style="width: 14%; font-size: 8px;">PENYELESAIAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $grandTotalScore = 0; 
                            $validPersonCount = 0;
                            
                            $personnelList = collect($detailedMonthlyData['perPerson'] ?? []);
                        @endphp
                        
                        @forelse($personnelList as $index => $person)
                            @php
                                if ($person['avg_percentage'] !== null) {
                                    $grandTotalScore += $person['avg_percentage'];
                                    $validPersonCount++;
                                }
                                $isTercapai = $person['avg_percentage'] >= 90; // Default threshold assumption
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-left font-bold">{{ $person['nama_lengkap'] }}</td>
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
                                <td>
                                    @if($score !== null)
                                        <span style="color: {{ $color }}">{{ $keterangan }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Tidak ada data Danru</td>
                            </tr>
                        @endforelse
                        
                        <!-- Pad rows to make table look full if less than 21 -->
                        @if(count($personnelList) > 0 && count($personnelList) < 21)
                            @for($i = count($personnelList) + 1; $i <= 21; $i++)
                                <tr>
                                    <td style="color: transparent;">{{ $i }}</td>
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
                        <tr style="background-color: #e2e8f0;">
                            <th colspan="5" class="text-right" style="padding-right: 15px; text-transform: uppercase;">RATA-RATA KESELURUHAN</th>
                            <th>{{ $validPersonCount > 0 ? number_format($grandTotalScore / $validPersonCount / 100 * 5, 1) : '-' }}</th>
                            <th>{{ $validPersonCount > 0 ? round($grandTotalScore / $validPersonCount) . '%' : '-' }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </td>
            
            <td valign="top" style="width: 32%;">
                <div class="section-header">B. REKAP CAPAIAN KPI BULANAN (PER INDIKATOR)</div>
                <table class="data-table">
                    <thead>
                        <tr style="height: 27px;">
                            <th style="width: 8%; height: 27px; vertical-align: middle;">NO</th>
                            <th style="height: 27px; vertical-align: middle;">INDIKATOR KPI</th>
                            <th style="width: 25%; height: 27px; vertical-align: middle;">RATA-RATA BULANAN (%)</th>
                            <th style="width: 15%; height: 27px; vertical-align: middle;">TARGET</th>
                            <th style="width: 20%; height: 27px; vertical-align: middle;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($detailedMonthlyData['perIndicator']))
                            @foreach($detailedMonthlyData['perIndicator'] as $index => $indicator)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-left">{{ $indicator['indikator'] }}</td>
                                    <td>{{ round($indicator['achieved_percentage']) }}%</td>
                                    <td>{!! $indicator['target'] < 100 ? '>= '.$indicator['target'].'%' : '100%' !!}</td>
                                    @php
                                        $ketColor = '#c53030';
                                        if ($indicator['keterangan'] === 'Sangat baik') $ketColor = '#2f855a';
                                        elseif ($indicator['keterangan'] === 'Baik') $ketColor = '#3182ce';
                                        elseif ($indicator['keterangan'] === 'Cukup baik') $ketColor = '#d69e2e';
                                        elseif ($indicator['keterangan'] === 'Buruk') $ketColor = '#dd6b20';
                                    @endphp
                                    <td>
                                        <span style="color: {{ $ketColor }}">{{ $indicator['keterangan'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="5">Tidak ada data indikator</td></tr>
                        @endif
                    </tbody>
                </table>
                
                <div class="section-header" style="margin-top: 8px;">C. KETERANGAN STATUS</div>
                <table class="data-table" style="margin-bottom: 0;">
                    <tbody>
                        <tr>
                            <td style="color: #2f855a; font-weight: bold; width: 40%;">Sangat baik</td>
                            <td class="text-left">: > 80%</td>
                        </tr>
                        <tr>
                            <td style="color: #3182ce; font-weight: bold;">Baik</td>
                            <td class="text-left">: 61% - 80%</td>
                        </tr>
                        <tr>
                            <td style="color: #d69e2e; font-weight: bold;">Cukup baik</td>
                            <td class="text-left">: 41% - 60%</td>
                        </tr>
                        <tr>
                            <td style="color: #dd6b20; font-weight: bold;">Buruk</td>
                            <td class="text-left">: 21% - 40%</td>
                        </tr>
                        <tr>
                            <td style="color: #c53030; font-weight: bold;">Sangat buruk</td>
                            <td class="text-left">: 0% - 20%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </td>
        </tr>
    </table>
    
    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td valign="top" style="width: 40%; font-size: 8px; line-height: 1.4;">
                <div class="font-bold" style="color: #1a365d; margin-bottom: 3px;">CATATAN:</div>
                <table style="width: 100%; border: none; font-size: 8px;">
                    <tr><td valign="top" width="10">1.</td><td>Nilai mingguan diperoleh dari Monitoring KPI Mingguan yang dinilai oleh Danru.</td></tr>
                    <tr><td valign="top">2.</td><td>Nilai akhir bulanan merupakan akumulasi rata-rata dari {{ $totalWeeks }} minggu.</td></tr>
                    <tr><td valign="top">3.</td><td>Target KPI mengacu pada target bulanan yang telah ditetapkan.</td></tr>
                </table>
            </td>
            <td valign="top" style="width: 60%;">
                <table class="signature-table">
                    <tr>
                        <td>
                            <div class="font-bold">DIBUAT OLEH</div>
                            <div>CHIEF SECURITY</div>
                            <div class="signature-line"></div>
                            <div>TANGGAL ..............................</div>
                        </td>
                        <td>
                            <div class="font-bold">DIKETAHUI OLEH</div>
                            <div>PENGGUNA JASA</div>
                            <div class="signature-line"></div>
                            <div>TANGGAL ..............................</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
