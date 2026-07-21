import { Head, router } from "@inertiajs/react";
import React, { useState } from "react";
import DashboardLayout from "@/layouts/DashboardLayout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

interface Pelanggaran {
  id_catatan: number;
  id_anggota: number;
  id_danru_penilai: number;
  tanggal_kejadian: string;
  minggu_ke: number;
  bulan: string;
  tahun: number;
  kategori_indikator: string;
  tingkat_pelanggaran: string;
  deskripsi_kejadian: string;
  status_tindak_lanjut: string;
  anggota?: {
    id_user: number;
    nama_lengkap: string;
    regu: string;
  };
  danru_penilai?: {
    id_user: number;
    nama_lengkap: string;
  };
}

interface Props {
  pelanggaran: Pelanggaran[];
  userRole: string;
}

export default function DaftarPelanggaran({ pelanggaran, userRole }: Props) {
  const [processingId, setProcessingId] = useState<number | null>(null);

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
    switch (tingkat) {
      case "Ringan":
        return "bg-yellow-500/10 text-yellow-600 border-yellow-500/20";
      case "Sedang":
        return "bg-orange-500/10 text-orange-600 border-orange-500/20";
      case "Berat":
        return "bg-red-500/10 text-red-600 border-red-500/20";
      default:
        return "";
    }
  };

  return (
    <>
      <Head title="Daftar Pelanggaran" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div>
          <h1 className="text-2xl font-bold text-primary tracking-tight">Daftar Catatan Pelanggaran</h1>
          <p className="text-sm text-muted-foreground">Riwayat pelanggaran kinerja personel sekuriti. Chief Security wajib menindaklanjuti setiap laporan.</p>
        </div>
        <Card>
          <CardContent className="pt-6">
            {/* Desktop View */}
            <div className="hidden lg:block overflow-x-auto">
              <table className="w-full text-left text-sm border-collapse">
                <thead>
                  <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                    <th className="p-3">Tanggal</th>
                    <th className="p-3">Nama Anggota</th>
                    <th className="p-3">Regu</th>
                    <th className="p-3">Pelanggaran</th>
                    <th className="p-3">Tingkat</th>
                    <th className="p-3">Danru Pelapor</th>
                    <th className="p-3 text-center">Tindak Lanjut</th>
                  </tr>
                </thead>
                <tbody>
                  {pelanggaran.length > 0 ? (
                    pelanggaran.map((p) => (
                      <tr key={p.id_catatan} className="border-b align-top hover:bg-muted/5">
                        <td className="p-3">
                          <div className="font-semibold">{p.tanggal_kejadian}</div>
                          <div className="text-xs text-muted-foreground">M{p.minggu_ke} - {p.bulan} {p.tahun}</div>
                        </td>
                        <td className="p-3 font-bold">{p.anggota?.nama_lengkap}</td>
                        <td className="p-3 font-semibold whitespace-nowrap">{p.anggota?.regu}</td>
                        <td className="p-3">
                          <div className="font-semibold text-primary">{p.kategori_indikator}</div>
                          <div className="text-xs text-muted-foreground mt-1">{p.deskripsi_kejadian}</div>
                        </td>
                        <td className="p-3">
                          <Badge variant="outline" className={getBadgeColor(p.tingkat_pelanggaran)}>
                            {p.tingkat_pelanggaran}
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
                              {(userRole === "Chief" || userRole === "Admin") && (
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
                        Belum ada catatan pelanggaran yang dilaporkan.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {/* Mobile View */}
            <div className="lg:hidden flex flex-col gap-4 p-2">
              {pelanggaran.length > 0 ? (
                pelanggaran.map((p) => (
                  <div key={p.id_catatan} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                    <div className="flex justify-between items-start border-b pb-3">
                      <div>
                        <div className="font-bold text-foreground text-sm">{p.anggota?.nama_lengkap}</div>
                        <span className="inline-block mt-1 bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase">{p.anggota?.regu}</span>
                      </div>
                      <div className="flex flex-col items-end gap-1">
                        <div className="font-semibold text-xs">{p.tanggal_kejadian}</div>
                        <div className="text-[10px] text-muted-foreground">M{p.minggu_ke} - {p.bulan} {p.tahun}</div>
                      </div>
                    </div>
                    
                    <div className="flex flex-col gap-1 mt-1">
                      <div className="flex justify-between items-center">
                        <span className="font-semibold text-primary text-sm">{p.kategori_indikator}</span>
                        <Badge variant="outline" className={`text-[10px] ${getBadgeColor(p.tingkat_pelanggaran)}`}>
                          {p.tingkat_pelanggaran}
                        </Badge>
                      </div>
                      <p className="text-xs text-muted-foreground mt-1 bg-muted/30 p-2 rounded border">
                        {p.deskripsi_kejadian}
                      </p>
                      <div className="text-[10px] text-muted-foreground mt-2">
                        Dilaporkan oleh: <span className="font-semibold">{p.danru_penilai?.nama_lengkap}</span>
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
                          {(userRole === "Chief" || userRole === "Admin") && (
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
                  Belum ada catatan pelanggaran yang dilaporkan.
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

DaftarPelanggaran.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
