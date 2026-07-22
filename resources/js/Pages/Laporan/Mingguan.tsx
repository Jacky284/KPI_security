import React, { useState } from "react";
import { router, Head } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { SignaturePad } from "@/components/SignaturePad";

interface DanruPembuat {
  id_user: number;
  nama_lengkap: string;
}

interface LaporanBulanan {
  id_laporan_bulanan: number;
  id_danru_pembuat: number;
  regu: string;
  bulan: string;
  tahun: number;
  status_dokumen: "Draft" | "Review_Chief" | "Review_Klien" | "Approved";
  ttd_danru_url: string | null;
  ttd_chief_url: string | null;
  ttd_klien_url: string | null;
  file_pdf_url: string | null;
  danru_pembuat: DanruPembuat;
}

interface Violation {
  id_catatan: number;
  kategori_indikator: string;
  tingkat_pelanggaran: string;
  tanggal_kejadian: string;
  deskripsi_kejadian: string;
}

interface OfficerPerformance {
  id_user: number;
  nama_lengkap: string;
  regu: string;
  scores: Record<string, number>;
  total_score: number;
  percentage: number;
  violations: Violation[];
}

interface LaporanMingguan {
  id_laporan_mingguan: number;
  id_danru: number;
  regu: string;
  minggu_ke: number;
  bulan: string;
  tahun: number;
  status_dokumen: "Draft" | "Review_Chief" | "Approved";
  ttd_danru_url: string | null;
  ttd_chief_url: string | null;
  danru: DanruPembuat;
  is_signable?: boolean;
}

interface DetailedMonthlyPerson {
  id_user: number;
  nama_lengkap: string;
  regu: string;
  weekly_scores: Record<string, number>;
  avg_percentage: number;
  penilaian: string;
}

interface DetailedMonthlyIndicator {
  indikator: string;
  target: number;
  achieved_percentage: number;
  keterangan: string;
}

interface CurrentUser {
  id_user: number;
  nama_lengkap: string;
  role: "Admin" | "Danru" | "Chief" | "Klien" | "Anggota";
  regu: string | null;
}

interface Props {
  laporanMingguan: LaporanMingguan[];
  performanceData: OfficerPerformance[];
  shiftGroups: Record<string, OfficerPerformance[]>;
  selectedBulan: string;
  selectedTahun: number;
  selectedMinggu: number;
  filterRegu?: string | null;
  reguList?: string[];
  currentUser: CurrentUser;
}

