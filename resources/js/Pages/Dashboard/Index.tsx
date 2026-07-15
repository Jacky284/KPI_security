import React from "react";
import { Link, Head } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Users, AlertTriangle } from "lucide-react";

interface Anggota {
  id_user: number;
  nama_lengkap: string;
  regu: string | null;
  username: string;
}

interface CurrentUser {
  nama_lengkap: string;
  role: "Admin" | "Danru" | "Chief" | "Klien" | "Anggota";
  regu: string | null;
}

interface Props {
  anggota: Anggota[];
  currentUser: CurrentUser;
}

export default function Page({ anggota, currentUser }: Props) {
  const isDanru = currentUser.role === "Danru";

  return (
    <>
      <Head title="Dashboard - Daftar Anggota" />
      <div className="flex flex-col gap-6 max-w-5xl mx-auto py-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
          <div>
            <h1 className="text-2xl font-bold text-primary uppercase tracking-tight">
              {isDanru ? `Daftar Anggota Regu Saya (${currentUser.regu})` : "Daftar Seluruh Anggota Sekuriti"}
            </h1>
            <p className="text-sm text-muted-foreground">
              Selamat datang, <strong className="text-foreground">{currentUser.nama_lengkap}</strong> (Role: <span className="font-semibold">{currentUser.role}</span>
              {currentUser.regu ? ` - ${currentUser.regu}` : ""})
            </p>
          </div>
        </div>

        {/* Member cards list */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {anggota.length > 0 ? (
            anggota.map((person) => (
              <Card key={person.id_user} className="shadow-xs border-2 hover:border-primary/25 transition-all">
                <CardHeader className="pb-3 flex flex-row items-center gap-3">
                  {/* Avatar Placeholder */}
                  <div className="size-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                    {person.nama_lengkap.charAt(0).toUpperCase()}
                  </div>
                  <div>
                    <CardTitle className="text-sm font-bold">{person.nama_lengkap}</CardTitle>
                    <CardDescription className="text-xs uppercase">{person.regu || "Tanpa Regu"}</CardDescription>
                  </div>
                </CardHeader>
                <CardContent className="pt-2 flex flex-col gap-3">
                  <div className="text-xs text-muted-foreground flex flex-col gap-1">
                    <div>Username: <strong className="text-foreground font-semibold">{person.username}</strong></div>
                    <div>Status Jaga: <span className="text-green-600 font-semibold">Aktif</span></div>
                  </div>
                  
                  {/* Quick Action Button for Danru/Admin to report violations */}
                  {(isDanru || currentUser.role === "Admin") && (
                    <Button asChild size="sm" variant="outline" className="w-full mt-2 gap-1 border-primary/20 text-primary hover:bg-primary/5 font-bold text-xs uppercase">
                      <Link href={`/pelanggaran?id_anggota=${person.id_user}`}>
                        <AlertTriangle className="size-3.5" />
                        Catat Pelanggaran
                      </Link>
                    </Button>
                  )}
                </CardContent>
              </Card>
            ))
          ) : (
            <div className="col-span-full text-center py-12 border-2 border-dashed rounded-lg bg-card/50">
              <Users className="size-12 text-muted-foreground mx-auto mb-3" />
              <h3 className="font-bold text-foreground">Tidak Ada Anggota</h3>
              <p className="text-xs text-muted-foreground mt-1">Belum ada personil yang didaftarkan ke regu Anda.</p>
            </div>
          )}
        </div>
      </div>
    </>
  );
}

Page.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
