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
  is_signable?: boolean;
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
  shift: string;
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
  laporanBulanan: LaporanBulanan[];
  detailedMonthlyData: {
    perPerson: DetailedMonthlyPerson[];
    perIndicator: DetailedMonthlyIndicator[];
  };
  shiftGroups: Record<string, OfficerPerformance[]>;
  selectedBulan: string;
  selectedTahun: number;
  filterRegu?: string | null;
  reguList?: string[];
  currentUser: CurrentUser;
}

export default function LaporanBulanan({
  laporanBulanan,
  detailedMonthlyData,
  selectedBulan,
  selectedTahun,
  filterRegu,
  reguList,
  currentUser,
}: Props) {
  const [activeSignatures, setActiveSignatures] = useState<Record<number, string>>({});
  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  const handleFilterChange = (bulan: string, tahun: number, regu?: string | null) => {
    const params: any = { bulan, tahun };
    if (regu) params.filter_regu = regu;
    router.get("/laporan/bulanan", params);
  };

  const handleSaveSignature = (reportId: number, signature: string) => {
    if (!signature) {
      alert("Harap tanda tangan terlebih dahulu.");
      return;
    }

    router.post(`/laporan/${reportId}/sign`, {
      signature,
      role: currentUser.role,
    }, {
      onSuccess: () => {
        setActiveSignatures(prev => {
          const next = { ...prev };
          delete next[reportId];
          return next;
        });
        alert("Tanda tangan Laporan Bulanan berhasil disimpan!");
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

  const getAvailableWeeks = (bulanStr: string, tahun: number) => {
    const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const monthIndex = months.indexOf(bulanStr);
    if (monthIndex === -1) return [1, 2, 3, 4, 5];
    const firstDay = new Date(tahun, monthIndex, 1);
    const lastDay = new Date(tahun, monthIndex + 1, 0);
    const numDays = lastDay.getDate();
    const firstDayOfWeek = (firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1);
    const totalWeeks = Math.ceil((numDays + firstDayOfWeek) / 7);
    return Array.from({ length: totalWeeks }, (_, i) => i + 1);
  };

  const availableWeeks = getAvailableWeeks(selectedBulan, selectedTahun);
  const canExportBulanan = laporanBulanan.length > 0;

  const visibleLaporan = laporanBulanan;

  return (
    <>
      <Head title="Dashboard Laporan Bulanan" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">

        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 className="text-2xl font-bold text-primary tracking-tight">Dashboard Laporan Bulanan</h1>
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
                onChange={(e) => handleFilterChange(e.target.value, selectedTahun, filterRegu)}
                className="p-2 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50 w-full"
              >
                {listBulan.map(b => <option key={b} value={b}>{b}</option>)}
              </select>
            </div>

            <div className="flex flex-col gap-1">
              <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Tahun</label>
              <input
                type="number"
                value={selectedTahun}
                onChange={(e) => handleFilterChange(selectedBulan, parseInt(e.target.value), filterRegu)}
                className="p-2 w-full md:w-24 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50"
              />
            </div>

            {(currentUser.role === "Chief" || currentUser.role === "Admin") && reguList && reguList.length > 0 && (
              <div className="flex flex-col gap-1 md:border-l md:pl-4 w-full md:w-auto col-span-2 md:col-span-1">
                <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Filter Regu</label>
                <select
                  value={filterRegu || ""}
                  onChange={(e) => handleFilterChange(selectedBulan, selectedTahun, e.target.value)}
                  className="p-2 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50 w-full"
                >
                  <option value="">Semua Regu</option>
                  {reguList.map(r => <option key={r} value={r}>{r}</option>)}
                </select>
              </div>
            )}
          </div>

          <div className="flex flex-col sm:flex-row items-center gap-2 md:border-l md:pl-4 md:ml-auto w-full md:w-auto">
            <Button asChild variant="outline" size="sm" className={`w-full sm:w-auto bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border-green-200 ${!canExportBulanan ? 'pointer-events-none opacity-50' : ''}`}>
              <a href={canExportBulanan ? `/export/laporan-bulanan?type=excel&bulan=${selectedBulan}&tahun=${selectedTahun}${filterRegu ? `&filter_regu=${filterRegu}` : ''}` : '#'} target={canExportBulanan ? "_blank" : undefined}>
                Export Excel
              </a>
            </Button>
            <Button asChild variant="outline" size="sm" className={`w-full sm:w-auto bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200 ${!canExportBulanan ? 'pointer-events-none opacity-50' : ''}`}>
              <a href={canExportBulanan ? `/export/laporan-bulanan?type=pdf&bulan=${selectedBulan}&tahun=${selectedTahun}${filterRegu ? `&filter_regu=${filterRegu}` : ''}` : '#'} target={canExportBulanan ? "_blank" : undefined}>
                Export PDF
              </a>
            </Button>
          </div>
        </div>

        {/* Section 3: Monthly Approval Workflow */}
        <Card className="shadow-xs border mt-2">
          <CardHeader className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle className="text-lg font-black text-foreground uppercase tracking-wider">
                Status Tanda Tangan Laporan Bulanan
              </CardTitle>
              <CardDescription>
                Alur otorisasi dokumen kinerja: Danru &rarr; Chief Security &rarr; Klien (Pengguna Jasa).
              </CardDescription>
            </div>
            <div className="flex items-center gap-2">
              {/* Export Buttons moved to filters */}
            </div>
          </CardHeader>
          <CardContent>
            {/* Desktop View */}
            <div className="hidden lg:block overflow-x-auto">
              <table className="w-full text-left text-sm border-collapse">
                <thead>
                  <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                    <th className="p-3">Periode</th>
                    <th className="p-3">Regu</th>
                    <th className="p-3">Danru Pembuat</th>
                    <th className="p-3">Status</th>
                    <th className="p-3">Tanda Tangan Danru</th>
                    <th className="p-3">Tanda Tangan Chief</th>
                    <th className="p-3">Tanda Tangan Klien</th>
                  </tr>
                </thead>
                <tbody>
                  {visibleLaporan.map((report) => {
                    const isDraft = report.status_dokumen === "Draft";
                    const isReviewChief = report.status_dokumen === "Review_Chief";
                    const isReviewKlien = report.status_dokumen === "Review_Klien";

                    const canDanruSign = currentUser.role === "Danru" && isDraft;
                    const canChiefSign = currentUser.role === "Chief" && isReviewChief;
                    const canKlienSign = currentUser.role === "Klien" && isReviewKlien;
                    const isSignable = report.is_signable !== false;

                    return (
                      <tr key={report.id_laporan_bulanan} className="border-b align-top hover:bg-muted/5">
                        <td className="p-3 font-bold text-foreground">
                          {report.bulan} {report.tahun}
                        </td>
                        <td className="p-3 font-semibold whitespace-nowrap">{report.regu}</td>
                        <td className="p-3">{report.danru_pembuat?.nama_lengkap || "-"}</td>
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
                                  onConfirm={(data) => handleSaveSignature(report.id_laporan_bulanan, data)}
                                />
                              </div>
                            ) : (
                              <span className="text-xs text-muted-foreground italic font-semibold">(Terkunci hingga akhir bulan)</span>
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
                                onConfirm={(data) => handleSaveSignature(report.id_laporan_bulanan, data)}
                              />
                            </div>
                          ) : (
                            <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                          )}
                        </td>

                        {/* Client Signature */}
                        <td className="p-3">
                          {report.ttd_klien_url ? (
                            <img src={report.ttd_klien_url} alt="TTD Klien" className="max-h-16 object-contain border rounded bg-white p-1" />
                          ) : canKlienSign ? (
                            <div className="min-w-[180px]">
                              <SignaturePad
                                label="Tanda Tangan Klien"
                                onConfirm={(data) => handleSaveSignature(report.id_laporan_bulanan, data)}
                              />
                            </div>
                          ) : (
                            <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>

            {/* Mobile View */}
            <div className="lg:hidden flex flex-col gap-4 p-2">
              {visibleLaporan.length > 0 ? (
                visibleLaporan.map((report) => {
                  const isDraft = report.status_dokumen === "Draft";
                  const isReviewChief = report.status_dokumen === "Review_Chief";
                  const isReviewKlien = report.status_dokumen === "Review_Klien";

                  const canDanruSign = currentUser.role === "Danru" && isDraft;
                  const canChiefSign = currentUser.role === "Chief" && isReviewChief;
                  const canKlienSign = currentUser.role === "Klien" && isReviewKlien;
                  const isSignable = report.is_signable !== false;

                  return (
                    <div key={report.id_laporan_bulanan} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                      <div className="flex justify-between items-start border-b pb-3">
                        <div>
                          <div className="font-bold text-foreground text-sm">{report.bulan} {report.tahun}</div>
                          <span className="inline-block mt-1 bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase">{report.regu}</span>
                        </div>
                        <div className="flex flex-col items-end gap-2">
                          {getStatusBadge(report.status_dokumen)}
                          <div className="text-[10px] text-muted-foreground">Oleh: {report.danru_pembuat?.nama_lengkap || "-"}</div>
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
                                  onConfirm={(data) => handleSaveSignature(report.id_laporan_bulanan, data)}
                                />
                              </div>
                            ) : (
                              <span className="text-xs text-muted-foreground italic font-semibold">(Terkunci hingga akhir bulan)</span>
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
                                onConfirm={(data) => handleSaveSignature(report.id_laporan_bulanan, data)}
                              />
                            </div>
                          ) : (
                            <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                          )}
                        </div>

                        {/* Client Sign */}
                        <div className="flex flex-col gap-1 border-t pt-2">
                          <span className="text-[10px] font-bold text-muted-foreground uppercase">Tanda Tangan Klien</span>
                          {report.ttd_klien_url ? (
                            <img src={report.ttd_klien_url} alt="TTD Klien" className="max-h-16 object-contain border rounded bg-white p-1 self-start" />
                          ) : canKlienSign ? (
                            <div className="w-full">
                              <SignaturePad
                                label="Tanda Tangan Klien"
                                onConfirm={(data) => handleSaveSignature(report.id_laporan_bulanan, data)}
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
                  Belum ada laporan bulanan.
                </div>
              )}
            </div>
          </CardContent>
        </Card>

      </div>
    </>
  );
}

LaporanBulanan.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
