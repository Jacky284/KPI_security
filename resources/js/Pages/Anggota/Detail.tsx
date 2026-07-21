import React from "react";
import { Head, Link } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip as RechartsTooltip, Legend, ResponsiveContainer } from "recharts";
import { MapPin, Calendar, Clock, Briefcase, FileWarning, ArrowLeft, CalendarDays } from "lucide-react";
import { Button } from "@/components/ui/button";

interface CatatanPelanggaran {
  id_catatan: number;
  tanggal_kejadian: string;
  tingkat_pelanggaran: string;
  deskripsi_kejadian: string;
  danru_penilai: { nama_lengkap: string } | null;
}

interface JadwalBulanan {
  jadwal_harian: Record<string, string>;
  bulan: string;
  tahun: number;
}

interface TrendData {
  name: string;
  Siang: number;
  Malam: number;
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
}

export default function Detail({ anggota, riwayatPelanggaran, jadwalBulanIni, trendData }: Props) {
  
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
              <CardHeader>
                <CardTitle>Tren Kinerja (3 Bulan Terakhir)</CardTitle>
                <CardDescription>Perbandingan skor kedisiplinan antara Shift Siang dan Shift Malam. Skor dasar 100.</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="h-[300px] w-full">
                  <ResponsiveContainer width="100%" height="100%">
                    <LineChart data={trendData} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                      <CartesianGrid strokeDasharray="3 3" vertical={false} opacity={0.3} />
                      <XAxis dataKey="name" tickLine={false} axisLine={false} tickMargin={10} />
                      <YAxis domain={[0, 100]} tickLine={false} axisLine={false} tickMargin={10} />
                      <RechartsTooltip 
                        contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }}
                      />
                      <Legend verticalAlign="top" height={36}/>
                      <Line 
                        type="monotone" 
                        name="Shift Pagi/Siang"
                        dataKey="Siang" 
                        stroke="#f59e0b" // Amber/Yellow for Day
                        strokeWidth={3}
                        dot={{ r: 4, strokeWidth: 2 }}
                        activeDot={{ r: 6 }}
                      />
                      <Line 
                        type="monotone"
                        name="Shift Malam" 
                        dataKey="Malam" 
                        stroke="#6366f1" // Indigo/Blue for Night
                        strokeWidth={3}
                        dot={{ r: 4, strokeWidth: 2 }}
                        activeDot={{ r: 6 }}
                      />
                    </LineChart>
                  </ResponsiveContainer>
                </div>
              </CardContent>
            </Card>

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
                            <span className="font-semibold text-sm">{formatDate(p.tanggal_kejadian)}</span>
                            <Badge variant={p.tingkat_pelanggaran === 'Berat' ? 'destructive' : p.tingkat_pelanggaran === 'Sedang' ? 'default' : 'secondary'} className="text-[10px]">
                              {p.tingkat_pelanggaran}
                            </Badge>
                          </div>
                          <p className="text-sm text-muted-foreground">{p.deskripsi_kejadian}</p>
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
