import { Head, router } from "@inertiajs/react";
import React, { useState } from "react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

interface Pelanggaran {
  id_catatan: number;
  id_anggota: number;
  id_penilai: number;
  tanggal_penilaian: string;
  minggu_ke: number;
  bulan: string;
  tahun: number;
  kategori_indikator: string;
  tingkat_penilaian: string;
  deskripsi_penilaian: string;
  status_tindak_lanjut: string;
  anggota?: {
    id_user: number;
    nama_lengkap: string;
    regu: string;
    role?: string;
  };
  danru_penilai?: {
    id_user: number;
    nama_lengkap: string;
  };
}

interface Props {
  pelanggaran: Pelanggaran[];
  userRole: string;
  selectedBulan: string;
  selectedTahun: number;
  filterRegu?: string | null;
  reguList?: string[];
}

export default function DaftarPelanggaran({ pelanggaran, userRole, selectedBulan, selectedTahun, filterRegu, reguList }: Props) {
  const [processingId, setProcessingId] = useState<number | null>(null);
  const [activeTab, setActiveTab] = useState<"anggota" | "danru">("anggota");

  const formatDayName = (dateStr: string | null) => {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return days[d.getDay()];
  };

  const formatDateFull = (dateStr: string | null) => {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  };

  const handleTindakLanjut = (id: number) => {
    if (confirm("Tandai pelanggaran ini sudah ditindaklanjuti?")) {
      setProcessingId(id);
      router.patch(`/pelanggaran/${id}/tindak-lanjut`, { status_tindak_lanjut: "Sudah" }, {
        onSuccess: () => alert("Status berhasil diperbarui!"),
        onFinish: () => setProcessingId(null),
      });
    }
  };

  const getBadgeColor = (tingkat: string) => {
    if (["Ringan 1 kali", "Kurang rapi 1 kali", "Terlambat 1 kali", "Komplain ringan"].includes(tingkat)) {
      return "bg-yellow-500/10 text-yellow-600 border-yellow-500/20";
    }
    if (["Ringan 2 kali", "Kurang rapi 2 kali", "Terlambat 2 kali", "Komplain sedang"].includes(tingkat)) {
      return "bg-orange-500/10 text-orange-600 border-orange-500/20";
    }
    if (["Sedang", "Seragam tidak lengkap", "Tidak hadir dengan izin", "Sering mendapat teguran"].includes(tingkat)) {
      return "bg-orange-500/20 text-orange-700 border-orange-500/30";
    }
    if (["Berat", "Penampilan tidak sesuai Standar", "Mangkir / Alpha", "Komplain berat"].includes(tingkat)) {
      return "bg-red-500/10 text-red-600 border-red-500/20";
    }
    return "bg-gray-500/10 text-gray-600 border-gray-500/20";
  };

  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  const handleFilterChange = (bulan: string, tahun: number, regu?: string | null) => {
    const params: any = { bulan, tahun };
    if (regu) params.filter_regu = regu;
    router.get("/pelanggaran/daftar", params);
  };

  return (
    <>
      <Head title="Daftar Pelanggaran" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-primary tracking-tight">Daftar Catatan Pelanggaran Anggota</h1>
            <p className="text-sm text-muted-foreground">Riwayat catatan indisipliner / pelanggaran personel sekuriti. Chief Security wajib menindaklanjuti setiap laporan.</p>
          </div>

          {userRole === 'Chief' && (
            <div className="flex bg-muted/50 p-1 rounded-lg w-fit">
              <button
                onClick={() => setActiveTab("anggota")}
                className={`px-4 py-2 text-sm font-semibold rounded-md transition-colors ${activeTab === "anggota" ? "bg-background shadow-sm text-foreground" : "text-muted-foreground hover:text-foreground"}`}
              >
                Pelanggaran Anggota
              </button>
              <button
                onClick={() => setActiveTab("danru")}
                className={`px-4 py-2 text-sm font-semibold rounded-md transition-colors ${activeTab === "danru" ? "bg-background shadow-sm text-foreground" : "text-muted-foreground hover:text-foreground"}`}
              >
                Pelanggaran Danru
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

            {(userRole === "Chief" || userRole === "Admin") && reguList && reguList.length > 0 && (
              <div className="flex flex-col gap-1 md:border-l md:pl-4 w-full md:w-auto">
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
            <Button asChild variant="outline" size="sm" className="w-full sm:w-auto bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200">
              <a href={`/export/pelanggaran?type=pdf&bulan=${selectedBulan}&tahun=${selectedTahun}${filterRegu ? `&filter_regu=${filterRegu}` : ''}&jenis=${activeTab}`} target="_blank">
                Export PDF
              </a>
            </Button>
          </div>
        </div>

        <Card>
          <CardContent className="pt-0">
            {/* Desktop View */}
            <div className="hidden lg:block overflow-x-auto">
              {(() => {
                const filteredPelanggaran = pelanggaran.filter(p => activeTab === 'danru' ? p.anggota?.role === 'Danru' : p.anggota?.role !== 'Danru');
                return (
                  <table className="w-full text-left text-sm border-collapse">
                    <thead>
                      <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                        <th className="p-3">Tanggal</th>
                        <th className="p-3">{activeTab === 'danru' ? 'Nama Danru' : 'Nama Anggota'}</th>
                        <th className="p-3">Regu</th>
                        <th className="p-3">Pelanggaran</th>
                        <th className="p-3">Tingkat</th>
                        <th className="p-3">{activeTab === 'danru' ? 'Chief Penilai' : 'Danru Penilai'}</th>
                        <th className="p-3 text-center">Tindak Lanjut</th>
                      </tr>
                    </thead>
                    <tbody>
                      {filteredPelanggaran.length > 0 ? (
                        filteredPelanggaran.map((p) => (
                          <tr key={p.id_catatan} className="border-b align-top hover:bg-muted/5">
                            <td className="p-3">
                              <div className="text-xs text-muted-foreground font-semibold uppercase">{formatDayName(p.tanggal_penilaian)}</div>
                              <div className="font-bold text-sm">{formatDateFull(p.tanggal_penilaian)}</div>
                            </td>
                            <td className="p-3 font-bold">
                              <div className="flex items-center gap-2">
                                <span>{p.anggota?.nama_lengkap}</span>
                                {p.anggota?.role === 'Danru' && (
                                  <span className="bg-destructive/15 text-destructive font-bold text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">Danru</span>
                                )}
                              </div>
                            </td>
                            <td className="p-3 font-semibold whitespace-nowrap">{p.anggota?.regu}</td>
                            <td className="p-3">
                              <div className="font-semibold text-primary">{p.kategori_indikator}</div>
                              <div className="text-xs text-muted-foreground mt-1">{p.deskripsi_penilaian}</div>
                            </td>
                            <td className="p-3">
                              <Badge variant="outline" className={getBadgeColor(p.tingkat_penilaian)}>
                                {p.tingkat_penilaian}
                              </Badge>
                            </td>
                            <td className="p-3">{p.danru_penilai?.nama_lengkap}</td>
                            <td className="p-3 text-center">
                              {p.status_tindak_lanjut === "Sudah" ? (
                                <Badge className="bg-green-500/10 text-green-600 border-green-500/20 shadow-none hover:bg-green-500/10">
                                  Sudah
                                </Badge>
                              ) : (
                                <div className="flex flex-col items-center gap-2">
                                  <Badge variant="destructive" className="shadow-none">Belum</Badge>
                                  {(userRole === "Chief" || userRole === "Admin" || userRole === "Danru") && (
                                    <Button
                                      size="sm"
                                      variant="outline"
                                      className="h-7 text-xs"
                                      onClick={() => handleTindakLanjut(p.id_catatan)}
                                      disabled={processingId === p.id_catatan}
                                    >
                                      Tandai Sudah
                                    </Button>
                                  )}
                                </div>
                              )}
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan={7} className="p-6 text-center text-muted-foreground italic">
                            Belum ada catatan pelanggaran yang dilaporkan untuk {activeTab === 'danru' ? 'Danru' : 'Anggota'}.
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                );
              })()}
            </div>

            {/* Mobile View */}
            <div className="lg:hidden flex flex-col gap-4 p-2">
              {(() => {
                const filteredPelanggaran = pelanggaran.filter(p => activeTab === 'danru' ? p.anggota?.role === 'Danru' : p.anggota?.role !== 'Danru');
                return filteredPelanggaran.length > 0 ? (
                  filteredPelanggaran.map((p) => (
                    <div key={p.id_catatan} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                      <div className="flex justify-between items-start border-b pb-3">
                        <div>
                          <div className="flex items-center gap-2">
                            <div className="font-bold text-foreground text-sm">{p.anggota?.nama_lengkap}</div>
                            {p.anggota?.role === 'Danru' && (
                              <span className="bg-destructive/15 text-destructive font-bold text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">Danru</span>
                            )}
                          </div>
                          <span className="inline-block mt-1 bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase">{p.anggota?.regu}</span>
                        </div>
                        <div className="flex flex-col items-end gap-1">
                          <div className="text-[10px] text-muted-foreground font-semibold uppercase">{formatDayName(p.tanggal_penilaian)}</div>
                          <div className="font-bold text-xs">{formatDateFull(p.tanggal_penilaian)}</div>
                        </div>
                      </div>

                      <div className="flex flex-col gap-1 mt-1">
                        <div className="flex justify-between items-center">
                          <span className="font-semibold text-primary text-sm">{p.kategori_indikator}</span>
                          <Badge variant="outline" className={`text-[10px] ${getBadgeColor(p.tingkat_penilaian)}`}>
                            {p.tingkat_penilaian}
                          </Badge>
                        </div>
                        <p className="text-xs text-muted-foreground mt-1 bg-muted/30 p-2 rounded border">
                          {p.deskripsi_penilaian}
                        </p>
                        <div className="text-[10px] text-muted-foreground mt-2">
                          {activeTab === 'danru' ? 'Chief Penilai:' : 'Danru Penilai:'} <span className="font-semibold">{p.danru_penilai?.nama_lengkap}</span>
                        </div>
                      </div>

                      <div className="flex justify-between items-center border-t pt-3 mt-1">
                        <span className="text-xs font-bold text-muted-foreground uppercase">Tindak Lanjut</span>
                        {p.status_tindak_lanjut === "Sudah" ? (
                          <Badge className="bg-green-500/10 text-green-600 border-green-500/20 shadow-none hover:bg-green-500/10">
                            Sudah
                          </Badge>
                        ) : (
                          <div className="flex items-center gap-2">
                            <Badge variant="destructive" className="shadow-none">Belum</Badge>
                            {(userRole === "Chief" || userRole === "Admin" || userRole === "Danru") && (
                              <Button
                                size="sm"
                                variant="outline"
                                className="h-7 text-xs"
                                onClick={() => handleTindakLanjut(p.id_catatan)}
                                disabled={processingId === p.id_catatan}
                              >
                                Tandai Sudah
                              </Button>
                            )}
                          </div>
                        )}
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="p-8 text-center text-muted-foreground border rounded-xl border-dashed">
                    Belum ada catatan pelanggaran yang dilaporkan untuk {activeTab === 'danru' ? 'Danru' : 'Anggota'}.
                  </div>
                );
              })()}
            </div>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

DaftarPelanggaran.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
