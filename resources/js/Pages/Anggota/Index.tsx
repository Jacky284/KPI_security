import React, { useMemo, useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent } from "@/components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/dialog";
import { ArrowRightLeft } from "lucide-react";

interface User {
  id_user: number;
  nama_lengkap: string;
  role?: string;
  regu: string | null;
  foto_profil: string | null;
  usia: string;
  hari_kerja: number;
}

interface Props {
  anggota: User[];
  reguList?: string[];
}

export default function Index({ anggota, reguList = [] }: Props) {
  const page = usePage();
  const currentUserRole = (page.props as any).auth?.user?.role;
  const canMoveRegu = ["Admin", "Chief"].includes(currentUserRole);

  const [isMoveModalOpen, setIsMoveModalOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const { data, setData, put, processing, reset } = useForm({
    regu: ""
  });

  const handleOpenMoveModal = (user: User) => {
    setSelectedUser(user);
    setData("regu", user.regu || "");
    setIsMoveModalOpen(true);
  };

  const handleMoveRegu = (e: React.FormEvent) => {
    e.preventDefault();
    if (selectedUser) {
      put(`/anggota/${selectedUser.id_user}/pindah-regu`, {
        onSuccess: () => {
          setIsMoveModalOpen(false);
          reset();
        }
      });
    }
  };

  const groupedData = useMemo(() => {
    const groups: Record<string, User[]> = {};

    (anggota || []).forEach((user) => {
      const reguName = user.regu?.trim() || "Tanpa Regu";
      if (!groups[reguName]) {
        groups[reguName] = [];
      }
      groups[reguName].push(user);
    });

    const sortedReguKeys = Object.keys(groups).sort((a, b) => {
      if (a === "Tanpa Regu") return 1;
      if (b === "Tanpa Regu") return -1;
      return a.localeCompare(b, undefined, { numeric: true, sensitivity: "base" });
    });

    return sortedReguKeys.map((reguKey) => {
      const members = [...groups[reguKey]].sort((a, b) => {
        const isDanruA = a.role === "Danru" ? 0 : 1;
        const isDanruB = b.role === "Danru" ? 0 : 1;
        if (isDanruA !== isDanruB) return isDanruA - isDanruB;
        return a.nama_lengkap.localeCompare(b.nama_lengkap);
      });
      return { regu: reguKey, members };
    });
  }, [anggota]);

  return (
    <>
      <Head title="Manajemen Anggota" />
      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div>
          <h1 className="text-2xl font-bold text-primary tracking-tight">Manajemen Anggota</h1>
          <p className="text-sm text-muted-foreground">Pilih anggota untuk melihat profil lengkap, riwayat kinerja, dan tren skor KPI bulanan.</p>
        </div>

        {/* Desktop Table View */}
        <div className="hidden md:block rounded-md border shadow-sm bg-card overflow-hidden">
          <table className="w-full text-sm text-left">
            <thead className="bg-muted text-muted-foreground font-semibold uppercase text-xs">
                  <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                    <th className="px-6 py-4 text-center">NAMA</th>
                    <th className="px-6 py-4 text-center">USIA</th>
                    <th className="px-6 py-4 text-center">TOTAL HARI KERJA</th>
                    <th className="px-6 py-4 text-center">AKSI</th>
                  </tr>
            </thead>
            <tbody>
              {groupedData.length > 0 ? (
                groupedData.map((group) => (
                  <React.Fragment key={group.regu}>
                    {/* Regu Section Header */}
                    <tr className="bg-muted/70 text-primary font-bold border-b border-t">
                      <td colSpan={4} className="px-6 py-2.5 uppercase tracking-wider text-xs bg-muted/50">
                        {group.regu}
                      </td>
                    </tr>
                    {group.members.map((user) => (
                      <tr key={user.id_user} className="border-b last:border-0 hover:bg-muted/30 transition-colors">
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-3">
                            <Avatar className="w-10 h-10 border">
                              <AvatarImage src={user.foto_profil || undefined} />
                              <AvatarFallback className="text-xs bg-primary/10 text-primary font-bold">
                                {user.nama_lengkap.split(" ").map(n => n[0]).join("").substring(0, 2).toUpperCase()}
                              </AvatarFallback>
                            </Avatar>
                            <div>
                              <div className="flex items-center gap-2">
                                <span className="font-bold text-foreground">{user.nama_lengkap}</span>
                                {user.role === "Danru" && (
                                  <span className="bg-destructive/15 text-destructive font-bold text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider">
                                    Danru
                                  </span>
                                )}
                              </div>
                              <div className="text-xs text-muted-foreground font-semibold">{user.regu || "Tanpa Regu"}</div>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 text-center font-medium">
                          {user.usia}
                        </td>
                        <td className="px-6 py-4 text-center">
                          <span className="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-bold text-xs">
                            {user.hari_kerja} Hari
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex items-center justify-center gap-2">
                            {canMoveRegu && (
                              <Button size="icon" variant="outline" className="h-8 w-8 text-muted-foreground hover:text-primary" onClick={() => handleOpenMoveModal(user)} title="Pindah Regu">
                                <ArrowRightLeft className="w-4 h-4" />
                              </Button>
                            )}
                            <Link href={`/anggota/${user.id_user}`}>
                              <Button size="sm" variant="outline">Lihat Profil</Button>
                            </Link>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </React.Fragment>
                ))
              ) : (
                <tr>
                  <td colSpan={4} className="py-12 text-center text-muted-foreground">
                    Belum ada anggota yang terdaftar atau dapat ditampilkan.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Mobile View */}
        <div className="flex md:hidden flex-col gap-6">
          {groupedData.map((group) => (
            <div key={group.regu} className="flex flex-col gap-3">
              <h2 className="font-bold text-primary uppercase text-xs tracking-wider px-1">{group.regu}</h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {group.members.map((user) => (
                  <div key={user.id_user} className="relative">
                    {canMoveRegu && (
                      <Button 
                        size="icon" 
                        variant="outline" 
                        className="absolute top-2 right-2 h-8 w-8 text-muted-foreground hover:text-primary z-10" 
                        onClick={(e) => { e.preventDefault(); handleOpenMoveModal(user); }} 
                        title="Pindah Regu"
                      >
                        <ArrowRightLeft className="w-4 h-4" />
                      </Button>
                    )}
                    <Link href={`/anggota/${user.id_user}`}>
                      <Card className="hover:shadow-lg transition-shadow cursor-pointer">
                        <CardContent className="pt-6 flex flex-col items-center text-center gap-3">
                          <Avatar className="w-20 h-20 border-2 border-primary/20">
                            <AvatarImage src={user.foto_profil || undefined} />
                            <AvatarFallback className="text-lg bg-primary/10 text-primary font-bold">
                              {user.nama_lengkap.split(" ").map(n => n[0]).join("").substring(0, 2).toUpperCase()}
                            </AvatarFallback>
                          </Avatar>
                          <div>
                            <div className="flex items-center justify-center gap-2">
                              <h3 className="font-bold text-foreground text-md leading-tight">{user.nama_lengkap}</h3>
                              {user.role === "Danru" && (
                                <span className="bg-destructive/15 text-destructive font-bold text-[10px] px-2 py-0.5 rounded-full uppercase">
                                  Danru
                                </span>
                              )}
                            </div>
                            <p className="text-sm text-muted-foreground font-semibold mb-1">{user.regu || "Tanpa Regu"}</p>
                            <p className="text-xs text-muted-foreground">
                              Usia: {user.usia} • {user.hari_kerja} Hari Kerja
                            </p>
                          </div>
                        </CardContent>
                      </Card>
                    </Link>
                  </div>
                ))}
              </div>
            </div>
          ))}
          {groupedData.length === 0 && (
            <div className="py-12 text-center text-muted-foreground">
              Belum ada anggota yang terdaftar atau dapat ditampilkan.
            </div>
          )}
        </div>

        <Dialog open={isMoveModalOpen} onOpenChange={setIsMoveModalOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Pindah Regu</DialogTitle>
              <DialogDescription>
                Pindahkan <span className="font-bold text-foreground">{selectedUser?.nama_lengkap}</span> ke regu lain.
              </DialogDescription>
            </DialogHeader>
            <form onSubmit={handleMoveRegu} className="flex flex-col gap-4 py-4">
              <div className="flex flex-col gap-2">
                <label className="text-sm font-semibold">Regu Baru</label>
                <select
                  value={data.regu}
                  onChange={(e) => setData("regu", e.target.value)}
                  className="w-full border rounded-md p-2 text-sm bg-background"
                  required
                >
                  <option value="" disabled>Pilih Regu</option>
                  {reguList.map(r => (
                    <option key={r} value={r}>{r}</option>
                  ))}
                  <option value="Tanpa Regu">Tanpa Regu</option>
                </select>
              </div>
              <DialogFooter>
                <Button type="button" variant="outline" onClick={() => setIsMoveModalOpen(false)}>Batal</Button>
                <Button type="submit" disabled={processing}>Simpan Perubahan</Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
      </div>
    </>
  );
}

Index.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
