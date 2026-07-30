import React, { useEffect, useState } from "react";
import { useForm, Head, router } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Edit, Ban, CheckCircle } from "lucide-react";
import { toast } from "sonner";
import { ConfirmModal } from "@/components/ConfirmModal";

interface User {
  id_user: number;
  nama_lengkap: string;
  role: "Admin" | "Anggota" | "Danru" | "Chief" | "Klien";
  regu: string | null;
  username: string;
  status_aktif: number;
  tempat_lahir?: string;
  tanggal_lahir?: string;
  sisa_cuti?: number;
}

interface Regu {
  id_regu: number;
  nama_regu: string;
}

interface Props {
  users: User[];
  regus: Regu[];
}

export default function UserManagement({ users, regus = [] }: Props) {
  // Add User Form
  const { data, setData, post, processing, errors, reset } = useForm({
    nama_lengkap: "",
    role: "Anggota",
    regu: "",
    username: "",
    password: "",
    tempat_lahir: "",
    tanggal_lahir: "",
    sisa_cuti: 12,
  });

  // Edit User Form
  const { data: editData, setData: setEditData, put: update, processing: editProcessing, errors: editErrors, reset: editReset } = useForm({
    nama_lengkap: "",
    role: "Anggota",
    regu: "",
    username: "",
    password: "",
    tempat_lahir: "",
    tanggal_lahir: "",
    sisa_cuti: 12,
  });

  const [editingUserId, setEditingUserId] = useState<number | null>(null);

  // Add Regu Form
  const { data: reguData, setData: setReguData, post: postRegu, processing: reguProcessing, reset: resetRegu } = useForm({
    nama_regu: "",
  });
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [deleteName, setDeleteName] = useState<string>("");
  const [toggleId, setToggleId] = useState<number | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/admin/users", {
      preserveScroll: true,
      onSuccess: () => {
        reset();
      },
    });
  };

  const handleEditSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingUserId) {
      update(`/admin/users/${editingUserId}`, {
        preserveScroll: true,
        onSuccess: () => {
          setEditingUserId(null);
        },
      });
    }
  };

  const handleToggleStatus = (id: number) => {
    setToggleId(id);
  };

  const confirmToggleStatus = () => {
    if (toggleId !== null) {
      router.post(`/admin/users/${toggleId}/toggle-status`, {}, {
        preserveScroll: true,
        onFinish: () => setToggleId(null)
      });
    }
  };

  const confirmDelete = () => {
    if (deleteId !== null) {
      router.delete(`/admin/users/${deleteId}`, {
        onSuccess: () => cancelEdit(),
        onFinish: () => {
          setDeleteId(null);
          setDeleteName("");
        }
      });
    }
  };

  const handleAddRegu = (e: React.FormEvent) => {
    e.preventDefault();
    postRegu("/admin/regus", {
      preserveScroll: true,
      onSuccess: () => {
        resetRegu();
      }
    });
  };

  const handleQuickReguChange = (user: User, newRegu: string) => {
    router.put(`/admin/users/${user.id_user}`, {
      nama_lengkap: user.nama_lengkap,
      role: user.role,
      username: user.username,
      regu: newRegu,
      tempat_lahir: user.tempat_lahir || "",
      tanggal_lahir: user.tanggal_lahir || "",
      sisa_cuti: user.sisa_cuti ?? 12,
    }, {
      preserveScroll: true,
    });
  };

  const startEdit = (user: User) => {
    setEditingUserId(user.id_user);
    setEditData({
      nama_lengkap: user.nama_lengkap,
      role: user.role,
      regu: user.regu || "",
      username: user.username,
      password: "", // empty password means no change
      tempat_lahir: user.tempat_lahir || "",
      tanggal_lahir: user.tanggal_lahir || "",
      sisa_cuti: user.sisa_cuti !== undefined ? user.sisa_cuti : 12,
    });
  };

  const cancelEdit = () => {
    setEditingUserId(null);
    editReset();
  };

  // Clear regu if role does not require it (Create)
  useEffect(() => {
    if (data.role !== "Danru" && data.role !== "Anggota") {
      setData("regu", "");
    }
  }, [data.role]);

  // Clear regu if role does not require it (Edit)
  useEffect(() => {
    if (editData.role !== "Danru" && editData.role !== "Anggota") {
      setEditData("regu", "");
    }
  }, [editData.role]);

  return (
    <>
      <Head title="Manajemen User" />
      
      {/* Edit Modal / Dialog */}
      {editingUserId && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <Card className="w-full max-w-lg shadow-xl">
            <CardHeader>
              <CardTitle>Edit Akun Pengguna</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleEditSubmit} className="flex flex-col gap-4">
                {/* Same fields as Add User */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-bold text-foreground uppercase tracking-wider">Nama Lengkap</label>
                  <input
                    type="text"
                    value={editData.nama_lengkap}
                    onChange={(e) => setEditData("nama_lengkap", e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                    required
                  />
                  {editErrors.nama_lengkap && <span className="text-xs text-destructive">{editErrors.nama_lengkap}</span>}
                </div>
                
                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-bold text-foreground uppercase tracking-wider">Role</label>
                  <select
                    value={editData.role}
                    onChange={(e) => setEditData("role", e.target.value as any)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
                    required
                  >
                    <option value="Anggota">Anggota</option>
                    <option value="Danru">Danru</option>
                    <option value="Chief">Chief</option>
                    <option value="Klien">Klien</option>
                    <option value="Admin">Admin</option>
                  </select>
                </div>

                {(editData.role === "Danru" || editData.role === "Anggota") && (
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-bold text-foreground uppercase tracking-wider">Regu</label>
                    <select
                      value={editData.regu}
                      onChange={(e) => setEditData("regu", e.target.value)}
                      className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                      required
                    >
                      <option value="">-- Pilih Regu --</option>
                      {regus.map(r => (
                        <option key={r.id_regu} value={r.nama_regu}>{r.nama_regu}</option>
                      ))}
                    </select>
                  </div>
                )}

                <div className="grid grid-cols-2 gap-4">
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-bold text-foreground uppercase tracking-wider">Tempat Lahir</label>
                    <input
                      type="text"
                      value={editData.tempat_lahir}
                      onChange={(e) => setEditData("tempat_lahir", e.target.value)}
                      className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                    />
                    {editErrors.tempat_lahir && <span className="text-xs text-destructive">{editErrors.tempat_lahir}</span>}
                  </div>
                  
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-bold text-foreground uppercase tracking-wider">Tanggal Lahir</label>
                    <input
                      type="date"
                      value={editData.tanggal_lahir}
                      onChange={(e) => setEditData("tanggal_lahir", e.target.value)}
                      className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                    />
                    {editErrors.tanggal_lahir && <span className="text-xs text-destructive">{editErrors.tanggal_lahir}</span>}
                  </div>
                </div>

                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-bold text-foreground uppercase tracking-wider">Sisa Cuti (Hari)</label>
                  <input
                    type="number"
                    value={editData.sisa_cuti}
                    onChange={(e) => setEditData("sisa_cuti", parseInt(e.target.value) || 0)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                  />
                  {editErrors.sisa_cuti && <span className="text-xs text-destructive">{editErrors.sisa_cuti}</span>}
                </div>

                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-bold text-foreground uppercase tracking-wider">Username</label>
                  <input
                    type="text"
                    value={editData.username}
                    onChange={(e) => setEditData("username", e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                    required
                  />
                  {editErrors.username && <span className="text-xs text-destructive">{editErrors.username}</span>}
                </div>

                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-bold text-foreground uppercase tracking-wider">Password (Kosongkan jika tidak diubah)</label>
                  <input
                    type="password"
                    value={editData.password}
                    onChange={(e) => setEditData("password", e.target.value)}
                    className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                  />
                  {editErrors.password && <span className="text-xs text-destructive">{editErrors.password}</span>}
                </div>

                <div className="flex justify-between items-center mt-2 pt-2 border-t">
                  <Button 
                    type="button" 
                    variant="destructive" 
                    onClick={() => {
                      setDeleteId(editingUserId);
                      setDeleteName(editData.nama_lengkap);
                    }}
                  >
                    Hapus
                  </Button>
                  <div className="flex gap-2">
                    <Button type="button" variant="outline" onClick={cancelEdit}>Batal</Button>
                    <Button type="submit" disabled={editProcessing}>Simpan Perubahan</Button>
                  </div>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      )}

      <Head title="Manajemen User & Regu" />

      <ConfirmModal
        isOpen={toggleId !== null}
        onOpenChange={(open) => !open && setToggleId(null)}
        title="Ubah Status User"
        description="Apakah Anda yakin ingin mengubah status aktif user ini?"
        onConfirm={confirmToggleStatus}
        confirmText="Ya, Ubah"
      />

      <ConfirmModal
        isOpen={deleteId !== null}
        onOpenChange={(open) => {
          if (!open) {
            setDeleteId(null);
            setDeleteName("");
          }
        }}
        title="Hapus User"
        description={`Apakah Anda yakin ingin menghapus user ${deleteName}? Aksi ini tidak dapat dibatalkan!`}
        onConfirm={confirmDelete}
        confirmText="Ya, Hapus"
      />

      <div className="flex flex-col gap-6 max-w-7xl mx-auto">
        <div>
          <h1 className="text-2xl font-bold text-primary tracking-tight">Manajemen Akun Pengguna</h1>
          <p className="text-sm text-muted-foreground">Daftarkan dan kelola akun personil keamanan, koordinator, dan klien.</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Form Column */}
          <div className="lg:col-span-1 flex flex-col gap-4">
            {/* Add Regu Form */}
            <Card className="shadow-xs border-2">
              <CardHeader className="pb-3">
                <CardTitle className="text-md font-bold">Tambah Regu Baru</CardTitle>
                <CardDescription className="text-xs">Tambahkan pilihan regu baru jika diperlukan.</CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleAddRegu} className="flex gap-2">
                  <input 
                    type="text"
                    value={reguData.nama_regu}
                    onChange={e => setReguData("nama_regu", e.target.value)}
                    className="flex-1 p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                    placeholder="Nama Regu..."
                    required
                  />
                  <Button type="submit" disabled={reguProcessing} className="px-4 font-bold">+</Button>
                </form>
              </CardContent>
            </Card>

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
                      <select
                        value={data.regu}
                        onChange={(e) => setData("regu", e.target.value)}
                        className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                        required
                      >
                        <option value="">-- Pilih Regu --</option>
                        {regus.map(r => (
                          <option key={r.id_regu} value={r.nama_regu}>{r.nama_regu}</option>
                        ))}
                      </select>
                      {errors.regu && <span className="text-xs text-destructive">{errors.regu}</span>}
                    </div>
                  )}

                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex flex-col gap-1.5">
                      <label className="text-xs font-bold text-foreground uppercase tracking-wider">Tempat Lahir</label>
                      <input
                        type="text"
                        value={data.tempat_lahir}
                        onChange={(e) => setData("tempat_lahir", e.target.value)}
                        className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                      />
                      {errors.tempat_lahir && <span className="text-xs text-destructive">{errors.tempat_lahir}</span>}
                    </div>
                    
                    <div className="flex flex-col gap-1.5">
                      <label className="text-xs font-bold text-foreground uppercase tracking-wider">Tanggal Lahir</label>
                      <input
                        type="date"
                        value={data.tanggal_lahir}
                        onChange={(e) => setData("tanggal_lahir", e.target.value)}
                        className="w-full p-2 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                      />
                      {errors.tanggal_lahir && <span className="text-xs text-destructive">{errors.tanggal_lahir}</span>}
                    </div>
                  </div>

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
                {/* Desktop View */}
                <div className="hidden lg:block overflow-x-auto">
                  <table className="w-full text-left text-sm border-collapse">
                    <thead>
                      <tr className="border-b bg-muted/50 text-muted-foreground font-bold">
                        <th className="p-3">Nama</th>
                        <th className="p-3">Role</th>
                        <th className="p-3 w-24">Regu</th>
                        <th className="p-3 text-center">Status</th>
                        <th className="p-3 text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      {Object.entries(
                        users.reduce((acc, user) => {
                          const regu = user.regu || "Tanpa Regu";
                          if (!acc[regu]) acc[regu] = [];
                          acc[regu].push(user);
                          return acc;
                        }, {} as Record<string, User[]>)
                      ).map(([reguName, reguUsers]) => {
                        const sortedReguUsers = [...reguUsers].sort((a, b) => {
                          if (a.role === 'Danru' && b.role !== 'Danru') return -1;
                          if (a.role !== 'Danru' && b.role === 'Danru') return 1;
                          return a.nama_lengkap.localeCompare(b.nama_lengkap);
                        });
                        return (
                          <React.Fragment key={reguName}>
                            <tr className="border-b bg-muted/30">
                              <td colSpan={6} className="p-3 font-black text-primary uppercase text-[10px] tracking-widest bg-muted/50">
                                {reguName === "Tanpa Regu" ? "TANPA REGU" : reguName.toUpperCase()}
                              </td>
                            </tr>
                            {sortedReguUsers.map((user) => (
                            <tr key={user.id_user} className="border-b hover:bg-muted/10">
                              <td className="p-3 font-semibold text-foreground">
                                <div className="flex items-center gap-2">
                                  {user.role === 'Danru' && (
                                    <span className="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-primary text-primary-foreground">
                                      DANRU
                                    </span>
                                  )}
                                  {user.nama_lengkap}
                                </div>
                              </td>
                              <td className="p-3">
                                <span className="px-2 py-0.5 rounded-full text-xs font-bold bg-muted border">
                                  {user.role}
                                </span>
                              </td>
                              <td className="p-3">
                                {(user.role === 'Danru' || user.role === 'Anggota') ? (
                                  <select
                                    value={user.regu || ""}
                                    onChange={(e) => handleQuickReguChange(user, e.target.value)}
                                    className="p-1 text-xs border rounded bg-background font-semibold focus:ring-1 focus:ring-primary w-20 text-center"
                                  >
                                    <option value="">-- Pilih Regu --</option>
                                    {regus.map(r => (
                                      <option key={r.id_regu} value={r.nama_regu}>{r.nama_regu}</option>
                                    ))}
                                  </select>
                                ) : (
                                  <span className="text-xs text-muted-foreground">-</span>
                                )}
                              </td>
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
                              <td className="p-3 text-center flex justify-center gap-1">
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => startEdit(user)}
                                  className="h-7 w-7 p-0"
                                  title="Edit"
                                >
                                  <Edit className="w-4 h-4" />
                                </Button>
                                <Button
                                  size="sm"
                                  variant={user.status_aktif ? "destructive" : "outline"}
                                  onClick={() => handleToggleStatus(user.id_user)}
                                  className="h-7 w-7 p-0"
                                  title={user.status_aktif ? "Nonaktifkan" : "Aktifkan"}
                                >
                                  {user.status_aktif ? <Ban className="w-4 h-4" /> : <CheckCircle className="w-4 h-4" />}
                                </Button>
                              </td>
                            </tr>
                          ))}
                        </React.Fragment>
                      );
                    })}
                    </tbody>
                  </table>
                </div>

                {/* Mobile View */}
                <div className="lg:hidden flex flex-col gap-4">
                  {Object.entries(
                    users.reduce((acc, user) => {
                      const regu = user.regu || "Tanpa Regu";
                      if (!acc[regu]) acc[regu] = [];
                      acc[regu].push(user);
                      return acc;
                    }, {} as Record<string, User[]>)
                  ).map(([reguName, reguUsers]) => {
                    const sortedReguUsers = [...reguUsers].sort((a, b) => {
                      if (a.role === 'Danru' && b.role !== 'Danru') return -1;
                      if (a.role !== 'Danru' && b.role === 'Danru') return 1;
                      return a.nama_lengkap.localeCompare(b.nama_lengkap);
                    });
                    return (
                      <div key={reguName} className="flex flex-col gap-3">
                        <div className="font-black text-primary uppercase text-[10px] tracking-widest bg-muted/50 p-2 rounded">
                          {reguName === "Tanpa Regu" ? "TANPA REGU" : reguName.toUpperCase()}
                        </div>
                        {sortedReguUsers.map((user) => (
                          <div key={user.id_user} className="flex flex-col gap-2 p-3 border rounded-lg bg-card shadow-xs">
                            <div className="flex justify-between items-start">
                              <div>
                                <div className="font-semibold text-foreground text-sm flex items-center gap-1.5">
                                  {user.role === 'Danru' && (
                                    <span className="px-1 py-0.2 rounded text-[9px] font-extrabold bg-primary text-primary-foreground">
                                      DANRU
                                    </span>
                                  )}
                                  {user.nama_lengkap}
                                </div>
                              </div>
                              <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-muted border">
                                {user.role}
                              </span>
                            </div>
                            {(user.role === 'Danru' || user.role === 'Anggota') && (
                              <div className="flex items-center gap-2 mt-1">
                                <span className="text-xs font-bold text-muted-foreground">Regu:</span>
                                <select
                                  value={user.regu || ""}
                                  onChange={(e) => handleQuickReguChange(user, e.target.value)}
                                  className="p-1 text-xs border rounded bg-background font-semibold focus:ring-1 focus:ring-primary flex-1"
                                >
                                  <option value="">-- Pilih Regu --</option>
                                  {regus.map(r => (
                                    <option key={r.id_regu} value={r.nama_regu}>{r.nama_regu}</option>
                                  ))}
                                </select>
                              </div>
                            )}
                            <div className="flex justify-between items-center mt-2 pt-2 border-t">
                              <div>
                                {user.status_aktif ? (
                                  <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">
                                    Aktif
                                  </span>
                                ) : (
                                  <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                    Non-aktif
                                  </span>
                                )}
                              </div>
                              <div className="flex gap-2">
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => startEdit(user)}
                                  className="h-7 w-7 p-0"
                                  title="Edit"
                                >
                                  <Edit className="w-4 h-4" />
                                </Button>
                                <Button
                                  size="sm"
                                  variant={user.status_aktif ? "destructive" : "outline"}
                                  onClick={() => handleToggleStatus(user.id_user)}
                                  className="h-7 w-7 p-0"
                                  title={user.status_aktif ? "Nonaktifkan" : "Aktifkan"}
                                >
                                  {user.status_aktif ? <Ban className="w-4 h-4" /> : <CheckCircle className="w-4 h-4" />}
                                </Button>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    );
                  })}
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
