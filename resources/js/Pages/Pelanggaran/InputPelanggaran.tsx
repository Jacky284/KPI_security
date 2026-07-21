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

interface JadwalBulanan {
  id_jadwal: number;
  id_anggota: number;
  bulan: string;
  tahun: number;
  jadwal_harian: Record<string, string>;
}

interface Props {
  anggota: Anggota[];
  jadwals: JadwalBulanan[];
}

export default function InputPelanggaran({ anggota, jadwals }: Props) {
  const urlParams = typeof window !== "undefined" ? new URLSearchParams(window.location.search) : null;
  const initialAnggotaId = urlParams ? urlParams.get("id_anggota") || "" : "";

  const today = new Date();
  const year = today.getFullYear();
  const month = today.getMonth();
  const day = today.getDate();
  const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
  const bulanStr = months[month];
  
  const firstDay = new Date(year, month, 1);
  const firstDayOfWeek = (firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1);
  const mingguKe = Math.ceil((day + firstDayOfWeek) / 7);

  const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
    id_anggota: initialAnggotaId,
    tanggal_kejadian: today.toISOString().split("T")[0],
    minggu_ke: mingguKe,
    bulan: bulanStr,
    tahun: year,
    kategori_indikator: "Kedisiplinan",
    tingkat_pelanggaran: "Ringan",
    deskripsi_kejadian: "",
  });

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

  const availableWeeks = getAvailableWeeks(data.bulan, data.tahun);

  const handleDateChange = (dateStr: string) => {
    if (!dateStr) {
      setData("tanggal_kejadian", dateStr);
      return;
    }
    
    const [y, m, d] = dateStr.split('-');
    const year = parseInt(y);
    const month = parseInt(m) - 1;
    const day = parseInt(d);

    const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const bulanStr = months[month];
    
    const firstDay = new Date(year, month, 1);
    const firstDayOfWeek = (firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1);
    const mingguKe = Math.ceil((day + firstDayOfWeek) / 7);

    setData({
      ...data,
      tanggal_kejadian: dateStr,
      tahun: year,
      bulan: bulanStr,
      minggu_ke: mingguKe
    });
  };

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

  // Calculate selected Regu
  const selectedUser = anggota.find(a => a.id_user.toString() === data.id_anggota.toString());
  const reguName = selectedUser?.regu || "-";

  // Calculate Shift based on selected user and date
  let shiftName = "-";
  if (data.id_anggota && data.tanggal_kejadian) {
    const dateObj = new Date(data.tanggal_kejadian);
    const day = dateObj.getDate().toString();
    const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
    const year = dateObj.getFullYear();

    const userJadwal = jadwals.find(j =>
      j.id_anggota.toString() === data.id_anggota.toString() &&
      j.bulan === month &&
      j.tahun === year
    );

    if (userJadwal && userJadwal.jadwal_harian) {
      shiftName = userJadwal.jadwal_harian[day] || "Libur";
    } else {
      shiftName = "Tidak Ada Jadwal (Libur)";
    }
  }

  return (
    <>
      <Head title="Input Pelanggaran" />
      <div className="flex flex-col gap-6 max-w-2xl mx-auto">
        <div>
          <h1 className="text-2xl font-bold text-primary tracking-tight">Input Catatan Pelanggaran Sekuriti</h1>
          <p className="text-sm text-muted-foreground">Gunakan formulir ini untuk mencatat pelanggaran kinerja anggota sekuriti demi perhitungan KPI bulanan.</p>
        </div>
        <Card className="shadow-md">
          <CardContent className="pt-6">
            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
              {/* Anggota Dropdown */}
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-semibold text-foreground">Pilih Personel (Anggota / Danru)</label>
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

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {/* Tanggal Kejadian */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Tanggal Kejadian</label>
                  <input
                    type="date"
                    value={data.tanggal_kejadian}
                    onChange={(e) => handleDateChange(e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  />
                  {errors.tanggal_kejadian && <span className="text-xs text-destructive">{errors.tanggal_kejadian}</span>}
                  <span className="text-xs text-muted-foreground/80 italic font-medium ml-1">
                    Periode: Minggu Ke-{data.minggu_ke}, {data.bulan} {data.tahun}
                  </span>
                </div>

                {/* Regu & Shift Jaga */}
                <div className="grid grid-cols-2 gap-4">
                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold text-foreground">Regu</label>
                    <input
                      type="text"
                      value={reguName}
                      className="w-full p-2 border rounded-md bg-muted text-muted-foreground font-semibold cursor-not-allowed"
                      disabled
                    />
                  </div>
                  <div className="flex flex-col gap-1.5">
                    <label className="text-sm font-semibold text-foreground">Shift Jaga</label>
                    <input
                      type="text"
                      value={shiftName}
                      className="w-full p-2 border rounded-md bg-muted text-muted-foreground font-semibold cursor-not-allowed"
                      disabled
                    />
                  </div>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
