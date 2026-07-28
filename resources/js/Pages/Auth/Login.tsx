import React from "react";
import { useForm, Head } from "@inertiajs/react";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export default function Login() {
  const { data, setData, post, processing, errors, reset } = useForm({
    username: "",
    password: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/login", {
      onFinish: () => reset("password"),
    });
  };

  return (
    <>
      <Head title="Login - PMS Security" />
      <div className="flex min-h-screen items-center justify-center bg-background px-4 py-12 sm:px-6 lg:px-8">
        <div className="w-full max-w-md space-y-8">
          <div className="flex flex-col items-center">
            <div className="flex h-28 w-28 items-center justify-center mb-2">
              <img src="/images/logo-app.png" alt="KPI Security Logo" className="object-contain w-full h-full" />
            </div>
            <h2 className="mt-6 text-center text-2xl font-black tracking-tight text-foreground uppercase">
              Sistem PMS Security
            </h2>
            <p className="mt-2 text-center text-xs text-muted-foreground">
              Silakan login untuk mengakses koordinasi & kinerja regu sekuriti
            </p>
          </div>

          <Card className="shadow-lg border-2">
            <CardHeader className="space-y-1">
              <CardTitle className="text-lg font-bold text-center">Masuk ke Akun Anda</CardTitle>
              <CardDescription className="text-center text-xs">
                Gunakan kredensial resmi dari administrator
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                {/* Username */}
                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-bold text-foreground uppercase tracking-wider">Username</label>
                  <input
                    type="text"
                    value={data.username}
                    onChange={(e) => setData("username", e.target.value)}
                    className="w-full p-2.5 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                    placeholder="Masukkan username..."
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
                    className="w-full p-2.5 border rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm font-semibold"
                    placeholder="Masukkan password..."
                    required
                  />
                  {errors.password && <span className="text-xs text-destructive">{errors.password}</span>}
                </div>

                {/* Submit */}
                <Button type="submit" disabled={processing} className="w-full bg-primary font-bold uppercase py-5 mt-2">
                  {processing ? "Memproses..." : "Masuk"}
                </Button>
              </form>
            </CardContent>
          </Card>

          <div className="text-center text-[10px] text-muted-foreground uppercase tracking-widest mt-4">
            Secured Area &bull; Authorized Personnel Only
          </div>
        </div>
      </div>
    </>
  );
}
