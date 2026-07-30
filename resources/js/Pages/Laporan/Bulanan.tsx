import React, { useState } from "react";
import { router, Head } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { toast } from "sonner";
import { SignaturePad } from "@/components/SignaturePad";
import { PdfExportButton } from "@/components/PdfExportButton";
import { ChevronDown, ChevronUp } from "lucide-react";

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
  all_weekly_approved?: boolean;
  danru_pembuat: DanruPembuat;
}

interface Violation {
  id_catatan: number;
  kategori_indikator: string;
  tingkat_penilaian: string;
  tanggal_penilaian: string;
  deskripsi_penilaian: string;
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
  ttd_url?: string | null;
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
  const [activeTab, setActiveTab] = useState<"anggota" | "danru">("anggota");

  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  const handleFilterChange = (bulan: string, tahun: number, regu?: string | null) => {
    const params: any = { bulan, tahun };
    if (regu) params.filter_regu = regu;
    router.get("/laporan/bulanan", params);
  };

  const handleSaveSignature = (reportId: number, signature: string, isUsingSaved: boolean = false) => {
    if (!signature) {
      toast.warning("Harap tanda tangan terlebih dahulu.");
      return;
    }

    router.post(`/laporan/${reportId}/sign`, {
      signature,
      role: currentUser.role,
      use_saved: isUsingSaved
    }, {
      preserveScroll: true,
      onSuccess: () => {
        setActiveSignatures(prev => {
          const next = { ...prev };
          delete next[reportId];
          return next;
        });
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
    return [1, 2, 3, 4];
  };

  const availableWeeks = getAvailableWeeks(selectedBulan, selectedTahun);

  // Filter based on activeTab (for Chief)
  const visibleLaporan = laporanBulanan.filter(report => {
    if (activeTab === "danru") return report.regu === "Laporan_Danru";
    return report.regu === "Semua";
  });

  const canExportBulanan = visibleLaporan.length > 0;



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

          {currentUser.role === "Chief" && (
            <div className="flex bg-muted/50 p-1 rounded-lg w-fit">
              <button 
                onClick={() => setActiveTab("anggota")} 
                className={`px-4 py-2 text-sm font-semibold rounded-md transition-colors ${activeTab === "anggota" ? "bg-background shadow-sm text-foreground" : "text-muted-foreground hover:text-foreground"}`}
              >
                Laporan Anggota
              </button>
              <button 
                onClick={() => setActiveTab("danru")} 
                className={`px-4 py-2 text-sm font-semibold rounded-md transition-colors ${activeTab === "danru" ? "bg-background shadow-sm text-foreground" : "text-muted-foreground hover:text-foreground"}`}
              >
                Laporan Danru
              </button>
            </div>
          )}
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


          </div>

          <div className="flex flex-col sm:flex-row items-center gap-2 md:border-l md:pl-4 md:ml-auto w-full md:w-auto">
            <PdfExportButton 
              url={`/export/laporan-bulanan?type=pdf&bulan=${selectedBulan}&tahun=${selectedTahun}${filterRegu ? `&filter_regu=${filterRegu}` : ''}${activeTab === 'danru' ? '&jenis=danru' : ''}`}
              disabled={!canExportBulanan}
            />
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
                Alur otorisasi dokumen kinerja: Chief Security &rarr; Klien (Pengguna Jasa).
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
                    <th className="p-3">Status</th>
                    <th className="p-3">Tanda Tangan Chief</th>
                    <th className="p-3">Tanda Tangan Klien</th>
                  </tr>
                </thead>
                <tbody>
                  {visibleLaporan.length > 0 ? (
                    visibleLaporan.map((report) => {
                      const isReviewChief = report.status_dokumen === "Draft" || report.status_dokumen === "Review_Chief";
                      const isReviewKlien = report.status_dokumen === "Review_Klien";

                      const canChiefSign = currentUser.role === "Chief" && isReviewChief && report.all_weekly_approved;
                      const canKlienSign = currentUser.role === "Klien" && isReviewKlien;

                      return (
                        <tr key={report.id_laporan_bulanan} className="border-b align-top hover:bg-muted/5">
                          <td className="p-3 font-bold text-foreground">
                            {report.bulan} {report.tahun}
                          </td>
                          <td className="p-3">{getStatusBadge(report.status_dokumen)}</td>

                          {/* Chief Signature */}
                              <td className="p-3">
                                {report.ttd_chief_url ? (
                                  <img src={report.ttd_chief_url} alt="TTD Chief" className="max-h-16 object-contain border rounded bg-white p-1" />
                                ) : canChiefSign ? (
                                  <div className="min-w-[180px]">
                                    <SignaturePad
                                      label="Tanda Tangan Chief"
                                      savedSignatureUrl={currentUser.ttd_url}
                                      onConfirm={(data, isUsingSaved) => handleSaveSignature(report.id_laporan_bulanan, data, isUsingSaved)}
                                    />
                                  </div>
                                ) : (
                                  <span className="text-xs text-muted-foreground italic">
                                    {currentUser.role === "Chief" && isReviewChief && !report.all_weekly_approved 
                                      ? "Menunggu Laporan Mingguan Disetujui..." 
                                      : "Menunggu..."}
                                  </span>
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
                                      savedSignatureUrl={currentUser.ttd_url}
                                      onConfirm={(data, isUsingSaved) => handleSaveSignature(report.id_laporan_bulanan, data, isUsingSaved)}
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
                      <td colSpan={4} className="p-6 text-center text-muted-foreground italic">
                        Belum ada laporan bulanan.
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
                  const isReviewChief = report.status_dokumen === "Draft" || report.status_dokumen === "Review_Chief";
                  const isReviewKlien = report.status_dokumen === "Review_Klien";

                  const canChiefSign = currentUser.role === "Chief" && isReviewChief && report.all_weekly_approved;
                  const canKlienSign = currentUser.role === "Klien" && isReviewKlien;

                  return (
                    <div key={report.id_laporan_bulanan} className="border p-4 rounded-lg bg-card shadow-sm flex flex-col gap-3">
                      <div className="flex justify-between items-center border-b pb-2">
                        <span className="font-bold text-foreground">{report.bulan} {report.tahun}</span>
                        {getStatusBadge(report.status_dokumen)}
                      </div>
                              
                              <div className="flex flex-col gap-2">
                                <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Tanda Tangan Chief</span>
                                {report.ttd_chief_url ? (
                                  <img src={report.ttd_chief_url} alt="TTD Chief" className="max-h-16 object-contain border rounded bg-white p-1 self-start" />
                                ) : canChiefSign ? (
                                  <SignaturePad
                                    label="Tanda Tangan Chief"
                                    savedSignatureUrl={currentUser.ttd_url}
                                    onConfirm={(data, isUsingSaved) => handleSaveSignature(report.id_laporan_bulanan, data, isUsingSaved)}
                                  />
                                ) : (
                                  <span className="text-xs text-muted-foreground italic">
                                    {currentUser.role === "Chief" && isReviewChief && !report.all_weekly_approved 
                                      ? "Menunggu Laporan Mingguan Disetujui..." 
                                      : "Menunggu..."}
                                  </span>
                                )}
                              </div>
                              
                              <div className="flex flex-col gap-2 pt-2 border-t">
                                <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Tanda Tangan Klien</span>
                                {report.ttd_klien_url ? (
                                  <img src={report.ttd_klien_url} alt="TTD Klien" className="max-h-16 object-contain border rounded bg-white p-1 self-start" />
                                ) : canKlienSign ? (
                                  <SignaturePad
                                    label="Tanda Tangan Klien"
                                    savedSignatureUrl={currentUser.ttd_url}
                                    onConfirm={(data, isUsingSaved) => handleSaveSignature(report.id_laporan_bulanan, data, isUsingSaved)}
                                  />
                                ) : (
                                  <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                                )}
                              </div>
                            </div>
                          );
                })
              ) : (
                <div className="text-center p-8 text-muted-foreground border rounded-xl">
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
