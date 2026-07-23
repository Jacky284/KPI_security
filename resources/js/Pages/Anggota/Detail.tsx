import React from "react";
import { Head, Link, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip as RechartsTooltip, Legend, ResponsiveContainer } from "recharts";
import { MapPin, Calendar, Clock, Briefcase, FileWarning, ArrowLeft, CalendarDays } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

interface CatatanPelanggaran {
  id_catatan: number;
  tanggal_penilaian: string;
  tingkat_penilaian: string;
  deskripsi_penilaian: string;
  danru_penilai: { nama_lengkap: string } | null;
}

interface JadwalBulanan {
  jadwal_harian: Record<string, string>;
  bulan: string;
  tahun: number;
}

interface TrendData {
  name: string;
  Kedisiplinan?: number;
  Kehadiran?: number;
  Kerapihan?: number;
  Komunikasi?: number;
  [key: string]: any;
}

interface User {
  id_user: number;
  nama_lengkap: string;
  regu: string | null;
  tempat_lahir: string | null;
  tanggal_lahir: string | null;
  foto_profil: string | null;
  sisa_cuti: number;
}

interface Props {
  anggota: User;
  riwayatPelanggaran: CatatanPelanggaran[];
  jadwalBulanIni: JadwalBulanan | null;
  trendData: TrendData[];
  indicatorTrendData?: TrendData[];
  filters?: {
    awal_bulan: string;
    tahun: number;
  };
}

