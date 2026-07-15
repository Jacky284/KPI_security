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
  shift: string;
  scores: Record<string, number>;
  total_score: number;
  percentage: number;
  violations: Violation[];
}

interface CurrentUser {
  id_user: number;
  nama_lengkap: string;
  role: "Admin" | "Danru" | "Chief" | "Klien" | "Anggota";
  regu: string | null;
}

interface Props {
  laporanBulanan: LaporanBulanan[];
  performanceData: OfficerPerformance[];
  shiftGroups: Record<string, OfficerPerformance[]>;
  selectedBulan: string;
  selectedTahun: number;
  selectedMinggu: number;
  currentUser: CurrentUser;
}

export default function DashboardLaporan({
  laporanBulanan,
  performanceData,
  shiftGroups,
  selectedBulan,
  selectedTahun,
  selectedMinggu,
  currentUser,
}: Props) {
  const [activeSignatures, setActiveSignatures] = useState<Record<number, string>>({});

  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  const handleFilterChange = (bulan: string, minggu: number, tahun: number) => {
    router.get("/laporan", { bulan, minggu_ke: minggu, tahun });
  };

  const handleSaveSignature = (reportId: number) => {
    const signature = activeSignatures[reportId];
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
        alert("Tanda tangan berhasil disimpan!");
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
      <span className={`px-2.5 py-1 text-xs font-semibold rounded-full border ${styles[status]}`}>
        {status.replace("_", " ")}
      </span>
    );
  };

  const getScoreBadgeColor = (percentage: number) => {
    if (percentage >= 90) return "bg-green-50 text-green-700 border-green-200";
    if (percentage >= 70) return "bg-yellow-50 text-yellow-700 border-yellow-200";
    return "bg-red-50 text-red-700 border-red-200";
  };

  return (
    <>
      <Head title="Dashboard Laporan & Kinerja" />
      <div className="flex flex-col gap-6">
        
        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
          <div>
            <h1 className="text-2xl font-bold text-primary">Dashboard Kinerja & Laporan</h1>
            <p className="text-sm text-muted-foreground">
              Selamat datang, <strong className="text-foreground">{currentUser.nama_lengkap}</strong> (Role: <span className="font-semibold">{currentUser.role}</span>
              {currentUser.regu ? ` - ${currentUser.regu}` : ""})
            </p>
          </div>
        </div>

        {/* Filters */}
        <div className="flex flex-wrap gap-4 items-center bg-card border p-4 rounded-lg shadow-2xs">
          <div className="flex flex-col gap-1">
            <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Bulan</label>
            <select
              value={selectedBulan}
              onChange={(e) => handleFilterChange(e.target.value, selectedMinggu, selectedTahun)}
              className="p-2 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50"
            >
              {listBulan.map(b => <option key={b} value={b}>{b}</option>)}
            </select>
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Minggu Ke</label>
            <select
              value={selectedMinggu}
              onChange={(e) => handleFilterChange(selectedBulan, parseInt(e.target.value), selectedTahun)}
              className="p-2 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50"
            >
              {[1, 2, 3, 4, 5].map(w => <option key={w} value={w}>Minggu {w}</option>)}
            </select>
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Tahun</label>
            <input
              type="number"
              value={selectedTahun}
              onChange={(e) => handleFilterChange(selectedBulan, selectedMinggu, parseInt(e.target.value))}
              className="p-2 w-24 text-sm border rounded-md bg-background focus:ring-2 focus:ring-primary/50"
            />
          </div>
        </div>

        {/* Section 1: Weekly Shift Duty Board */}
        <div>
          <h2 className="text-lg font-black text-foreground uppercase tracking-wider mb-3">
            Daftar Shift Jaga & Kinerja Mingguan (Minggu {selectedMinggu} - {selectedBulan} {selectedTahun})
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {["Shift Pagi", "Shift Siang", "Shift Malam"].map((shiftName) => {
              const officers = shiftGroups[shiftName] || [];
              return (
                <Card key={shiftName} className="shadow-xs border-2">
                  <CardHeader className="bg-muted/55 pb-3">
                    <CardTitle className="text-sm font-black text-primary uppercase tracking-widest">{shiftName}</CardTitle>
                    <CardDescription className="text-xs">
                      Anggota yang bertugas beserta nilai berjalan
                    </CardDescription>
                  </CardHeader>
                  <CardContent className="pt-4 flex flex-col gap-3">
                    {officers.length > 0 ? (
                      officers.map((officer) => (
                        <div key={officer.id_user} className="p-3 border rounded-lg bg-card/50 flex flex-col gap-2 shadow-2xs">
                          <div className="flex justify-between items-center">
                            <div>
                              <h4 className="font-bold text-sm text-foreground">{officer.nama_lengkap}</h4>
                              <p className="text-[10px] text-muted-foreground uppercase">{officer.regu}</p>
                            </div>
                            <span className={`px-2 py-0.5 text-xs font-black rounded-full border ${getScoreBadgeColor(officer.percentage)}`}>
                              {officer.percentage}%
                            </span>
                          </div>

                          {/* Indicator breakdown */}
                          <div className="grid grid-cols-2 gap-1.5 border-t pt-2 mt-1 text-[11px]">
                            <div className="flex justify-between text-muted-foreground">
                              <span>Disiplin:</span>
                              <span className="font-bold text-foreground">{officer.scores.Kedisiplinan}/5</span>
                            </div>
                            <div className="flex justify-between text-muted-foreground">
                              <span>Hadir:</span>
                              <span className="font-bold text-foreground">{officer.scores.Kehadiran}/5</span>
                            </div>
                            <div className="flex justify-between text-muted-foreground">
                              <span>Rapi:</span>
                              <span className="font-bold text-foreground">{officer.scores.Kerapihan}/5</span>
                            </div>
                            <div className="flex justify-between text-muted-foreground">
                              <span>Komunikasi:</span>
                              <span className="font-bold text-foreground">{officer.scores.Komunikasi}/5</span>
                            </div>
                          </div>

                          {/* Violations detail if any */}
                          {officer.violations.length > 0 && (
                            <div className="mt-1 text-[10px] bg-destructive/5 text-destructive p-1.5 rounded border border-destructive/10">
                              <span className="font-bold block uppercase tracking-wider mb-0.5">Catatan Pelanggaran:</span>
                              {officer.violations.map((v) => (
                                <div key={v.id_catatan}>
                                  &bull; [{v.tingkat_pelanggaran}] {v.deskripsi_kejadian}
                                </div>
                              ))}
                            </div>
                          )}
                        </div>
                      ))
                    ) : (
                      <div className="text-center py-6 text-xs text-muted-foreground italic">
                        Tidak ada personil terjadwal
                      </div>
                    )}
                  </CardContent>
                </Card>
              );
            })}
          </div>
        </div>

        {/* Section 2: Monthly Approval Workflow */}
        <Card className="shadow-xs border mt-2">
          <CardHeader>
            <CardTitle className="text-lg font-black text-foreground uppercase tracking-wider">
              Status Tanda Tangan & Laporan Bulanan
            </CardTitle>
            <CardDescription>
              Alur otorisasi dokumen kinerja: Danru &rarr; Chief Security &rarr; Klien (Pengguna Jasa).
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
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
                  {laporanBulanan.map((report) => {
                    const isDraft = report.status_dokumen === "Draft";
                    const isReviewChief = report.status_dokumen === "Review_Chief";
                    const isReviewKlien = report.status_dokumen === "Review_Klien";

                    const canDanruSign = currentUser.role === "Danru" && isDraft;
                    const canChiefSign = currentUser.role === "Chief" && isReviewChief;
                    const canKlienSign = currentUser.role === "Klien" && isReviewKlien;

                    return (
                      <tr key={report.id_laporan_bulanan} className="border-b align-top hover:bg-muted/5">
                        <td className="p-3 font-bold text-foreground">
                          {report.bulan} {report.tahun}
                        </td>
                        <td className="p-3 font-semibold">{report.regu}</td>
                        <td className="p-3">{report.danru_pembuat?.nama_lengkap || "-"}</td>
                        <td className="p-3">{getStatusBadge(report.status_dokumen)}</td>
                        
                        {/* Danru Signature */}
                        <td className="p-3">
                          {report.ttd_danru_url ? (
                            <img src={report.ttd_danru_url} alt="TTD Danru" className="max-h-16 object-contain border rounded bg-white p-1" />
                          ) : canDanruSign ? (
                            <div className="flex flex-col gap-2 min-w-[180px]">
                              <SignaturePad
                                label="Tanda Tangan Danru"
                                onSave={(data) => setActiveSignatures(prev => ({ ...prev, [report.id_laporan_bulanan]: data }))}
                              />
                              <Button
                                size="sm"
                                className="w-full bg-primary font-bold uppercase text-[10px]"
                                onClick={() => handleSaveSignature(report.id_laporan_bulanan)}
                                disabled={!activeSignatures[report.id_laporan_bulanan]}
                              >
                                Simpan Tanda Tangan
                              </Button>
                            </div>
                          ) : (
                            <span className="text-xs text-muted-foreground italic">Menunggu...</span>
                          )}
                        </td>

                        {/* Chief Signature */}
                        <td className="p-3">
                          {report.ttd_chief_url ? (
                            <img src={report.ttd_chief_url} alt="TTD Chief" className="max-h-16 object-contain border rounded bg-white p-1" />
                          ) : canChiefSign ? (
                            <div className="flex flex-col gap-2 min-w-[180px]">
                              <SignaturePad
                                label="Tanda Tangan Chief"
                                onSave={(data) => setActiveSignatures(prev => ({ ...prev, [report.id_laporan_bulanan]: data }))}
                              />
                              <Button
                                size="sm"
                                className="w-full bg-primary font-bold uppercase text-[10px]"
                                onClick={() => handleSaveSignature(report.id_laporan_bulanan)}
                                disabled={!activeSignatures[report.id_laporan_bulanan]}
                              >
                                Simpan Tanda Tangan
                              </Button>
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
                            <div className="flex flex-col gap-2 min-w-[180px]">
                              <SignaturePad
                                label="Tanda Tangan Klien"
                                onSave={(data) => setActiveSignatures(prev => ({ ...prev, [report.id_laporan_bulanan]: data }))}
                              />
                              <Button
                                size="sm"
                                className="w-full bg-primary font-bold uppercase text-[10px]"
                                onClick={() => handleSaveSignature(report.id_laporan_bulanan)}
                                disabled={!activeSignatures[report.id_laporan_bulanan]}
                              >
                                Simpan Tanda Tangan
                              </Button>
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
          </CardContent>
        </Card>
      </div>
    </>
  );
}

DashboardLaporan.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
