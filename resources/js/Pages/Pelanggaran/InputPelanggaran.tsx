import React from "react";
import { useForm, Head } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

interface Anggota {
  id_user: number;
  nama_lengkap: string;
  regu: string;
  role?: string;
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
  userRole?: string;
}

export default function InputPelanggaran({ anggota, jadwals, userRole }: Props) {
  const urlParams = typeof window !== "undefined" ? new URLSearchParams(window.location.search) : null;
  const initialAnggotaId = urlParams ? urlParams.get("id_anggota") || "" : "";



  const calculateWeekNumber = (dateStr: string) => {
    const [y, m, d] = dateStr.split('-').map(Number);
    const year = y;
    const monthIndex = m - 1;
    const day = d;
    const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const bulanStr = months[monthIndex];

    const firstOfMonth = new Date(year, monthIndex, 1);
    const dayOfWeek = firstOfMonth.getDay(); // 0 = Sun, 1 = Mon, ..., 6 = Sat

    let startOfWeek1 = new Date(firstOfMonth);

    if (dayOfWeek === 6) {
      startOfWeek1 = new Date(firstOfMonth);
    } else if (dayOfWeek === 0 || dayOfWeek === 1 || dayOfWeek === 2) {
      const daysToSubtract = (dayOfWeek + 1);
      startOfWeek1.setDate(startOfWeek1.getDate() - daysToSubtract);
    } else {
      const daysToAdd = 6 - dayOfWeek;
      startOfWeek1.setDate(startOfWeek1.getDate() + daysToAdd);
    }

    startOfWeek1.setHours(0, 0, 0, 0);
    const targetDate = new Date(year, monthIndex, day);
    targetDate.setHours(0, 0, 0, 0);

    let weekNum = 1;
    if (targetDate >= startOfWeek1) {
      const diffTime = targetDate.getTime() - startOfWeek1.getTime();
      const diffDays = Math.floor(diffTime / (1000 * 3600 * 24));
      weekNum = Math.floor(diffDays / 7) + 1;
    }

    if (weekNum > 4) weekNum = 4;
    if (weekNum < 1) weekNum = 1;

    return {
      tahun: year,
      bulan: bulanStr,
      minggu_ke: weekNum
    };
  };

  const today = new Date();
  const todayStr = today.toISOString().split("T")[0];
  const initialWeekDetails = calculateWeekNumber(todayStr);

  const initialPerson = anggota.find(a => a.id_user.toString() === initialAnggotaId.toString());
  const initialIsDanru = initialPerson?.role === 'Danru' || userRole === "Chief";

  const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
    id_anggota: initialAnggotaId,
    tanggal_penilaian: todayStr,
    minggu_ke: initialWeekDetails.minggu_ke,
    bulan: initialWeekDetails.bulan,
    tahun: initialWeekDetails.tahun,
    kategori_indikator: initialIsDanru ? "Pengawasan Personel" : "Disiplin Kerja",
    tingkat_penilaian: initialIsDanru ? "Skor 4" : "Ringan 1 kali",
    deskripsi_penilaian: "",
  });

  // Selected user & role check
  const selectedUser = anggota.find(a => a.id_user.toString() === data.id_anggota.toString());
  const isDanruSelected = selectedUser?.role === 'Danru';

  const categories = isDanruSelected
    ? ["Pengawasan Personel", "Ketepatan Pelaporan", "Penyelesaian Masalah"]
    : [
        "Disiplin Kerja",
        "Penampilan & Kerapihan",
        "Kehadiran",
        "Komunikasi & Pelayanan"
      ];

  const tingkatOptions: Record<string, string[]> = isDanruSelected
    ? {
        "Pengawasan Personel": ["Skor 4", "Skor 3", "Skor 2", "Skor 1"],
        "Ketepatan Pelaporan": ["Skor 4", "Skor 3", "Skor 2", "Skor 1"],
        "Penyelesaian Masalah": ["Skor 4", "Skor 3", "Skor 2", "Skor 1"],
      }
    : {
        "Disiplin Kerja": ["Ringan 1 kali", "Ringan 2 kali", "Sedang", "Berat"],
        "Penampilan & Kerapihan": ["Kurang rapi 1 kali", "Kurang rapi 2 kali", "Seragam tidak lengkap", "Penampilan tidak sesuai Standar"],
        "Kehadiran": ["Terlambat 1 kali", "Terlambat 2 kali", "Tidak hadir dengan izin", "Mangkir / Alpha"],
        "Komunikasi & Pelayanan": ["Komplain ringan", "Komplain sedang", "Sering mendapat teguran", "Komplain berat"],
      };

  const handleAnggotaChange = (id: string) => {
    const person = anggota.find(a => a.id_user.toString() === id.toString());
    const isDanru = person?.role === 'Danru';
    const defaultCat = isDanru ? "Pengawasan Personel" : "Disiplin Kerja";
    const defaultTingkat = isDanru ? "Skor 4" : "Ringan 1 kali";

    setData({
      ...data,
      id_anggota: id,
      kategori_indikator: defaultCat,
      tingkat_penilaian: defaultTingkat,
    });
  };

  const getAvailableWeeks = (bulanStr: string, tahun: number) => {
    return [1, 2, 3, 4];
  };

  const availableWeeks = getAvailableWeeks(data.bulan, data.tahun);

  const handleDateChange = (dateStr: string) => {
    if (!dateStr) {
      setData("tanggal_penilaian", dateStr);
      return;
    }

    const weekDetails = calculateWeekNumber(dateStr);

    setData({
      ...data,
      tanggal_penilaian: dateStr,
      tahun: weekDetails.tahun,
      bulan: weekDetails.bulan,
      minggu_ke: weekDetails.minggu_ke
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/pelanggaran", {
      onSuccess: () => {
        reset("deskripsi_penilaian", "id_anggota");
        alert("Catatan pelanggaran berhasil disimpan!");
      },
    });
  };

  const listBulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  const reguName = selectedUser?.regu || "-";

  // Calculate Shift based on selected user and date
  let shiftName = "-";
  if (data.id_anggota && data.tanggal_penilaian) {
    const dateObj = new Date(data.tanggal_penilaian);
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
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div>
          <h1 className="text-2xl font-bold text-primary tracking-tight">Input Catatan Pelanggaran Security</h1>
          <p className="text-sm text-muted-foreground mt-1">Gunakan formulir ini untuk mencatat pelaporan indisipliner / pelanggaran anggota sekuriti demi perhitungan KPI bulanan.</p>
        </div>
        <Card className="shadow-md">
          <CardContent className="pt-0">
            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
              {/* Anggota Dropdown */}
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-semibold text-foreground">
                  {userRole === "Chief" ? "Pilih Danru" : userRole === "Admin" ? "Pilih Personel (Danru / Anggota)" : "Pilih Anggota"}
                </label>
                <select
                  value={data.id_anggota}
                  onChange={(e) => handleAnggotaChange(e.target.value)}
                  className="w-full p-2.5 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                  required
                >
                  <option value="">
                    {userRole === "Chief" ? "-- Pilih Danru --" : userRole === "Admin" ? "-- Pilih Personel --" : "-- Pilih Anggota --"}
                  </option>
                  {anggota.map((person) => (
                    <option key={person.id_user} value={person.id_user}>
                      {person.nama_lengkap} {person.role === 'Danru' ? '(DANRU)' : ''} - {person.regu || "-"}
                    </option>
                  ))}
                </select>
                {errors.id_anggota && <span className="text-xs text-destructive">{errors.id_anggota}</span>}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {/* Tanggal Penilaian */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Tanggal Penilaian</label>
                  <input
                    type="date"
                    value={data.tanggal_penilaian}
                    onChange={(e) => handleDateChange(e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  />
                  {errors.tanggal_penilaian && <span className="text-xs text-destructive">{errors.tanggal_penilaian}</span>}
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
                    onChange={(e) => {
                      const newCat = e.target.value;
                      setData({
                        ...data,
                        kategori_indikator: newCat,
                        tingkat_penilaian: tingkatOptions[newCat][0]
                      });
                    }}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  >
                    {categories.map((cat) => (
                      <option key={cat} value={cat}>{cat}</option>
                    ))}
                  </select>
                  {errors.kategori_indikator && <span className="text-xs text-destructive">{errors.kategori_indikator}</span>}
                </div>

                {/* Tingkat Penilaian */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-sm font-semibold text-foreground">Tingkat Penilaian</label>
                  <select
                    value={data.tingkat_penilaian}
                    onChange={(e) => setData("tingkat_penilaian", e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50"
                    required
                  >
                    {tingkatOptions[data.kategori_indikator]?.map((lvl) => (
                      <option key={lvl} value={lvl}>{lvl}</option>
                    ))}
                  </select>
                  {errors.tingkat_penilaian && <span className="text-xs text-destructive">{errors.tingkat_penilaian}</span>}
                </div>
              </div>

              {/* Deskripsi Penilaian */}
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-semibold text-foreground">Deskripsi Penilaian</label>
                <textarea
                  value={data.deskripsi_penilaian}
                  onChange={(e) => setData("deskripsi_penilaian", e.target.value)}
                  rows={4}
                  placeholder="Ceritakan kejadian pelanggaran secara rinci..."
                  className="w-full p-2.5 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
                  required
                />
                {errors.deskripsi_penilaian && <span className="text-xs text-destructive">{errors.deskripsi_penilaian}</span>}
              </div>

              {/* Submit Button */}
              {shiftName === 'Libur' && (
                <div className="mt-4 p-3 bg-red-100 text-red-700 text-sm font-semibold rounded-md border border-red-200">
                  Tidak dapat menginput penilaian karena Anggota/Danru sedang Libur pada tanggal tersebut.
                </div>
              )}

              <div className="flex justify-end gap-2 mt-4">
                <Button
                  type="submit"
                  disabled={processing || shiftName === 'Libur'}
                  className={`w-full md:w-auto px-6 ${shiftName === 'Libur' ? 'opacity-50 cursor-not-allowed' : ''}`}
                >
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