export default function Detail({ anggota, riwayatPelanggaran, jadwalBulanIni, trendData, indicatorTrendData, filters }: Props) {


  // Format Tanggal Lahir
  const formatDate = (dateStr: string | null) => {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  };

  // Cuti Color
  const getCutiColor = (sisa: number) => {
    if (sisa > 6) return "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400";
    if (sisa > 2) return "bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400";
    return "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400";
  };

  return (
    <>
      <Head title={`Profil - ${anggota.nama_lengkap}`} />

      <div className="flex flex-col gap-6 max-w-7xl mx-auto">

        {/* Header Navigation */}
        <div className="flex items-center gap-4">
          <Link href="/anggota">
            <Button variant="outline" size="icon" className="rounded-full">
              <ArrowLeft className="h-4 w-4" />
            </Button>
          </Link>
          <div>
            <h1 className="text-2xl font-bold text-primary tracking-tight">Profil & Kinerja Anggota</h1>
            <p className="text-sm text-muted-foreground">Analisis performa, biodata, dan riwayat kedisiplinan.</p>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

          {/* Left Column: Biodata */}
          <div className="flex flex-col gap-6">
            <Card className="shadow-sm border-2 overflow-hidden">
              <div className="h-24 bg-primary/10 w-full"></div>
              <CardContent className="pt-0 relative flex flex-col items-center text-center pb-6">
                <Avatar className="w-24 h-24 border-4 border-background -mt-12 mb-4 bg-background">
                  <AvatarImage src={anggota.foto_profil || undefined} />
                  <AvatarFallback className="text-2xl bg-primary/20 text-primary font-bold">
                    {anggota.nama_lengkap.split(" ").map(n => n[0]).join("").substring(0, 2).toUpperCase()}
                  </AvatarFallback>
                </Avatar>

                <h2 className="text-xl font-bold">{anggota.nama_lengkap}</h2>
                <Badge variant="secondary" className="mt-1 font-semibold">{anggota.regu || "Tanpa Regu"}</Badge>

                <div className="w-full mt-6 space-y-3 text-left">
                  <div className="flex items-center gap-3 text-sm">
                    <MapPin className="h-4 w-4 text-muted-foreground" />
                    <span><strong className="font-semibold text-foreground">Tempat Lahir:</strong> {anggota.tempat_lahir || "-"}</span>
                  </div>
                  <div className="flex items-center gap-3 text-sm">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    <span><strong className="font-semibold text-foreground">Tanggal Lahir:</strong> {formatDate(anggota.tanggal_lahir)}</span>
                  </div>
                  <div className="flex items-center gap-3 text-sm">
                    <Briefcase className="h-4 w-4 text-muted-foreground" />
                    <span><strong className="font-semibold text-foreground">Jatah Cuti:</strong>
                      <Badge className={`ml-2 hover:bg-transparent ${getCutiColor(anggota.sisa_cuti)}`} variant="outline">
                        {anggota.sisa_cuti} Hari Tersisa
                      </Badge>
                    </span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Link href={`/pelanggaran?id_anggota=${anggota.id_user}`}>
              <Button className="w-full" size="lg">
                <FileWarning className="w-4 h-4 mr-2" />
                Catat Pelanggaran
              </Button>
            </Link>
          </div>

          {/* Right Column: Graphs & Data */}
          <div className="lg:col-span-2 flex flex-col gap-6">

            {/* Chart */}
            <Card className="shadow-sm">
              <CardHeader className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                  <CardTitle>Tren Kinerja (3 Bulan Terakhir)</CardTitle>
                  <CardDescription>Perbandingan skor per indikator kinerja. Skor dasar 100.</CardDescription>
                </div>
                <div className="flex items-center gap-2">
                  <Select value={filters?.awal_bulan || "Januari"} onValueChange={(val) => {
                    router.get(`/anggota/${anggota.id_user}`, { awal_bulan: val, tahun: filters?.tahun || new Date().getFullYear() }, { preserveScroll: true });
                  }}>
                    <SelectTrigger className="w-[140px]">
                      <SelectValue placeholder="Mulai Bulan" />
                    </SelectTrigger>
                    <SelectContent>
                      {["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"].map(m => (
                        <SelectItem key={m} value={m}>{m}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </CardHeader>
              <CardContent>
                <div className="h-[300px] w-full mt-2">
                  <ResponsiveContainer width="100%" height="100%">
                    <LineChart data={trendData} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                      <CartesianGrid strokeDasharray="3 3" vertical={false} opacity={0.3} />
                      <XAxis dataKey="name" tickLine={false} axisLine={false} tickMargin={10} />
                      <YAxis domain={[0, 100]} tickLine={false} axisLine={false} tickMargin={10} />
                      <RechartsTooltip
                        contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }}
                      />
                      <Legend wrapperStyle={{ paddingTop: '10px' }} />
                      <Line type="monotone" dataKey="Kedisiplinan" name="Kedisiplinan" stroke="#0ea5e9" strokeWidth={3} dot={{ r: 4 }} activeDot={{ r: 6 }} />
                      <Line type="monotone" dataKey="Kehadiran" name="Kehadiran" stroke="#10b981" strokeWidth={3} dot={{ r: 4 }} activeDot={{ r: 6 }} />
                      <Line type="monotone" dataKey="Kerapihan" name="Kerapihan" stroke="#f59e0b" strokeWidth={3} dot={{ r: 4 }} activeDot={{ r: 6 }} />
                      <Line type="monotone" dataKey="Komunikasi" name="Komunikasi" stroke="#8b5cf6" strokeWidth={3} dot={{ r: 4 }} activeDot={{ r: 6 }} />
                    </LineChart>
                  </ResponsiveContainer>
                </div>
              </CardContent>
            </Card>

            {/* Indicator Breakdown Chart */}
            {indicatorTrendData && (
              <Card className="shadow-sm">
                <CardHeader>
                  <CardTitle>Rincian Skor per Indikator (3 Bulan Terakhir)</CardTitle>
                  <CardDescription>Analisis indikator mana yang paling sering dilanggar pada masing-masing shift.</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="h-[300px] w-full">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={indicatorTrendData} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} opacity={0.3} />
                        <XAxis dataKey="name" tickLine={false} axisLine={false} tickMargin={10} tick={{ fontSize: 12 }} />
                        <YAxis domain={[0, 100]} tickLine={false} axisLine={false} tickMargin={10} tick={{ fontSize: 12 }} />
                        <RechartsTooltip
                          cursor={{ fill: 'rgba(0,0,0,0.05)' }}
                          contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }}
                        />
                        <Legend
                          verticalAlign="top"
                          content={() => (
                            <div className="flex justify-center items-center gap-6 text-xs pb-4">
                              <div className="flex items-center gap-1.5">
                                <span className="w-3 h-3 bg-[#0ea5e9] rounded-xs inline-block"></span>
                                <span className="font-medium text-foreground">Shift Pagi</span>
                              </div>
                              <div className="flex items-center gap-1.5">
                                <span className="w-3 h-3 bg-[#8b5cf6] rounded-xs inline-block"></span>
                                <span className="font-medium text-foreground">Shift Malam</span>
                              </div>
                            </div>
                          )}
                        />
                        <Bar dataKey="Pagi" name="Shift Pagi" fill="#0ea5e9" radius={[4, 4, 0, 0]} />
                        <Bar dataKey="Malam" name="Shift Malam" fill="#8b5cf6" radius={[4, 4, 0, 0]} />
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Violation History */}
            <Card className="shadow-sm">
              <CardHeader>
                <CardTitle>Riwayat Pelanggaran</CardTitle>
                <CardDescription>Daftar indisipliner yang tercatat pada sistem.</CardDescription>
              </CardHeader>
              <CardContent>
                {riwayatPelanggaran.length > 0 ? (
                  <div className="space-y-4">
                    {riwayatPelanggaran.map(p => (
                      <div key={p.id_catatan} className="flex justify-between items-start border-b pb-4 last:border-0 last:pb-0">
                        <div className="flex flex-col gap-1">
                          <div className="flex items-center gap-2">
                            <span className="font-semibold text-sm">{formatDate(p.tanggal_penilaian)}</span>
                            <Badge variant={p.tingkat_penilaian === 'Berat' ? 'destructive' : p.tingkat_penilaian === 'Sedang' ? 'default' : 'secondary'} className="text-[10px]">
                              {p.tingkat_penilaian}
                            </Badge>
                          </div>
                          <p className="text-sm text-muted-foreground">{p.deskripsi_penilaian}</p>
                          <p className="text-xs text-muted-foreground/60 italic">Dinilai oleh: {p.danru_penilai?.nama_lengkap || "Sistem"}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8 text-muted-foreground flex flex-col items-center gap-2">
                    <div className="h-12 w-12 rounded-full bg-green-100/50 flex items-center justify-center text-green-600 mb-2">
                      <Clock className="w-6 h-6" />
                    </div>
                    <p className="font-semibold">Bersih tanpa catatan</p>
                    <p className="text-sm">Anggota ini tidak memiliki catatan pelanggaran.</p>
                  </div>
                )}
              </CardContent>
            </Card>

          </div>
        </div>
      </div>
    </>
  );
}

Detail.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
