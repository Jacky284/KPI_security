<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap KPI Bulanan Sekuriti</title>
    <style>
        /* Margin kertas disesuaikan */
        @page { margin: 5mm 10mm; size: A4 landscape; }

        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .header-title { font-size: 24px; font-weight: bold; margin: 0 0 2px 0; text-align: center; letter-spacing: 1px; color: #1a365d; }
        .header-subtitle { font-size: 13px; margin: 0; text-align: center; color: #4a5568; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }

        .data-table th, .data-table td { border: 1px solid #4a5568; padding: 2px 4px; text-align: center; font-size: 10px; vertical-align: middle; }
        .data-table th { background-color: #e4ecf7; color: #1a365d; font-weight: bold; text-transform: uppercase; }
        .data-table td.text-left { text-align: left; }

        /* JURUS PAMUNGKAS: Judul dikeluarkan dari tabel dan dikunci mati ukurannya */
        .title-block {
            background-color: #1a365d;
            color: white;
            font-weight: bold;
            padding: 0 6px;
            line-height: 22px; /* Teks otomatis di tengah vertikal */
            height: 22px; /* Tinggi mutlak dikunci */
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #1a365d;
            border-bottom: none;
            margin: 0;
            display: block;
            white-space: nowrap;
            overflow: hidden;
        }

        .signature-table { width: 100%; text-align: center; border: none; }
        .signature-table td { border: none; padding: 0 5px; width: 50%; }

        .signature-img { height: 45px; max-height: 45px; margin-top: 2px; margin-bottom: 2px; }
    </style>
</head>
<body>
    @php
        $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $dateObj = \Carbon\Carbon::now();
        $tanggalRekap = $dateObj->format('d') . ' ' . strtoupper($bulanList[$dateObj->format('n') - 1]) . ' ' . $dateObj->format('Y');
        $bulanText = isset($bulan) ? (is_numeric($bulan) ? $bulanList[$bulan-1] : $bulan) : $bulanList[date('n')-1];

        $totalWeeks = isset($detailedMonthlyData['totalWeeks']) ? $detailedMonthlyData['totalWeeks'] : 4;

        $logoBase64 = '';
        if (file_exists(public_path('images/logo-app.png'))) {
            $logoBase64 = config('pdf_logo.base64');
        }

        $personnelList = collect($detailedMonthlyData['perPerson'] ?? []);

        $rowsPerPage = 20;

        $chunks = $personnelList->chunk($rowsPerPage);
        if ($chunks->isEmpty()) {
            $chunks = collect([collect([])]);
        }
        $totalPages = $chunks->count();

        $grandTotalScore = 0;
        $validPersonCount = 0;
        foreach($personnelList as $person) {
            if ($person['avg_percentage'] !== null) {
                $grandTotalScore += $person['avg_percentage'];
                $validPersonCount++;
            }
        }
    @endphp

    @foreach($chunks as $pageIndex => $chunk)
        <div style="{{ $pageIndex < $totalPages - 1 ? 'page-break-after: always;' : '' }}">

            <!-- HEADER SURAT -->
            <table style="width: 100%; border: none; margin-bottom: 6px;">
                <tr>
                    <td style="width: 15%; text-align: left; vertical-align: middle;">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" style="height: 75px; width: auto;">
                        @endif
                    </td>
                    <td style="width: 62%; text-align: center; vertical-align: middle;">
                        <h1 class="header-title">REKAP KPI BULANAN PERSONEL SECURITY</h1>
                        <p class="header-subtitle">BERDASARKAN TARGET KPI BULANAN</p>
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: top;">
                        <div style="font-size: 10px; font-weight: bold; color: #000; margin-bottom: 4px; text-align: right;">
                            TANGGAL REKAP : {{ strtoupper($tanggalRekap) }}
                        </div>

                        <!-- TABEL PERIODE -->
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

            <!-- INFO PENILAIAN, DIBUAT & DIKETAHUI -->
            <table style="width: 100%; margin-bottom: 4px; border: none; font-size: 10px;">
                <tr>
                    <td style="width: 40%; border: none; padding: 0; vertical-align: top;">
                        <div class="font-bold" style="color: #1a365d;">PENILAIAN KESELURUHAN PERSONEL</div>
                        <div class="font-bold" style="color: #2b6cb0; margin-top: 2px;">(Lampiran: {{ $pageIndex + 1 }} dari {{ $totalPages }} Lembar)</div>
                    </td>
                    <td style="width: 30%; border: none; padding: 0; vertical-align: top;">
                        <div class="font-bold" style="color: #1a365d;">DIBUAT OLEH</div>
                        <div style="margin-top: 2px;">CHIEF SECURITY : {{ isset($laporanBulananObj) && $laporanBulananObj->danruPembuat ? strtoupper($laporanBulananObj->danruPembuat->nama_lengkap) : '______________________' }}</div>
                    </td>
                    <td style="width: 30%; border: none; padding: 0; vertical-align: top;">
                        <div class="font-bold" style="color: #1a365d;">DIKETAHUI OLEH</div>
                        <div style="margin-top: 2px;">PENGGUNA JASA : ______________________</div>
                    </td>
                </tr>
            </table>

            <!-- LAYOUT BERDAMPINGAN: 3 KOLOM MURNI TANPA PADDING AGAR TIDAK KEPOTONG -->
            <table style="width: 100%; border: none; margin-bottom: 2px; table-layout: fixed;">
                <tr>
                    <!-- KIRI: TABEL A (Lebar 57%) -->
                    <td valign="top" style="width: 57%; padding: 0;">

                        <!-- Judul Dipisah dari Tabel -->
                        <div class="title-block">A. REKAP NILAI MINGGUAN PER PERSONEL</div>

                        <table class="data-table" style="margin-top: 0; margin-bottom: 0; table-layout: fixed;">
                            <thead>
                                <!-- KUNCI TINGGI BARIS 1: 18px -->
                                <tr style="height: 18px;">
                                    <th rowspan="2" style="width: 5%;">NO</th>
                                    <th rowspan="2" style="width: 25%;">NAMA PERSONEL</th>
                                    <th colspan="{{ $totalWeeks }}">NILAI MINGGUAN (%)</th>
                                    <th rowspan="2" style="width: 15%;">RATA-RATA BULANAN (%)</th>
                                    <th rowspan="2" style="width: 23%;">KETERANGAN (PENCAPAIAN)</th>
                                </tr>
                                <!-- KUNCI TINGGI BARIS 2: 18px (TOTAL 36px) -->
                                <tr style="height: 18px;">
                                    @for($i = 1; $i <= $totalWeeks; $i++)
                                        <th style="width: {{ 32 / $totalWeeks }}%;">M{{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentRowCount = 0; @endphp
                                @foreach($chunk as $originalIndex => $person)
                                    @php $currentRowCount++; @endphp
                                    <tr>
                                        <td>{{ $originalIndex + 1 }}</td>
                                        <td class="text-left font-bold">{{ $person['nama_lengkap'] }} {!! $person['role'] === 'Danru' ? '<span style="color:red; font-size:8px;">(DANRU)</span>' : '' !!}</td>
                                        @for($i = 1; $i <= $totalWeeks; $i++)
                                            <td>{{ isset($person['weekly_scores']['M'.$i]) ? round($person['weekly_scores']['M'.$i]) . '%' : '-' }}</td>
                                        @endfor
                                        <td class="font-bold">{{ $person['avg_percentage'] !== null ? round($person['avg_percentage']) . '%' : '-' }}</td>
                                        @php
                                            $score = $person['avg_percentage'];
                                            $keterangan = '-';
                                            $color = '#000';
                                            if ($score !== null) {
                                                if ($score >= 90) { $keterangan = 'Tercapai'; $color = '#2f855a'; }
                                                else { $keterangan = 'Tidak Tercapai'; $color = '#c53030'; }
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

                                @for($j = $currentRowCount; $j < $rowsPerPage; $j++)
                                    <tr>
                                        <td>&nbsp;</td><td>&nbsp;</td>
                                        @for($i = 1; $i <= $totalWeeks; $i++) <td>&nbsp;</td> @endfor
                                        <td>&nbsp;</td><td>&nbsp;</td>
                                    </tr>
                                @endfor
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #e2e8f0; font-weight: bold;">
                                    <td colspan="{{ $totalWeeks + 2 }}" class="text-right" style="padding-right: 15px;">RATA-RATA KESELURUHAN</td>
                                    <td style="color: #1a365d;">{{ $validPersonCount > 0 ? round($grandTotalScore / $validPersonCount) . '%' : '-' }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>

                    <!-- TENGAH: SPACER PEMISAH (Lebar 2%) -->
                    <td style="width: 2%; padding: 0;"></td>

                    <!-- KANAN: TABEL B & TABEL C (Lebar 41%) -->
                    <td valign="top" style="width: 41%; padding: 0;">

                        <!-- Judul Dipisah dari Tabel -->
                        <div class="title-block">B. REKAP CAPAIAN KPI BULANAN (PER INDIKATOR)</div>

                        <table class="data-table" style="margin-top: 0; margin-bottom: 0; table-layout: fixed;">
                            <thead>
                                <!-- KUNCI TINGGI LANGSUNG 36px BIAR SAMA PERSIS DENGAN TABEL A (18+18) -->
                                <tr style="height: 36px;">
                                    <th style="width: 8%;">NO</th>
                                    <th style="width: 35%;">INDIKATOR KPI</th>
                                    <th style="width: 20%;">RATA-RATA (%)<br>(SEMUA)</th>
                                    <th style="width: 15%;">TARGET</th>
                                    <th style="width: 22%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($detailedMonthlyData['perIndicator']))
                                    @foreach($detailedMonthlyData['perIndicator'] as $index => $indicator)
                                        <tr>
                                            <td style="height: 28px;">{{ $index + 1 }}</td>
                                            <td class="text-left font-bold" style="height: 28px;">{{ $indicator['indikator'] }}</td>
                                            <td class="font-bold" style="height: 28px;">{{ round($indicator['achieved_percentage']) }}%</td>
                                            <td class="font-bold" style="height: 28px;">
                                                {!! $indicator['target_text'] !!}
                                            </td>
                                            @php
                                                $ketColor = $indicator['keterangan'] === 'Tercapai' ? '#2f855a' : '#c53030';
                                            @endphp
                                            <td class="font-bold" style="height: 28px;"><span style="color: {{ $ketColor }}">{{ $indicator['keterangan'] }}</span></td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" style="height: 28px;">&nbsp;</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <!-- TABEL C -->
                        <div class="title-block" style="text-align: center; margin-top: 6px; border-bottom: 1px solid #4a5568;">C. KETERANGAN STATUS</div>
                        <table style="width: 100%; border: 1px solid #4a5568; border-top: none; border-collapse: collapse; background-color: #ffffff; table-layout: fixed;">
                            <tr>
                                <td style="padding: 4px 0; text-align: center;">
                                    <table style="margin: 0 auto; border: none; font-size: 10px; background-color: transparent;">
                                        <tr>
                                            <td style="text-align: right; color: #2f855a; font-weight: bold; padding: 2px 10px 2px 0; border: none;">Tercapai</td>
                                            <td style="text-align: left; font-weight: normal; padding: 2px 0 2px 10px; border: none; color: #333;">: Nilai >= Target</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right; color: #c53030; font-weight: bold; padding: 2px 10px 2px 0; border: none;">Tidak Tercapai</td>
                                            <td style="text-align: left; font-weight: normal; padding: 2px 0 2px 10px; border: none; color: #333;">: Nilai &lt; Target</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

            <!-- BAGIAN BAWAH: CATATAN & TANDA TANGAN -->
            <table style="width: 100%; border: none; font-size: 10px; margin-top: 4px;">
                <tr>
                    <td valign="top" style="width: 57%; padding: 0;">
                        <div class="font-bold" style="color: #1a365d; margin-bottom: 2px; font-size: 10px;">CATATAN:</div>
                        <table style="width: 100%; border: none; font-size: 9px; line-height: 1.4;">
                            <tr><td valign="top" width="12">1.</td><td>Nilai mingguan diperoleh dari Monitoring KPI Mingguan yang dinilai oleh Danru.</td></tr>
                            <tr><td valign="top">2.</td><td>Nilai akhir bulanan merupakan akumulasi rata-rata dari {{ $totalWeeks }} minggu.</td></tr>
                            <tr><td valign="top">3.</td><td>Target KPI mengacu pada target bulanan yang telah ditetapkan.</td></tr>
                        </table>
                    </td>

                    <td style="width: 2%; padding: 0;"></td>

                    <td valign="top" style="width: 41%; padding: 0;">
                        @if($pageIndex == $totalPages - 1)
                            <table class="signature-table">
                                <!-- Baris 1: Judul -->
                                <tr>
                                    <td style="vertical-align: top; padding-bottom: 5px;">
                                        <div class="font-bold" style="font-size: 9px;">DIBUAT OLEH<br>CHIEF SECURITY</div>
                                    </td>
                                    <td style="vertical-align: top; padding-bottom: 5px;">
                                        <div class="font-bold" style="font-size: 9px;">DIKETAHUI OLEH<br>PENGGUNA JASA</div>
                                    </td>
                                </tr>

                                <!-- Baris 2: Ruang Tanda Tangan -->
                                <tr>
                                    <td style="height: 45px; vertical-align: bottom;">
                                        @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_chief_url)
                                            <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanBulananObj->ttd_chief_url)) }}" class="signature-img">
                                        @endif
                                    </td>
                                    <td style="height: 45px; vertical-align: bottom;">
                                        @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_klien_url)
                                            <img src="{{ public_path(str_replace('/storage/', 'storage/', $laporanBulananObj->ttd_klien_url)) }}" class="signature-img">
                                        @endif
                                    </td>
                                </tr>

                                <!-- Baris 3: Garis Bawah, Nama, Tanggal -->
                                <tr>
                                    <td style="vertical-align: top;">
                                        @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_chief_url)
                                            <div style="font-weight: bold; text-decoration: underline; font-size: 10px; line-height: 1.2;">{{ strtoupper($laporanBulananObj->danruPembuat->nama_lengkap ?? 'CHIEF SECURITY') }}</div>
                                        @else
                                            <div style="font-size: 10px; line-height: 1.2; border-bottom: 1px solid #333; width: 85%; margin: 0 auto; color: transparent;">&nbsp;</div>
                                        @endif
                                        <div style="margin-top: 2px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanBulananObj) && $laporanBulananObj->tgl_ttd_chief) ? \Carbon\Carbon::parse($laporanBulananObj->tgl_ttd_chief)->translatedFormat('d/m/Y') : '.........................' }}</div>
                                    </td>
                                    <td style="vertical-align: top;">
                                        @if(isset($laporanBulananObj) && $laporanBulananObj->ttd_klien_url)
                                            <div style="font-weight: bold; text-decoration: underline; font-size: 10px; line-height: 1.2;">PENGGUNA JASA</div>
                                        @else
                                            <div style="font-size: 10px; line-height: 1.2; border-bottom: 1px solid #333; width: 85%; margin: 0 auto; color: transparent;">&nbsp;</div>
                                        @endif
                                        <div style="margin-top: 2px; font-size: 9px; color: #555;">TANGGAL: {{ (isset($laporanBulananObj) && $laporanBulananObj->tgl_ttd_klien) ? \Carbon\Carbon::parse($laporanBulananObj->tgl_ttd_klien)->translatedFormat('d/m/Y') : '.........................' }}</div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>
            </table>

        </div>
    @endforeach
</body>
</html>
