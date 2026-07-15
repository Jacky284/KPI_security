import React, { useEffect } from "react";
import { useForm, Head, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

interface User {
  id_user: number;
  nama_lengkap: string;
  role: "Admin" | "Anggota" | "Danru" | "Chief" | "Klien";
  regu: string | null;
  username: string;
  status_aktif: number;
}

interface Props {
  users: User[];
}

export default function UserManagement({ users }: Props) {
  const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
    nama_lengkap: "",
    role: "Anggota",
    regu: "",
    username: "",
    password: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/admin/users", {
      onSuccess: () => {
        reset();
        alert("User berhasil didaftarkan!");
      },
    });
  };

  const handleToggleStatus = (id: number) => {
    if (confirm("Apakah Anda yakin ingin mengubah status aktif user ini?")) {
      router.post(`/admin/users/${id}/toggle-status`);
    }
  };

  // Clear regu if role does not require it
  useEffect(() => {
    if (data.role !== "Danru" && data.role !== "Anggota") {
      setData("regu", "");
    }
  }, [data.role]);

  return (
    <>
      <Head title="Manajemen User" />
      <div className="flex flex-col gap-6 max-w-6xl mx-auto py-6">
        <div>
          <h1 className="text-2xl font-bold text-primary">Manajemen Akun Pengguna</h1>
          <p className="text-sm text-muted-foreground">Daftarkan dan kelola akun personil keamanan, koordinator, dan klien.</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Form Column */}
          <div className="lg:col-span-1">
            <Card className="shadow-xs border-2">
              <CardHeader>
                <CardTitle className="text-md font-bold">Daftarkan Akun Baru</CardTitle>
                <CardDescription>Masukkan informasi akun personil baru di bawah ini.</CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                  {/* Nama Lengkap */}
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-bold text-foreground uppercase tracking-wider">Nama Lengkap</label>
                    <input
                      type="text"
                      value={data.nama_lengkap}
                      onChange={(e) => setData("nama_lengkap", e.target.value)}
                      className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                      placeholder="Nama Lengkap..."
                      required
                    />
                    {errors.nama_lengkap && <span className="text-xs text-destructive">{errors.nama_lengkap}</span>}
                  </div>

                  {/* Role */}
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-bold text-foreground uppercase tracking-wider">Role / Hak Akses</label>
                    <select
                      value={data.role}
                      onChange={(e) => setData("role", e.target.value as any)}
                      className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
                      required
                    >
                      <option value="Anggota">Anggota (Security Officer)</option>
                      <option value="Danru">Danru (Komandan Regu)</option>
                      <option value="Chief">Chief Security</option>
                      <option value="Klien">Klien (Pengguna Jasa)</option>
                      <option value="Admin">Admin Sistem</option>
                    </select>
                    {errors.role && <span className="text-xs text-destructive">{errors.role}</span>}
                  </div>

                  {/* Regu (Visible only for Danru and Anggota) */}
                  {(data.role === "Danru" || data.role === "Anggota") && (
                    <div className="flex flex-col gap-1.5">
                      <label className="text-xs font-bold text-foreground uppercase tracking-wider">Regu</label>
                      <input
                        type="text"
                        value={data.regu}
                        onChange={(e) => setData("regu", e.target.value)}
                        className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                        placeholder="Contoh: Regu 1, Regu 2..."
                        required
                      />
                      {errors.regu && <span className="text-xs text-destructive">{errors.regu}</span>}
                    </div>
                  )}

                  {/* Username */}
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-bold text-foreground uppercase tracking-wider">Username</label>
                    <input
                      type="text"
                      value={data.username}
                      onChange={(e) => setData("username", e.target.value)}
                      className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                      placeholder="Username login..."
                      required
                    />
                    {errors.username && <span className="text-xs text-destructive">{errors.username}</span>}
                  </div>

                  {/* Password */}
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-bold text-foreground uppercase tracking-wider">Password</label>
                    <input
                      type="password"
                      value={data.password}
                      onChange={(e) => setData("password", e.target.value)}
                      className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                      placeholder="Minimal 6 karakter..."
                      required
                    />
                    {errors.password && <span className="text-xs text-destructive">{errors.password}</span>}
                  </div>

                  <Button type="submit" disabled={processing} className="w-full mt-2 font-bold uppercase">
                    {processing ? "Memproses..." : "Daftarkan User"}
                  </Button>
                </form>
              </CardContent>
            </Card>
          </div>

          {/* Table Column */}
          <div className="lg:col-span-2">
            <Card className="shadow-xs border">
              <CardHeader>
                <CardTitle className="text-md font-bold">Daftar Akun Terdaftar</CardTitle>
                <CardDescription>List semua akun pengguna aktif dan non-aktif.</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-sm border-collapse">
                    <thead>
                      <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                        <th className="p-3">Nama</th>
                        <th className="p-3">Username</th>
                        <th className="p-3">Role</th>
                        <th className="p-3">Regu</th>
                        <th className="p-3 text-center">Status</th>
                        <th className="p-3 text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      {users.map((user) => (
                        <tr key={user.id_user} className="border-b hover:bg-muted/10">
                          <td className="p-3 font-semibold text-foreground">{user.nama_lengkap}</td>
                          <td className="p-3 font-medium">{user.username}</td>
                          <td className="p-3">
                            <span className="px-2 py-0.5 rounded-full text-xs font-bold bg-muted border">
                              {user.role}
                            </span>
                          </td>
                          <td className="p-3">{user.regu || "-"}</td>
                          <td className="p-3 text-center">
                            {user.status_aktif ? (
                              <span className="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                Aktif
                              </span>
                            ) : (
                              <span className="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                Non-aktif
                              </span>
                            )}
                          </td>
                          <td className="p-3 text-center">
                            <Button
                              size="sm"
                              variant={user.status_aktif ? "destructive" : "outline"}
                              onClick={() => handleToggleStatus(user.id_user)}
                              className="text-xs px-2.5 h-7 font-bold"
                            >
                              {user.status_aktif ? "Nonaktifkan" : "Aktifkan"}
                            </Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </>
  );
}

UserManagement.layout = (page: React.ReactNode) => <DashboardLayout>{page}</DashboardLayout>;
