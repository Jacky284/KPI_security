import React from "react";
import { useForm, Head } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

interface Anggota {
  id_user: number;
  nama_lengkap: string;
  regu: string;
}

interface Props {
  anggota: Anggota[];
}

export default function InputPelanggaran({ anggota }: Props) {
  const urlParams = typeof window !== "undefined" ? new URLSearchParams(window.location.search) : null;
  const initialAnggotaId = urlParams ? urlParams.get("id_anggota") || "" : "";

  const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
    id_anggota: initialAnggotaId,
    tanggal_kejadian: new Date().toISOString().split("T")[0],
    minggu_ke: 1,
    bulan: "Juli",
    tahun: new Date().getFullYear(),
    kategori_indikator: "Kedisiplinan",
    tingkat_pelanggaran: "Ringan",
    deskripsi_kejadian: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/pelanggaran", {
      onSuccess: () => {
        reset("deskripsi_kejadian", "id_anggota");
        alert("Catatan pelanggaran berhasil disimpan!");
      },
    });
  };

  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  return (
    <>
      <Head title="Input Pelanggaran" />
      <div className="max-w-2xl mx-auto py-6">
        <Card className="shadow-md">
          <CardHeader>
            <CardTitle className="text-xl font-bold text-primary">Input Catatan Pelanggaran Sekuriti</CardTitle>
            <CardDescription>
              Gunakan formulir ini untuk mencatat pelanggaran kinerja anggota sekuriti demi perhitungan KPI bulanan.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
              {/* Anggota Dropdown */}
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-semibold text-foreground">Pilih Anggota Sekuriti</label>
                <select
                  value={data.id_anggota}
                  onChange={(e) => setData("id_anggota", e.target.value)}
                  className="w-full p-2.5 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                  required
                >
                  <option value="">-- Pilih Anggota --</option>
                  {anggota.map((person) => (
                    <option key={person.id_user} value={person.id_user}>
                      {person.nama_lengkap} ({person.regu || "Tanpa Regu"})
                    </option>
                  ))}
                </select>
                {errors.id_anggota && <span className="text-xs text-destructive">{errors.id_anggota}</span>}
              </div>

              {/* Tanggal Kejadian */}
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-semibold text-foreground">Tanggal Kejadian</label>
                <input
                  type="date"
                  value={data.tanggal_kejadian}
                  onChange={(e) => setData("tanggal_kejadian", e.target.value)}
                  className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                  required
                />
                {errors.tanggal_kejadian && <span className="text-xs text-destructive">{errors.tanggal_kejadian}</span>}
              </div>

              <div className="grid grid-cols-3 gap-4">
                {/* Minggu Ke */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Minggu Ke</label>
                  <select
                    value={data.minggu_ke}
                    onChange={(e) => setData("minggu_ke", parseInt(e.target.value))}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  >
                    {[1, 2, 3, 4, 5].map((m) => (
                      <option key={m} value={m}>Minggu {m}</option>
                    ))}
                  </select>
                  {errors.minggu_ke && <span className="text-xs text-destructive">{errors.minggu_ke}</span>}
                </div>

                {/* Bulan */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Bulan</label>
                  <select
                    value={data.bulan}
                    onChange={(e) => setData("bulan", e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  >
                    {listBulan.map((b) => (
                      <option key={b} value={b}>{b}</option>
                    ))}
                  </select>
                  {errors.bulan && <span className="text-xs text-destructive">{errors.bulan}</span>}
                </div>

                {/* Tahun */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Tahun</label>
                  <input
                    type="number"
                    value={data.tahun}
                    onChange={(e) => setData("tahun", parseInt(e.target.value))}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  />
                  {errors.tahun && <span className="text-xs text-destructive">{errors.tahun}</span>}
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                {/* Kategori Indikator */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Kategori Indikator</label>
                  <select
                    value={data.kategori_indikator}
                    onChange={(e) => setData("kategori_indikator", e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  >
                    {["Kedisiplinan", "Kehadiran", "Kerapihan", "Komunikasi"].map((cat) => (
                      <option key={cat} value={cat}>{cat}</option>
                    ))}
                  </select>
                  {errors.kategori_indikator && <span className="text-xs text-destructive">{errors.kategori_indikator}</span>}
                </div>

                {/* Tingkat Pelanggaran */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Tingkat Pelanggaran</label>
                  <select
                    value={data.tingkat_pelanggaran}
                    onChange={(e) => setData("tingkat_pelanggaran", e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  >
                    {["Ringan", "Sedang", "Berat"].map((lvl) => (
                      <option key={lvl} value={lvl}>{lvl}</option>
                    ))}
                  </select>
                  {errors.tingkat_pelanggaran && <span className="text-xs text-destructive">{errors.tingkat_pelanggaran}</span>}
                </div>
              </div>

              {/* Deskripsi Kejadian */}
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-semibold text-foreground">Deskripsi Kejadian</label>
                <textarea
                  value={data.deskripsi_kejadian}
                  onChange={(e) => setData("deskripsi_kejadian", e.target.value)}
                  rows={4}
                  placeholder="Ceritakan kejadian pelanggaran secara rinci..."
                  className="w-full p-2.5 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
                  required
                />
                {errors.deskripsi_kejadian && <span className="text-xs text-destructive">{errors.deskripsi_kejadian}</span>}
              </div>

              {/* Submit Button */}
              <div className="flex justify-end gap-2 mt-4">
                <Button type="submit" disabled={processing} className="w-full md:w-auto px-6">
                  {processing ? "Menyimpan..." : "Simpan Pelanggaran"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

InputPelanggaran.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