export default function LaporanMingguan({
  laporanMingguan,
  performanceData,
  selectedBulan,
  selectedTahun,
  selectedMinggu,
  filterRegu,
  reguList,
  currentUser,
}: Props) {
  const [activeMingguanSignatures, setActiveMingguanSignatures] = useState<Record<number, string>>({});

  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  const getAvailableWeeks = (bulan: string, tahun: number) => {
    return [1, 2, 3, 4];
  };

  const handleFilterChange = (bulan: string, minggu: number, tahun: number, regu?: string | null) => {
    const available = getAvailableWeeks(bulan, tahun);
    if (!available.includes(minggu)) {
      minggu = available[available.length - 1]; // Clamp to the last available week if out of bounds
    }
    const params: any = { bulan, minggu_ke: minggu, tahun };
    if (regu) params.filter_regu = regu;
    router.get("/laporan/mingguan", params);
  };
  const handleSaveMingguanSignature = (reportId: number, signature: string) => {
    if (!signature) {
      alert("Harap tanda tangan terlebih dahulu.");
      return;
    }

    router.post(`/laporan/mingguan/${reportId}/sign`, {
      signature,
      role: currentUser.role,
    }, {
      onSuccess: () => {
        setActiveMingguanSignatures(prev => {
          const next = { ...prev };
          delete next[reportId];
          return next;
        });
        alert("Tanda tangan Laporan Mingguan berhasil disimpan!");
      }
    });
  };

  const getStatusBadge = (status: LaporanBulanan["status_dokumen"]) => {
    const styles = {
      Draft: "bg-blue-50 text-blue-700 border-blue-200",
      Review_Chief: "bg-yellow-50 text-yellow-700 border-yellow-200",
      Review_Klien: "bg-orange-50 text-orange-700 border-orange-200",
      Approved: "bg-green-50 text-green-700 border-green-200",
    };
    return (
      <span className={`inline-block whitespace-nowrap px-2.5 py-1 text-xs font-semibold rounded-full border ${styles[status]}`}>
        {status.replace("_", " ")}
      </span>
    );
  };

  const getScoreBadgeColor = (percentage: number) => {
    if (percentage >= 90) return "bg-green-50 text-green-700 border-green-200";
    if (percentage >= 70) return "bg-yellow-50 text-yellow-700 border-yellow-200";
    return "bg-red-50 text-red-700 border-red-200";
  };

  const availableWeeks = getAvailableWeeks(selectedBulan, selectedTahun);

  const currentWeekReport = laporanMingguan.find(r => r.minggu_ke === selectedMinggu);
  const canExportMingguan = currentWeekReport !== undefined;

  const visibleLaporan = laporanMingguan.filter(r =>
    r.status_dokumen !== "Draft" || ('is_signable' in r ? (r as any).is_signable !== false : true)
  );

  return (
    <>
      <Head title="Dashboard Laporan Mingguan" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">

        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 className="text-2xl font-bold text-primary tracking-tight">Dashboard Laporan Mingguan</h1>
            <p className="text-sm text-muted-foreground">
              Selamat datang, <strong className="text-foreground">{currentUser.nama_lengkap}</strong> (Role: <span className="font-semibold">{currentUser.role}</span>
              {currentUser.regu ? ` - ${currentUser.regu}` : ""})
            </p>
          </div>
        </div>

        {/* Filters */}
        <div className="flex flex-col md:flex-row md:flex-wrap gap-4 md:items-center bg-card border p-4 rounded-lg shadow-2xs">
          <div className="grid grid-cols-2 md:flex md:flex-row gap-4 items-center w-full md:w-auto">
            <div className="flex flex-col gap-1">
              <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Bulan</label>
              <select
                value={selectedBulan}
                onChange={(e) => handleFilterChange(e.target.value, selectedMinggu, selectedTahun, filterRegu)}
                className="p-2 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50 w-full"
              >
                {listBulan.map(b => <option key={b} value={b}>{b}</option>)}
              </select>
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Minggu Ke</label>
              <select
                value={selectedMinggu}
                onChange={(e) => handleFilterChange(selectedBulan, parseInt(e.target.value), selectedTahun, filterRegu)}
                className="p-2 border rounded-md text-sm bg-background w-full"
              >
                {availableWeeks.map(w => <option key={w} value={w}>Minggu {w}</option>)}
              </select>
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Tahun</label>
              <input
                type="number"
                value={selectedTahun}
                onChange={(e) => handleFilterChange(selectedBulan, selectedMinggu, parseInt(e.target.value), filterRegu)}
                className="p-2 w-full md:w-24 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50"
              />
            </div>

            {(currentUser.role === "Chief" || currentUser.role === "Admin") && reguList && reguList.length > 0 && (
              <div className="flex flex-col gap-1 md:border-l md:pl-4 w-full md:w-auto">
                <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Filter Regu</label>
                <select
                  value={filterRegu || ""}
                  onChange={(e) => handleFilterChange(selectedBulan, selectedMinggu, selectedTahun, e.target.value)}
                  className="p-2 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50 w-full"
                >
                  <option value="">Semua Regu</option>
                  {reguList.map(r => <option key={r} value={r}>{r}</option>)}
                </select>
              </div>
            )}
          </div>

          <div className="flex flex-col sm:flex-row items-center gap-2 md:border-l md:pl-4 md:ml-auto w-full md:w-auto">
            <Button asChild variant="outline" size="sm" className={`w-full sm:w-auto bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border-green-200 ${!canExportMingguan ? 'pointer-events-none opacity-50' : ''}`}>
              <a href={canExportMingguan ? `/export/laporan?type=excel&minggu_ke=${selectedMinggu}&bulan=${selectedBulan}&tahun=${selectedTahun}${filterRegu ? `&filter_regu=${filterRegu}` : ''}` : '#'} target={canExportMingguan ? "_blank" : undefined}>
                Export Excel
              </a>
            </Button>
            <Button asChild variant="outline" size="sm" className={`w-full sm:w-auto bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200 ${!canExportMingguan ? 'pointer-events-none opacity-50' : ''}`}>
              <a href={canExportMingguan ? `/export/laporan?type=pdf&minggu_ke=${selectedMinggu}&bulan=${selectedBulan}&tahun=${selectedTahun}${filterRegu ? `&filter_regu=${filterRegu}` : ''}` : '#'} target={canExportMingguan ? "_blank" : undefined}>
                Export PDF
              </a>
            </Button>
          </div>
        </div>

        {/* Section 1: Weekly Performance Table */}
        <Card className="shadow-xs border mt-2">
          <CardHeader className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-muted/30 pb-3">
            <div>
              <CardTitle className="text-lg font-black text-foreground uppercase tracking-wider">
                Detail Laporan Mingguan (Minggu {selectedMinggu} - {selectedBulan} {selectedTahun})
              </CardTitle>
              <CardDescription>
                Rekap nilai kinerja dan indikator per orang untuk minggu ini.
              </CardDescription>
            </div>
          </CardHeader>
          <CardContent className="pt-4">
            {/* Desktop View */}
            <div className="hidden lg:block overflow-x-auto">
              <table className="w-full text-left text-sm border-collapse">
                <thead>
                  <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                    <th className="p-3">Nama Anggota</th>
                    <th className="p-3">Regu</th>
                    <th className="p-3 text-center">Kedisiplinan</th>
                    <th className="p-3 text-center">Kehadiran</th>
                    <th className="p-3 text-center">Kerapihan</th>
                    <th className="p-3 text-center">Komunikasi</th>
                    <th className="p-3 text-center">Total Skor</th>
                    <th className="p-3 text-center">Persentase</th>
                  </tr>
                </thead>
                <tbody>
                  {performanceData.length > 0 ? (
                    performanceData.map((officer) => (
                      <tr key={officer.id_user} className="border-b align-top hover:bg-muted/5">
                        <td className="p-3 font-bold text-foreground">
                          {officer.nama_lengkap}
                          {officer.violations.length > 0 && (
                            <div className="mt-1 text-[10px] text-destructive italic font-normal">
                              Ada {officer.violations.length} pelanggaran
                            </div>
                          )}
                        </td>
                        <td className="p-3 font-semibold whitespace-nowrap">{officer.regu}</td>
                        <td className="p-3 text-center">{officer.scores.Kedisiplinan ?? '-'}</td>
                        <td className="p-3 text-center">{officer.scores.Kehadiran ?? '-'}</td>
                        <td className="p-3 text-center">{officer.scores.Kerapihan ?? '-'}</td>
                        <td className="p-3 text-center">{officer.scores.Komunikasi ?? '-'}</td>
                        <td className="p-3 text-center font-semibold">{officer.total_score ?? '-'}</td>
                        <td className="p-3 text-center">
                          {officer.percentage !== null ? (
                            <span className={`px-2 py-0.5 text-xs font-black rounded border ${getScoreBadgeColor(officer.percentage)}`}>
                              {officer.percentage}%
                            </span>
                          ) : (
                            <span className="text-muted-foreground">-</span>
                          )}
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan={8} className="p-6 text-center text-muted-foreground italic">
                        Tidak ada personil terjadwal.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {/* Mobile View */}
            <div className="lg:hidden flex flex-col gap-4 p-2">
              {performanceData.length > 0 ? (
                performanceData.map((officer) => (
                  <div key={officer.id_user} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                    <div className="flex justify-between items-start border-b pb-2">
                      <div>
                        <div className="font-bold text-foreground text-sm">{officer.nama_lengkap}</div>
                        {officer.violations.length > 0 && (
                          <div className="text-[10px] text-destructive italic font-normal mt-0.5">
                            Ada {officer.violations.length} pelanggaran
                          </div>
                        )}
                        <span className="inline-block mt-1 bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase">{officer.regu}</span>
                      </div>
                      <div className="flex flex-col items-end gap-1">
                        <span className="text-xs text-muted-foreground font-semibold">Skor: {officer.total_score ?? '-'}</span>
                        {officer.percentage !== null ? (
                          <span className={`px-2 py-0.5 text-[10px] font-black rounded border ${getScoreBadgeColor(officer.percentage)}`}>
                            {officer.percentage}%
                          </span>
                        ) : (
                          <span className="text-muted-foreground text-xs">-</span>
                        )}
                      </div>
                    </div>
                    <div className="grid grid-cols-4 gap-2 text-center text-[10px] font-semibold text-muted-foreground mt-1">
                      <div className="flex flex-col bg-muted/30 p-2 rounded-lg border">
                        <span className="mb-1 truncate">Kedisiplinan</span>
                        <span className="text-foreground text-xs">{officer.scores.Kedisiplinan ?? '-'}</span>
                      </div>
                      <div className="flex flex-col bg-muted/30 p-2 rounded-lg border">
                        <span className="mb-1 truncate">Kehadiran</span>
                        <span className="text-foreground text-xs">{officer.scores.Kehadiran ?? '-'}</span>
                      </div>
                      <div className="flex flex-col bg-muted/30 p-2 rounded-lg border">
                        <span className="mb-1 truncate">Kerapihan</span>
                        <span className="text-foreground text-xs">{officer.scores.Kerapihan ?? '-'}</span>
                      </div>
                      <div className="flex flex-col bg-muted/30 p-2 rounded-lg border">
                        <span className="mb-1 truncate">Komunikasi</span>
                        <span className="text-foreground text-xs">{officer.scores.Komunikasi ?? '-'}</span>
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="p-8 text-center text-muted-foreground border rounded-xl border-dashed">
                  Tidak ada personil terjadwal.
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Section 2: Weekly Approval Workflow */}
        <Card className="shadow-xs border mt-2">
          <CardHeader className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle className="text-lg font-black text-foreground uppercase tracking-wider">
                Status Tanda Tangan Laporan Mingguan
              </CardTitle>
              <CardDescription>
                Alur otorisasi laporan mingguan: Danru &rarr; Chief Security.
              </CardDescription>
            </div>
          </CardHeader>
          <CardContent>
            {/* Desktop View */}
            <div className="hidden lg:block overflow-x-auto">
              <table className="w-full text-left text-sm border-collapse">
                <thead>
                  <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                    <th className="p-3">Minggu Ke</th>
                    <th className="p-3">Periode</th>
                    <th className="p-3">Regu</th>
                    <th className="p-3">Danru Pembuat</th>
                    <th className="p-3">Status</th>
                    <th className="p-3">Tanda Tangan Danru</th>
                    <th className="p-3">Tanda Tangan Chief</th>
                  </tr>
                </thead>
                <tbody>
                  {visibleLaporan.length > 0 ? (
                    visibleLaporan.map((report) => {
                      const isDraft = report.status_dokumen === "Draft";
                      const isReviewChief = report.status_dokumen === "Review_Chief";
                      const canDanruSign = currentUser.role === "Danru" && isDraft;
                      const canChiefSign = currentUser.role === "Chief" && isReviewChief;
                      const isSignable = 'is_signable' in report ? (report as any).is_signable !== false : true;

                      return (
                        <tr key={report.id_laporan_mingguan} className="border-b align-top hover:bg-muted/5">
                          <td className="p-3 font-bold text-foreground">Minggu {report.minggu_ke}</td>
                          <td className="p-3">{report.bulan} {report.tahun}</td>
                          <td className="p-3 font-semibold whitespace-nowrap">{report.regu}</td>
                          <td className="p-3">{report.danru?.nama_lengkap || "-"}</td>
                          <td className="p-3">{getStatusBadge(report.status_dokumen)}</td>

                          {/* Danru Signature */}
                          <td className="p-3">
                            {report.ttd_danru_url ? (
                              <img src={report.ttd_danru_url} alt="TTD Danru" className="max-h-16 object-contain border rounded bg-white p-1" />
                            ) : canDanruSign ? (
                              isSignable ? (
                                <div className="min-w-[180px]">
                                  <SignaturePad
                                    label="Tanda Tangan Danru"
                                    onConfirm={(data) => handleSaveMingguanSignature(report.id_laporan_mingguan, data)}
                                  />
                                </div>
                              ) : (
                                <span className="text-xs text-muted-foreground italic font-semibold">(Terkunci hingga minggu berakhir)</span>
                              )
                            ) : (
                              <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                            )}
                          </td>

                          {/* Chief Signature */}
                          <td className="p-3">
                            {report.ttd_chief_url ? (
                              <img src={report.ttd_chief_url} alt="TTD Chief" className="max-h-16 object-contain border rounded bg-white p-1" />
                            ) : canChiefSign ? (
                              <div className="min-w-[180px]">
                                <SignaturePad
                                  label="Tanda Tangan Chief"
                                  onConfirm={(data) => handleSaveMingguanSignature(report.id_laporan_mingguan, data)}
                                />
                              </div>
                            ) : (
                              <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                            )}
                          </td>
                        </tr>
                      );
                    })
                  ) : (
                    <tr>
                      <td colSpan={7} className="p-6 text-center text-muted-foreground italic">
                        Belum ada laporan mingguan.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {/* Mobile View */}
            <div className="lg:hidden flex flex-col gap-4 p-2">
              {visibleLaporan.length > 0 ? (
                visibleLaporan.map((report) => {
                  const isDraft = report.status_dokumen === "Draft";
                  const isReviewChief = report.status_dokumen === "Review_Chief";
                  const canDanruSign = currentUser.role === "Danru" && isDraft;
                  const canChiefSign = currentUser.role === "Chief" && isReviewChief;
                  const isSignable = report.is_signable !== false;

                  return (
                    <div key={report.id_laporan_mingguan} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                      <div className="flex justify-between items-start border-b pb-3">
                        <div>
                          <div className="font-bold text-foreground text-sm">Minggu {report.minggu_ke}</div>
                          <div className="text-xs text-muted-foreground">{report.bulan} {report.tahun}</div>
                          <span className="inline-block mt-1 bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase">{report.regu}</span>
                        </div>
                        <div className="flex flex-col items-end gap-2">
                          {getStatusBadge(report.status_dokumen)}
                          <div className="text-[10px] text-muted-foreground">Oleh: {report.danru?.nama_lengkap || "-"}</div>
                        </div>
                      </div>
                      <div className="flex flex-col gap-3 mt-1">
                        {/* Danru Sign */}
                        <div className="flex flex-col gap-1">
                          <span className="text-[10px] font-bold text-muted-foreground uppercase">Tanda Tangan Danru</span>
                          {report.ttd_danru_url ? (
                            <img src={report.ttd_danru_url} alt="TTD Danru" className="max-h-16 object-contain border rounded bg-white p-1 self-start" />
                          ) : canDanruSign ? (
                            isSignable ? (
                              <div className="w-full">
                                <SignaturePad
                                  label="Tanda Tangan Danru"
                                  onConfirm={(data) => handleSaveMingguanSignature(report.id_laporan_mingguan, data)}
                                />
                              </div>
                            ) : (
                              <span className="text-xs text-muted-foreground italic font-semibold">(Terkunci hingga minggu berakhir)</span>
                            )
                          ) : (
                            <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                          )}
                        </div>

                        {/* Chief Sign */}
                        <div className="flex flex-col gap-1 border-t pt-2">
                          <span className="text-[10px] font-bold text-muted-foreground uppercase">Tanda Tangan Chief</span>
                          {report.ttd_chief_url ? (
                            <img src={report.ttd_chief_url} alt="TTD Chief" className="max-h-16 object-contain border rounded bg-white p-1 self-start" />
                          ) : canChiefSign ? (
                            <div className="w-full">
                              <SignaturePad
                                label="Tanda Tangan Chief"
                                onConfirm={(data) => handleSaveMingguanSignature(report.id_laporan_mingguan, data)}
                              />
                            </div>
                          ) : (
                            <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                          )}
                        </div>
                      </div>
                    </div>
                  );
                })
              ) : (
                <div className="p-8 text-center text-muted-foreground border rounded-xl border-dashed">
                  Belum ada laporan mingguan.
                </div>
              )}
            </div>
          </CardContent>
        </Card>



      </div>
    </>
  );
}

LaporanMingguan.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
