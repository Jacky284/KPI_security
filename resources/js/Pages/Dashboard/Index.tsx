import React from "react";
import { Link, Head, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Users, AlertTriangle, ChevronLeft, ChevronRight, Calendar as CalendarIcon, CheckCircle2, XCircle, Minus, Activity } from "lucide-react";
import { getStatusBadge } from "@/lib/utils";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip as RechartsTooltip, Legend, ResponsiveContainer } from "recharts";

interface Anggota {
  id_user: number;
  nama_lengkap: string;
  regu: string | null;
  username: string;
}

interface DanruDashboardData {
  id_user: number;
  nama_lengkap: string;
  regu: string | null;
  mingguan_status: Record<string, string | null>;
}

interface CurrentUser {
  nama_lengkap: string;
  role: "Admin" | "Danru" | "Chief" | "Klien" | "Anggota";
  regu: string | null;
}

interface WeekDate {
  date: string;
  day_name: string;
  day: string;
  month: string;
  year: string;
}

interface TrendData {
  name: string;
  [key: string]: string | number;
}

interface Props {
  anggota?: Anggota[];
  danrus?: DanruDashboardData[];
  currentUser: CurrentUser;
  weekDates?: WeekDate[];
  jadwalMingguan?: Record<number, Record<string, string>>;
  currentStartDate?: string;
  currentBulan?: string;
  currentTahun?: number;
  trendSiang?: TrendData[];
  trendMalam?: TrendData[];
}

