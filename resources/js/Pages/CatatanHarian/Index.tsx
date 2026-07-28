import React, { useState } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Plus, FileText } from "lucide-react";

interface Anggota {
  id_user: number;
  nama_lengkap: string;
  regu: string;
  role?: string;
}

interface CatatanHarian {
  id_catatan: number;
  id_danru: number;
  id_anggota: number;
  tanggal: string;
  jam_kejadian?: string | null;
  shift?: string | null;
  pos_lokasi?: string | null;
  minggu_ke: number;
  bulan: string;
  tahun: number;
  indikator: string;
  deskripsi: string;
  arahan: string | null;
  status_tindak_lanjut: "Sudah" | "Belum";
  keterangan: string | null;
  created_at?: string;
  anggota?: { id_user: number; nama_lengkap: string; regu: string; role?: string };
  danru?: { id_user: number; nama_lengkap: string };
}

interface Props {
  catatan: CatatanHarian[];
  anggota: Anggota[];
  userRole: string;
  selectedBulan: string;
  selectedTahun: number;
  selectedMinggu: number;
  filterRegu?: string;
  reguList?: string[];
}

export default function CatatanHarianIndex({
  catatan,
  anggota,
  userRole,
  selectedBulan,
  selectedTahun,
  selectedMinggu,
  filterRegu,
  reguList,
}: Props) {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [processingId, setProcessingId] = useState<number | null>(null);

  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];
  const availableWeeks = [1, 2, 3, 4];

  const handleFilterChange = (bulan: string, minggu: number, tahun: number, regu?: string) => {
    const params: any = { bulan, minggu_ke: minggu, tahun };
    if (regu) params.filter_regu = regu;
    router.get("/catatan-harian", params);
  };

  const getCurrentTimeString = () => {
    const d = new Date();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
  };

  const formatTime = (jamKejadian?: string | null, createdAt?: string) => {
    if (jamKejadian) {
      return ` (${jamKejadian.substring(0, 5)})`;
    }
    if (createdAt) {
      const d = new Date(createdAt);
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      return ` (${hours}:${minutes})`;
    }
    return "";
  };

  const { data, setData, post, processing, errors, reset } = useForm({
    id_anggota: "",
    tanggal: new Date().toISOString().split('T')[0],
    jam_kejadian: getCurrentTimeString(),
    shift: "Pagi",
    pos_lokasi: "",
    indikator: userRole === "Chief" ? "Pengawasan Personel" : "Disiplin Kerja",
    deskripsi: "",
    arahan: "",
    status_tindak_lanjut: "Belum",
    keterangan: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/catatan-harian", {
      onSuccess: () => {
        setIsModalOpen(false);
        reset();
      },
    });
  };

  const handleTindakLanjut = (id: number) => {
    if (confirm("Tandai catatan harian ini sudah ditindaklanjuti?")) {
      setProcessingId(id);
      router.patch(`/catatan-harian/${id}/tindak-lanjut`, { status_tindak_lanjut: "Sudah" }, {
        onSuccess: () => alert("Status berhasil diperbarui!"),
        onFinish: () => setProcessingId(null),
      });
    }
  };

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

  return (
    <>
      <Head title="Catatan Harian" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-primary tracking-tight">
              Catatan Harian {userRole === 'Admin' ? 'Danru & Chief' : (userRole === 'Chief' ? 'Chief' : 'Danru')}
            </h1>
            <p className="text-sm text-muted-foreground">Log aktivitas dan temuan harian. Tidak memotong poin KPI utama.</p>
          </div>
          
          <div className="flex flex-col md:flex-row gap-2">
          {(userRole === "Danru" || userRole === "Admin" || userRole === "Chief") && (
            <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
              <DialogTrigger asChild>
                <Button className="w-full md:w-auto shadow-md">
                  <Plus className="w-4 h-4 mr-2" /> Input Catatan
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                  <DialogTitle>Input Catatan Harian</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="flex flex-col gap-4 mt-2">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="flex flex-col gap-1.5">
                      <label className="text-sm font-semibold">Tanggal</label>
                      <input
                        type="date"
                        value={data.tanggal}
                        onChange={(e) => setData("tanggal", e.target.value)}
                        className="p-2 border rounded-md"
                        required
                      />
                    </div>
                    <div className="flex flex-col gap-1.5">
                      <label className="text-sm font-semibold">Jam Kejadian</label>
                      <input
                        type="time"
                        value={data.jam_kejadian}
                        onChange={(e) => setData("jam_kejadian", e.target.value)}
                        className="p-2 border rounded-md"
                        required
                      />
                    </div>
                  </div>
                  
                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">
                      {userRole === "Chief" || userRole === "Admin" ? "Personel" : "Anggota"}
                    </label>
                    <select
                      value={data.id_anggota}
                      onChange={(e) => {
                        const newId = e.target.value;
                        const selectedPerson = anggota.find(a => a.id_user.toString() === newId);
                        const isDanru = selectedPerson?.role === 'Danru';
                        const newIndikator = isDanru ? "Pengawasan Personel" : "Disiplin Kerja";
                        setData(prev => ({
                          ...prev,
                          id_anggota: newId,
                          indikator: newIndikator
                        }));
                      }}
                      className="p-2 border rounded-md"
                      required
                    >
                      <option value="">
                        {userRole === "Chief" || userRole === "Admin" ? "-- Pilih Personel --" : "-- Pilih Anggota --"}
                      </option>
                      {anggota.map((person) => (
                        <option key={person.id_user} value={person.id_user}>
                          {person.nama_lengkap} {person.role === 'Danru' ? '(DANRU)' : ''} - {person.regu || "-"}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">Shift</label>
                    <div className="flex items-center gap-4">
                      <label className="flex items-center gap-2 cursor-pointer">
                        <input
                          type="radio"
                          name="shift"
                          value="Pagi"
                          checked={data.shift === "Pagi"}
                          onChange={(e) => setData("shift", e.target.value)}
                          className="w-4 h-4 text-primary"
                        />
                        Pagi
                      </label>
                      <label className="flex items-center gap-2 cursor-pointer">
                        <input
                          type="radio"
                          name="shift"
                          value="Malam"
                          checked={data.shift === "Malam"}
                          onChange={(e) => setData("shift", e.target.value)}
                          className="w-4 h-4 text-primary"
                        />
                        Malam
                      </label>
                    </div>
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">Pos / Lokasi</label>
                    <input
                      type="text"
                      placeholder="Contoh: Pos Utama, Lobby Utama, Gate A"
                      value={data.pos_lokasi}
                      onChange={(e) => setData("pos_lokasi", e.target.value)}
                      className="p-2 border rounded-md text-sm"
                    />
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">Indikator</label>
                    <select
                      value={data.indikator}
                      onChange={(e) => setData("indikator", e.target.value)}
                      className="p-2 border rounded-md"
                      required
                    >
                      {(() => {
                        const selectedPerson = anggota.find(a => a.id_user.toString() === data.id_anggota?.toString());
                        const isSelectedDanru = selectedPerson?.role === 'Danru';
                        const options = isSelectedDanru 
                          ? ["Pengawasan Personel", "Ketepatan Pelaporan", "Penyelesaian Masalah"] 
                          : ["Disiplin Kerja", "Penampilan & Kerapihan", "Kehadiran", "Komunikasi & Pelayanan"];
                        
                        return options.map((cat) => (
                          <option key={cat} value={cat}>{cat}</option>
                        ));
                      })()}
                    </select>
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">Deskripsi Kejadian / Temuan</label>
                    <textarea
                      value={data.deskripsi}
                      onChange={(e) => setData("deskripsi", e.target.value)}
                      className="p-2 border rounded-md min-h-[80px]"
                      required
                    />
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">Arahan yang Diberikan</label>
                    <textarea
                      value={data.arahan}
                      onChange={(e) => setData("arahan", e.target.value)}
                      className="p-2 border rounded-md min-h-[60px]"
                    />
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">Status Tindak Lanjut</label>
                    <div className="flex gap-4">
                      <label className="flex items-center gap-2">
                        <input
                          type="radio"
                          name="status_tindak_lanjut"
                          value="Belum"
                          checked={data.status_tindak_lanjut === "Belum"}
                          onChange={(e) => setData("status_tindak_lanjut", e.target.value as any)}
                        />
                        Belum
                      </label>
                      <label className="flex items-center gap-2">
                        <input
                          type="radio"
                          name="status_tindak_lanjut"
                          value="Sudah"
                          checked={data.status_tindak_lanjut === "Sudah"}
                          onChange={(e) => setData("status_tindak_lanjut", e.target.value as any)}
                        />
                        Sudah
                      </label>
                    </div>
                  </div>

                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold">Keterangan</label>
                    <textarea
                      placeholder="Catatan tambahan / keterangan tindak lanjut (opsional)"
                      value={data.keterangan}
                      onChange={(e) => setData("keterangan", e.target.value)}
                      className="p-2 border rounded-md min-h-[60px] text-sm"
                    />
                  </div>

                  <div className="flex justify-end gap-2 mt-4">
                    <Button type="button" variant="outline" onClick={() => setIsModalOpen(false)}>Batal</Button>
                    <Button type="submit" disabled={processing}>Simpan</Button>
                  </div>
                </form>
                  </DialogContent>
                </Dialog>
              )}
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
                className="p-2 text-sm border rounded-md"
              >
                {listBulan.map(b => <option key={b} value={b}>{b}</option>)}
              </select>
            </div>
            <div className="flex flex-col gap-1">
              <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Minggu Ke</label>
              <select
                value={selectedMinggu}
                onChange={(e) => handleFilterChange(selectedBulan, parseInt(e.target.value), selectedTahun, filterRegu)}
                className="p-2 border rounded-md text-sm"
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
                className="p-2 w-full md:w-24 text-sm border rounded-md"
              />
            </div>

            {(userRole === "Chief" || userRole === "Admin") && reguList && reguList.length > 0 && (
              <div className="flex flex-col gap-1 md:border-l md:pl-4 w-full md:w-auto">
                <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Filter Regu</label>
                <select
                  value={filterRegu || ""}
                  onChange={(e) => handleFilterChange(selectedBulan, selectedMinggu, selectedTahun, e.target.value)}
                  className="p-2 text-sm border rounded-md"
                >
                  <option value="">Semua Regu</option>
                  {reguList.map(r => <option key={r} value={r}>{r}</option>)}
                </select>
              </div>
            )}
          </div>

          <div className="flex flex-col sm:flex-row items-center gap-2 md:border-l md:pl-4 md:ml-auto w-full md:w-auto">
            <Button asChild variant="outline" size="sm" className="w-full sm:w-auto bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200">
              <a href={`/catatan-harian/export-pdf?bulan=${selectedBulan}&tahun=${selectedTahun}&minggu_ke=${selectedMinggu}${filterRegu ? `&filter_regu=${filterRegu}` : ''}`} target="_blank">
                Export PDF
              </a>
            </Button>
          </div>
        </div>

        <Card>
          <CardContent className="pt-6">
            <div className="hidden lg:block overflow-x-auto">
              <table className="w-full text-left text-sm border-collapse">
                <thead>
                  <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                    <th className="p-3">Tanggal</th>
                    <th className="p-3">Shift</th>
                    <th className="p-3">Pos / Lokasi</th>
                    <th className="p-3">Nama Anggota</th>
                    <th className="p-3">Regu</th>
                    <th className="p-3">Indikator</th>
                    <th className="p-3">Deskripsi & Arahan</th>
                    <th className="p-3 text-center">Status</th>
                    <th className="p-3">Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  {catatan.length > 0 ? (
                    catatan.map((c) => (
                      <tr key={c.id_catatan} className="border-b align-top hover:bg-muted/5">
                        <td className="p-3">
                          <div className="text-xs text-muted-foreground font-semibold uppercase">{formatDayName(c.tanggal)}</div>
                          <div className="font-bold text-sm">{formatDateFull(c.tanggal)}</div>
                        </td>
                        <td className="p-3 whitespace-nowrap font-medium text-muted-foreground">{c.shift || "-"}{formatTime(c.jam_kejadian, c.created_at)}</td>
                        <td className="p-3 whitespace-nowrap font-semibold text-foreground">{c.pos_lokasi || "-"}</td>
                        <td className="p-3 font-bold">
                          <div className="flex items-center gap-2">
                            <span>{c.anggota?.nama_lengkap}</span>
                            {c.anggota?.role === 'Danru' && (
                              <span className="bg-destructive/15 text-destructive font-bold text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">Danru</span>
                            )}
                          </div>
                        </td>
                        <td className="p-3 font-semibold whitespace-nowrap">{c.anggota?.regu}</td>
                        <td className="p-3">
                          <div className="font-semibold text-primary">{c.indikator}</div>
                        </td>
                        <td className="p-3">
                          <div className="text-xs text-muted-foreground mb-1"><span className="font-semibold text-foreground">Temuan:</span> {c.deskripsi}</div>
                          {c.arahan && <div className="text-xs text-muted-foreground"><span className="font-semibold text-foreground">Arahan:</span> {c.arahan}</div>}
                        </td>
                        <td className="p-3 text-center">
                          {c.status_tindak_lanjut === "Sudah" ? (
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
                                  onClick={() => handleTindakLanjut(c.id_catatan)}
                                  disabled={processingId === c.id_catatan}
                                >
                                  Tandai Sudah
                                </Button>
                              )}
                            </div>
                          )}
                        </td>
                        <td className="p-3">
                          <div className="text-xs text-muted-foreground">{c.keterangan || "-"}</div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan={9} className="text-center p-8 text-muted-foreground">Belum ada catatan harian di periode ini.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div className="lg:hidden flex flex-col gap-4 p-2">
              {catatan.length > 0 ? (
                catatan.map((c) => (
                  <div key={c.id_catatan} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                    <div className="flex justify-between items-start border-b pb-3">
                      <div>
                        <div className="flex items-center gap-2">
                          <div className="font-bold text-foreground text-sm">{c.anggota?.nama_lengkap}</div>
                          {c.anggota?.role === 'Danru' && (
                            <span className="bg-destructive/15 text-destructive font-bold text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">Danru</span>
                          )}
                        </div>
                        <div className="flex items-center gap-2 mt-1">
                          <span className="bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase">{c.anggota?.regu}</span>
                          {c.pos_lokasi && (
                            <span className="bg-muted text-muted-foreground px-2 py-0.5 rounded text-[10px] font-semibold">{c.pos_lokasi}</span>
                          )}
                        </div>
                      </div>
                      <div className="flex flex-col items-end gap-1">
                        <div className="text-[10px] text-muted-foreground font-semibold uppercase">{formatDayName(c.tanggal)}</div>
                        <div className="font-bold text-xs">{formatDateFull(c.tanggal)}</div>
                      </div>
                    </div>

                    <div className="flex flex-col gap-1 mt-1">
                      <span className="font-semibold text-primary text-sm">{c.indikator}</span>
                      <div className="text-xs text-muted-foreground mt-1 bg-muted/30 p-2 rounded border">
                        <div><span className="font-semibold text-foreground">Temuan:</span> {c.deskripsi}</div>
                        {c.arahan && <div className="mt-1"><span className="font-semibold text-foreground">Arahan:</span> {c.arahan}</div>}
                        {c.keterangan && <div className="mt-1"><span className="font-semibold text-foreground">Ket:</span> {c.keterangan}</div>}
                      </div>
                      
                      <div className="flex justify-end items-center mt-3 pt-3 border-t">
                        <div className="flex items-center gap-2">
                          {c.status_tindak_lanjut === "Sudah" ? (
                            <Badge className="bg-green-500/10 text-green-600 border-green-500/20 shadow-none hover:bg-green-500/10 text-[9px] px-1.5 py-0">Sudah</Badge>
                          ) : (
                            <div className="flex items-center gap-2">
                              <Badge variant="destructive" className="shadow-none text-[9px] px-1.5 py-0">Belum</Badge>
                              {(userRole === "Admin" || userRole === "Chief" || userRole === "Danru") && (
                                <Button 
                                  size="sm" 
                                  variant="outline" 
                                  className="h-5 text-[9px] px-1.5" 
                                  onClick={() => handleTindakLanjut(c.id_catatan)}
                                  disabled={processingId === c.id_catatan}
                                >
                                  Tandai Sudah
                                </Button>
                              )}
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center p-8 text-muted-foreground border rounded-xl">Belum ada catatan harian di periode ini.</div>
              )}
            </div>
          </CardContent>
        </Card>

      </div>
    </>
  );
}

CatatanHarianIndex.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;
