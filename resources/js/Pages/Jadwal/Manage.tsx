import React, { useState, useEffect } from "react";
import { Head, router, useForm } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

interface User {
  id_user: number;
  nama_lengkap: string;
  regu: string | null;
}

interface Jadwal {
  id_jadwal: number;
  id_anggota: number;
  bulan: string;
  tahun: number;
  jadwal_harian: Record<string, "Pagi" | "Malam" | "Libur">;
}

interface Props {
  anggotas: User[];
  jadwals: Record<number, Jadwal>;
  bulan: string;
  tahun: string;
}

export default function Manage({ anggotas, jadwals, bulan, tahun }: Props) {
  const [selectedBulan, setSelectedBulan] = useState(bulan);
  const [selectedTahun, setSelectedTahun] = useState(tahun);

  // Generate days array based on month and year
  const daysInMonth = new Date(parseInt(selectedTahun), parseInt(selectedBulan), 0).getDate();
  const daysArray = Array.from({ length: daysInMonth }, (_, i) => i + 1);

  // Form setup
  const { data, setData, post, processing } = useForm({
    bulan: selectedBulan,
    tahun: selectedTahun,
    jadwal: {} as Record<number, Record<string, "Pagi" | "Malam" | "Libur">>,
  });

  // Initialize data on load
  useEffect(() => {
    const initialJadwal: Record<number, Record<string, "Pagi" | "Malam" | "Libur">> = {};
    anggotas.forEach(anggota => {
      initialJadwal[anggota.id_user] = {};
      daysArray.forEach(day => {
        // Find existing schedule or default to Libur
        const existingJadwal = jadwals[anggota.id_user]?.jadwal_harian;
        initialJadwal[anggota.id_user][day.toString()] = existingJadwal ? existingJadwal[day.toString()] || "Libur" : "Libur";
      });
    });
    setData(data => ({
      ...data,
      bulan: selectedBulan,
      tahun: selectedTahun,
      jadwal: initialJadwal
    }));
  }, [anggotas, jadwals, selectedBulan, selectedTahun]);

  const handleFilter = () => {
    router.get('/jadwal/manage', { bulan: selectedBulan, tahun: selectedTahun }, { preserveState: true });
  };

  const handleShiftChange = (id_anggota: number, day: string, newShift: "Pagi" | "Malam" | "Libur") => {
    setData("jadwal", {
      ...data.jadwal,
      [id_anggota]: {
        ...data.jadwal[id_anggota],
        [day]: newShift
      }
    });
  };

  // Quick toggle feature for faster UI (Click to cycle Pagi -> Malam -> Libur -> Pagi)
  const toggleShift = (current: string) => {
    if (current === "Libur") return "Pagi";
    if (current === "Pagi") return "Malam";
    return "Libur";
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/jadwal/manage', {
      preserveScroll: true,
      onSuccess: () => alert('Jadwal berhasil disimpan!')
    });
  };

  return (
    <>
      <Head title="Manajemen Jadwal Bulanan" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div>
          <h1 className="text-2xl font-bold text-primary tracking-tight">Pengaturan Jadwal Bulanan</h1>
          <p className="text-sm text-muted-foreground">Atur jadwal shift anggota (Pagi / Siang / Libur) dalam satu bulan.</p>
        </div>

        <Card className="shadow-xs border-2 overflow-hidden">
          <CardHeader className="bg-muted/30 border-b flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4">
            <div>
              <CardTitle className="text-md font-bold">Grid Jadwal</CardTitle>
              <CardDescription>Klik pada sel untuk mengubah shift secara cepat, atau gunakan tombol simpan.</CardDescription>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <select 
                value={selectedBulan} 
                onChange={e => setSelectedBulan(e.target.value)}
                className="p-1.5 border rounded text-sm bg-background"
              >
                {Array.from({length: 12}, (_, i) => i + 1).map(m => (
                  <option key={m} value={m.toString().padStart(2, '0')}>
                    {new Date(0, m - 1).toLocaleString('id-ID', { month: 'long' })}
                  </option>
                ))}
              </select>
              <input 
                type="number" 
                value={selectedTahun}
                onChange={e => setSelectedTahun(e.target.value)}
                className="p-1.5 border rounded text-sm w-20 bg-background"
              />
              <Button type="button" onClick={handleFilter} variant="outline" size="sm">Tampilkan</Button>
              <div className="flex items-center gap-1 sm:border-l sm:pl-2 w-full sm:w-auto mt-2 sm:mt-0">
                <Button asChild variant="outline" size="sm" className="w-full sm:w-auto bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border-green-200">
                  <a href={`/export/jadwal-bulanan?type=excel&bulan=${selectedBulan}&tahun=${selectedTahun}`} target="_blank">
                    Export Excel
                  </a>
                </Button>
                <Button asChild variant="outline" size="sm" className="w-full sm:w-auto bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200">
                  <a href={`/export/jadwal-bulanan?type=pdf&bulan=${selectedBulan}&tahun=${selectedTahun}`} target="_blank">
                    Export PDF
                  </a>
                </Button>
              </div>
            </div>
          </CardHeader>
          <CardContent className="p-0 overflow-x-auto">
            <form onSubmit={submit}>
              {/* Desktop View */}
              <div className="hidden lg:block min-w-max pb-4">
                <table className="w-full text-xs text-center border-collapse">
                  <thead>
                    <tr className="bg-muted text-foreground border-b border-muted-foreground/20">
                      <th className="p-2 border-r sticky left-0 bg-muted z-10 w-48 text-left">Nama Anggota</th>
                      {daysArray.map(day => (
                        <th key={day} className="p-2 border-r w-8 min-w-[32px]">{day}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {anggotas.length === 0 ? (
                      <tr>
                        <td colSpan={daysArray.length + 1} className="p-4 text-center text-muted-foreground">
                          Tidak ada anggota ditemukan.
                        </td>
                      </tr>
                    ) : (
                      anggotas.map((anggota, index) => (
                        <tr key={anggota.id_user} className={`border-b hover:bg-muted/10 ${index % 2 === 0 ? 'bg-background' : 'bg-muted/5'}`}>
                          <td className="p-2 font-bold border-r sticky left-0 bg-background text-left truncate max-w-[200px]">
                            {anggota.nama_lengkap}
                          </td>
                          {daysArray.map(day => {
                            const shift = data.jadwal[anggota.id_user]?.[day.toString()] || "Libur";
                            let colorClass = "bg-background text-muted-foreground";
                            if (shift === "Pagi") colorClass = "bg-blue-100 text-blue-700 font-bold border-blue-200";
                            if (shift === "Malam") colorClass = "bg-indigo-100 text-indigo-700 font-bold border-indigo-200";
                            
                            return (
                              <td 
                                key={day} 
                                className="border-r p-0 cursor-pointer hover:opacity-80 transition-opacity"
                                onClick={() => handleShiftChange(anggota.id_user, day.toString(), toggleShift(shift) as any)}
                              >
                                <div className={`w-full h-full p-2 flex items-center justify-center ${colorClass}`}>
                                  {shift === "Libur" ? "-" : shift.charAt(0)}
                                </div>
                              </td>
                            );
                          })}
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>

              {/* Mobile View */}
              <div className="lg:hidden flex flex-col gap-4 p-4">
                {anggotas.length === 0 ? (
                  <div className="p-8 text-center text-muted-foreground border rounded-xl border-dashed">
                    Tidak ada anggota ditemukan.
                  </div>
                ) : (
                  anggotas.map((anggota) => (
                    <div key={anggota.id_user} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                      <div className="font-bold text-foreground border-b pb-2">
                        {anggota.nama_lengkap}
                      </div>
                      <div className="grid grid-cols-7 gap-1">
                        {daysArray.map(day => {
                          const shift = data.jadwal[anggota.id_user]?.[day.toString()] || "Libur";
                          let colorClass = "bg-muted/30 text-muted-foreground border";
                          if (shift === "Pagi") colorClass = "bg-blue-100 text-blue-700 font-bold border-blue-200 border";
                          if (shift === "Malam") colorClass = "bg-indigo-100 text-indigo-700 font-bold border-indigo-200 border";
                          
                          return (
                            <div 
                              key={day}
                              onClick={() => handleShiftChange(anggota.id_user, day.toString(), toggleShift(shift) as any)}
                              className={`flex flex-col items-center justify-center p-1 rounded cursor-pointer hover:opacity-80 transition-opacity ${colorClass}`}
                            >
                              <span className="text-[10px] opacity-70 mb-0.5">{day}</span>
                              <span className="text-xs">{shift === "Libur" ? "-" : shift.charAt(0)}</span>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  ))
                )}
              </div>
              <div className="p-4 bg-muted/20 border-t flex justify-end gap-2 sticky left-0">
                <Button type="button" variant="outline" onClick={() => router.reload()}>Batal</Button>
                <Button type="submit" disabled={processing || anggotas.length === 0}>
                  {processing ? "Menyimpan..." : "Simpan Jadwal"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

Manage.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;