export default function Page({ anggota, danrus, currentUser, weekDates, jadwalMingguan, currentStartDate, currentBulan, currentTahun, trendSiang, trendMalam }: Props) {
  const isChiefOrAdmin = currentUser.role === "Chief" || currentUser.role === "Admin";
  
  const navigateWeek = (direction: 'prev' | 'next') => {
    if (!currentStartDate) return;
    const current = new Date(currentStartDate);
    const newDate = new Date(current);
    if (direction === 'prev') {
      newDate.setDate(current.getDate() - 7);
    } else {
      newDate.setDate(current.getDate() + 7);
    }
    
    const formattedDate = newDate.toISOString().split('T')[0];
    router.get('/', { start_date: formattedDate }, { preserveState: true, preserveScroll: true });
  };

  const getShiftBadgeStyle = (shift: string) => {
    switch (shift) {
      case 'Pagi': return 'bg-sky-100 text-sky-800 border-sky-200 shadow-sky-100/50';
      case 'Malam': return 'bg-indigo-100 text-indigo-800 border-indigo-200 shadow-indigo-100/50';
      case 'Libur': return 'bg-rose-100 text-rose-800 border-rose-200 shadow-rose-100/50';
      default: return 'bg-slate-100 text-slate-800 border-slate-200 shadow-slate-100/50';
    }
  };

  const renderStatusIcon = (status: string | null) => {
    if (status === 'Signed') {
      return <CheckCircle2 className="size-5 text-green-500 mx-auto" title="Sudah ditandatangani Danru" />;
    } else if (status === 'Unsigned') {
      return <XCircle className="size-5 text-red-500 mx-auto" title="Belum ditandatangani Danru (Minggu telah lewat)" />;
    } else if (status === 'Not_Available') {
      return null;
    }
    // Pending (Minggu belum lewat)
    return <Minus className="size-5 text-muted-foreground mx-auto opacity-50" title="Minggu belum terlewati" />;
  };

  return (
    <>
      <Head title="Dashboard" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
          <div>
            <h1 className="text-2xl font-bold text-primary uppercase tracking-tight">
              Dashboard {currentUser.role}
            </h1>
            <p className="text-sm text-muted-foreground">
              Selamat datang, <strong className="text-foreground">{currentUser.nama_lengkap}</strong> (Role: <span className="font-semibold">{currentUser.role}</span>
              {currentUser.regu ? ` - ${currentUser.regu}` : ""})
            </p>
          </div>
        </div>

        {isChiefOrAdmin ? (
          <Card className="shadow-xs border-2 overflow-hidden">
            <CardHeader className="bg-muted/30 border-b pb-4">
              <CardTitle className="text-md font-bold flex items-center gap-2">
                <Users className="size-5 text-primary" />
                Status Laporan Mingguan Danru ({currentBulan} {currentTahun})
              </CardTitle>
              <CardDescription>
                Pantau progres persetujuan laporan mingguan dari setiap Danru di bulan ini.
              </CardDescription>
            </CardHeader>
            <CardContent className="p-0">
              {/* Desktop View */}
              <div className="hidden lg:block overflow-x-auto">
                <table className="w-full text-sm text-left">
                  <thead className="bg-muted/50 border-b">
                    <tr>
                      <th className="p-3 font-semibold w-12 text-center text-muted-foreground border-r">No</th>
                      <th className="p-3 font-semibold text-muted-foreground border-r">Nama Danru</th>
                      <th className="p-3 font-semibold text-center text-muted-foreground border-r">Regu</th>
                      <th className="p-3 font-semibold text-center text-muted-foreground border-r">Minggu 1</th>
                      <th className="p-3 font-semibold text-center text-muted-foreground border-r">Minggu 2</th>
                      <th className="p-3 font-semibold text-center text-muted-foreground border-r">Minggu 3</th>
                      <th className="p-3 font-semibold text-center text-muted-foreground border-r">Minggu 4</th>
                      <th className="p-3 font-semibold text-center text-muted-foreground border-r">Minggu 5</th>
                      <th className="p-3 font-semibold text-center text-muted-foreground">Minggu 6</th>
                    </tr>
                  </thead>
                  <tbody>
                    {danrus && danrus.length > 0 ? (
                      danrus.map((d, index) => (
                        <tr key={d.id_user} className="border-b last:border-0 hover:bg-muted/10 transition-colors">
                          <td className="p-3 text-center text-muted-foreground font-mono text-xs border-r">{index + 1}</td>
                          <td className="p-3 font-bold text-foreground border-r">{d.nama_lengkap}</td>
                          <td className="p-3 text-center font-medium border-r"><span className="bg-primary/10 text-primary px-2 py-0.5 rounded text-xs uppercase">{d.regu || '-'}</span></td>
                          <td className="p-3 text-center border-r">{renderStatusIcon(d.mingguan_status.minggu_1)}</td>
                          <td className="p-3 text-center border-r">{renderStatusIcon(d.mingguan_status.minggu_2)}</td>
                          <td className="p-3 text-center border-r">{renderStatusIcon(d.mingguan_status.minggu_3)}</td>
                          <td className="p-3 text-center border-r">{renderStatusIcon(d.mingguan_status.minggu_4)}</td>
                          <td className="p-3 text-center border-r">{renderStatusIcon(d.mingguan_status.minggu_5)}</td>
                          <td className="p-3 text-center">{renderStatusIcon(d.mingguan_status.minggu_6)}</td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan={9} className="p-8 text-center text-muted-foreground">
                          <div className="flex flex-col items-center justify-center gap-2">
                            <AlertTriangle className="size-8 text-muted-foreground/50" />
                            <p>Tidak ada data Danru ditemukan.</p>
                          </div>
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>

              {/* Mobile View */}
              <div className="lg:hidden flex flex-col gap-4 p-4">
                {danrus && danrus.length > 0 ? (
                  danrus.map((d, index) => (
                    <div key={d.id_user} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                      <div className="flex justify-between items-start">
                        <div className="flex gap-3 items-center">
                          <div className="flex items-center justify-center size-8 rounded-full bg-muted font-bold text-muted-foreground text-xs">{index + 1}</div>
                          <div>
                            <div className="font-bold text-foreground text-sm">{d.nama_lengkap}</div>
                            <span className="inline-block mt-1 bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold uppercase">{d.regu || '-'}</span>
                          </div>
                        </div>
                      </div>
                      <div className="grid grid-cols-3 gap-2 mt-2 pt-3 border-t">
                        {[1, 2, 3, 4, 5, 6].map((m) => {
                           const key = `minggu_${m}` as keyof typeof d.mingguan_status;
                           return (
                             <div key={m} className="flex flex-col items-center p-2 bg-muted/30 rounded-lg border text-xs">
                               <span className="font-semibold mb-1 text-muted-foreground">M{m}</span>
                               {renderStatusIcon(d.mingguan_status[key])}
                             </div>
                           );
                        })}
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="p-8 text-center text-muted-foreground border rounded-xl border-dashed">
                    <div className="flex flex-col items-center justify-center gap-2">
                      <AlertTriangle className="size-8 text-muted-foreground/50" />
                      <p className="text-sm">Tidak ada data Danru ditemukan.</p>
                    </div>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        ) : (
          <>
            <Card className="shadow-xs border-2 overflow-hidden">
              <CardHeader className="bg-muted/30 border-b flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4">
                <div>
                  <CardTitle className="text-md font-bold flex items-center gap-2">
                    <CalendarIcon className="size-5 text-primary" />
                    Jadwal Mingguan Anggota
                  </CardTitle>
                  <CardDescription>Jadwal shift anggota sekuriti untuk minggu ini.</CardDescription>
                </div>
                
                <div className="flex items-center gap-2 bg-background p-1 rounded-md border shadow-sm">
                  <Button variant="ghost" size="icon" onClick={() => navigateWeek('prev')} className="h-8 w-8">
                    <ChevronLeft className="size-4" />
                  </Button>
                  <div className="text-xs font-bold px-2 whitespace-nowrap min-w-[120px] text-center">
                    {weekDates?.[0]?.date} s/d {weekDates?.[6]?.date}
                  </div>
                  <Button variant="ghost" size="icon" onClick={() => navigateWeek('next')} className="h-8 w-8">
                    <ChevronRight className="size-4" />
                  </Button>
                </div>
                
                <div className="flex items-center gap-2 bg-background p-1 rounded-md border shadow-sm md:ml-auto">
                  <Button asChild variant="outline" size="sm" className="bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border-green-200 h-8">
                    <a href={`/export/jadwal-mingguan?type=excel&start_date=${currentStartDate}`} target="_blank">
                      Export Excel
                    </a>
                  </Button>
                  <Button asChild variant="outline" size="sm" className="bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 border-red-200 h-8">
                    <a href={`/export/jadwal-mingguan?type=pdf&start_date=${currentStartDate}`} target="_blank">
                      Export PDF
                    </a>
                  </Button>
                </div>
              </CardHeader>
              <CardContent className="p-0">
                {/* Desktop View */}
                <div className="hidden lg:block overflow-x-auto">
                  <table className="w-full text-sm text-left">
                    <thead className="bg-muted/50 border-b">
                      <tr>
                        <th className="p-3 font-semibold w-12 text-center text-muted-foreground border-r">No</th>
                        <th className="p-3 font-semibold text-muted-foreground border-r">Nama Anggota</th>
                        {weekDates?.map((wd) => (
                          <th key={wd.date} className="p-3 font-semibold text-center text-muted-foreground min-w-[100px] border-r last:border-r-0">
                            <div className="flex flex-col">
                              <span className="text-foreground">{wd.day_name}</span>
                              <span className="text-[10px] font-normal">{wd.date}</span>
                            </div>
                          </th>
                        ))}
                      </tr>
                    </thead>
                    <tbody>
                      {anggota && anggota.length > 0 ? (
                        anggota.map((member, index) => (
                          <tr key={member.id_user} className="border-b last:border-0 hover:bg-muted/20 transition-colors">
                            <td className="p-3 text-center text-muted-foreground font-mono text-xs border-r">{index + 1}</td>
                            <td className="p-3 font-bold text-foreground border-r">
                              <Link href={`/anggota/${member.id_user}`} className="hover:underline hover:text-primary transition-colors">
                                {member.nama_lengkap}
                              </Link>
                            </td>
                            {weekDates?.map((wd) => {
                              const shift = jadwalMingguan?.[member.id_user]?.[wd.date] || "Libur";
                              return (
                                <td key={`${member.id_user}-${wd.date}`} className="p-2 text-center border-r last:border-r-0">
                                  <span className={`inline-block px-2 py-1 text-xs font-bold rounded shadow-sm border ${getShiftBadgeStyle(shift)}`}>
                                    {shift}
                                  </span>
                                </td>
                              );
                            })}
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan={9} className="p-8 text-center text-muted-foreground">
                            <div className="flex flex-col items-center justify-center gap-2">
                              <AlertTriangle className="size-8 text-muted-foreground/50" />
                              <p>Tidak ada anggota ditemukan. Jika Anda Danru, pastikan anggota Anda telah terdaftar.</p>
                            </div>
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>

                {/* Mobile View */}
                <div className="lg:hidden flex flex-col gap-4 p-4">
                  {anggota && anggota.length > 0 ? (
                    anggota.map((member, index) => (
                      <div key={member.id_user} className="flex flex-col gap-3 p-4 border rounded-xl bg-card shadow-sm">
                         <div className="flex justify-between items-center border-b pb-3">
                           <div className="flex items-center gap-3">
                             <div className="flex items-center justify-center size-8 rounded-full bg-primary/10 text-primary font-bold text-xs shrink-0">{index + 1}</div>
                             <div className="font-bold text-foreground truncate">
                               <Link href={`/anggota/${member.id_user}`} className="hover:underline hover:text-primary transition-colors">
                                 {member.nama_lengkap}
                               </Link>
                             </div>
                           </div>
                           <Button asChild size="sm" variant="ghost" className="h-8 w-8 p-0 shrink-0 text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-200 bg-red-50/50" title="Catat Pelanggaran">
                             <Link href={`/pelanggaran?id_anggota=${member.id_user}`}>
                               <AlertTriangle className="size-4" />
                             </Link>
                           </Button>
                         </div>
                         <div className="grid grid-cols-2 gap-2">
                           {weekDates?.map((wd) => {
                               const shift = jadwalMingguan?.[member.id_user]?.[wd.date] || "Libur";
                               return (
                                 <div key={`${member.id_user}-${wd.date}`} className="flex justify-between items-center p-2 bg-muted/30 rounded-md border text-xs">
                                   <div className="flex flex-col">
                                     <span className="font-semibold">{wd.day_name}</span>
                                     <span className="text-[10px] text-muted-foreground">{wd.date}</span>
                                   </div>
                                   <span className={`px-2 py-1 font-bold rounded shadow-sm border ${getShiftBadgeStyle(shift)}`}>
                                     {shift}
                                   </span>
                                 </div>
                               );
                           })}
                         </div>
                      </div>
                    ))
                  ) : (
                    <div className="p-8 text-center text-muted-foreground border rounded-xl border-dashed">
                      <div className="flex flex-col items-center justify-center gap-2">
                        <AlertTriangle className="size-8 text-muted-foreground/50" />
                        <p className="text-sm">Tidak ada anggota ditemukan. Jika Anda Danru, pastikan anggota Anda telah terdaftar.</p>
                      </div>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            <div className="hidden lg:grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
              {anggota && anggota.map((person) => (
                <Card key={person.id_user} className="shadow-xs border-2 hover:border-primary/25 transition-all">
                  <CardHeader className="pb-4 flex flex-row items-center justify-between gap-2">
                    <div className="flex flex-row items-center gap-3 min-w-0">
                      <div className="size-10 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                        {person.nama_lengkap.charAt(0).toUpperCase()}
                      </div>
                      <div className="min-w-0 pr-2">
                        <CardTitle className="text-sm font-bold truncate">
                          <Link href={`/anggota/${person.id_user}`} className="hover:underline hover:text-primary transition-colors">
                            {person.nama_lengkap}
                          </Link>
                        </CardTitle>
                        <CardDescription className="text-xs uppercase truncate">{person.regu || "Tanpa Regu"}</CardDescription>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent className="pt-0 flex flex-col gap-3">
                    <Button asChild size="sm" variant="outline" className="w-full gap-1 border-primary/20 text-primary hover:bg-primary/5 font-bold text-xs uppercase">
                      <Link href={`/pelanggaran?id_anggota=${person.id_user}`}>
                        <AlertTriangle className="size-3.5" />
                        Catat Pelanggaran
                      </Link>
                    </Button>
                  </CardContent>
                </Card>
              ))}
            </div>

            {/* Performance Charts (Siang vs Malam) */}
            {trendSiang && trendMalam && anggota && anggota.length > 0 && (
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <Card className="shadow-xs border-2">
                  <CardHeader className="bg-muted/30 border-b pb-4">
                    <CardTitle className="text-md font-bold flex items-center gap-2">
                      <Activity className="size-5 text-primary" />
                      Tren Kinerja: Shift Siang (3 Bulan)
                    </CardTitle>
                    <CardDescription>Skor kedisiplinan & performa anggota regu selama bertugas siang hari.</CardDescription>
                  </CardHeader>
                  <CardContent className="pt-6">
                    <div className="h-[300px] w-full">
                      <ResponsiveContainer width="100%" height="100%">
                        <LineChart data={trendSiang} margin={{ top: 5, right: 10, left: -20, bottom: 5 }}>
                          <CartesianGrid strokeDasharray="3 3" vertical={false} opacity={0.3} />
                          <XAxis dataKey="name" tickLine={false} axisLine={false} tick={{ fontSize: 12 }} />
                          <YAxis tickLine={false} axisLine={false} tick={{ fontSize: 12 }} domain={[0, 100]} />
                          <RechartsTooltip contentStyle={{ borderRadius: '8px', border: '1px solid #e2e8f0', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }} />
                          <Legend wrapperStyle={{ paddingTop: '20px' }} />
                          {anggota.map((person, idx) => (
                            <Line
                              key={person.id_user}
                              type="monotone"
                              dataKey={person.nama_lengkap}
                              stroke={`hsl(${(idx * 137.5) % 360}, 70%, 50%)`}
                              strokeWidth={2}
                              dot={{ r: 4, strokeWidth: 2 }}
                              activeDot={{ r: 6 }}
                            />
                          ))}
                        </LineChart>
                      </ResponsiveContainer>
                    </div>
                  </CardContent>
                </Card>

                <Card className="shadow-xs border-2">
                  <CardHeader className="bg-muted/30 border-b pb-4">
                    <CardTitle className="text-md font-bold flex items-center gap-2">
                      <Activity className="size-5 text-indigo-600" />
                      Tren Kinerja: Shift Malam (3 Bulan)
                    </CardTitle>
                    <CardDescription>Skor kedisiplinan & performa anggota regu selama bertugas malam hari.</CardDescription>
                  </CardHeader>
                  <CardContent className="pt-6">
                    <div className="h-[300px] w-full">
                      <ResponsiveContainer width="100%" height="100%">
                        <LineChart data={trendMalam} margin={{ top: 5, right: 10, left: -20, bottom: 5 }}>
                          <CartesianGrid strokeDasharray="3 3" vertical={false} opacity={0.3} />
                          <XAxis dataKey="name" tickLine={false} axisLine={false} tick={{ fontSize: 12 }} />
                          <YAxis tickLine={false} axisLine={false} tick={{ fontSize: 12 }} domain={[0, 100]} />
                          <RechartsTooltip contentStyle={{ borderRadius: '8px', border: '1px solid #e2e8f0', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }} />
                          <Legend wrapperStyle={{ paddingTop: '20px' }} />
                          {anggota.map((person, idx) => (
                            <Line
                              key={person.id_user}
                              type="monotone"
                              dataKey={person.nama_lengkap}
                              stroke={`hsl(${(idx * 137.5 + 180) % 360}, 70%, 50%)`}
                              strokeWidth={2}
                              dot={{ r: 4, strokeWidth: 2 }}
                              activeDot={{ r: 6 }}
                            />
                          ))}
                        </LineChart>
                      </ResponsiveContainer>
                    </div>
                  </CardContent>
                </Card>
              </div>
            )}
          </>
        )}
      </div>
    </>
  );
}

Page.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;

