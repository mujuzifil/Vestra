"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Eye, EyeOff, Loader2, UserPlus } from "lucide-react";
import { Container } from "@/components/common/container";
import { useAuth } from "@/lib/auth-context";
import { cn } from "@/lib/utils";

export function RegisterPageClient() {
  const router = useRouter();
  const { register } = useAuth();
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError("");
    setFieldErrors({});
    setLoading(true);

    const formData = new FormData(e.currentTarget);
    const name = formData.get("name")?.toString() || "";
    const email = formData.get("email")?.toString() || "";
    const phone = formData.get("phone")?.toString() || "";
    const password = formData.get("password")?.toString() || "";
    const passwordConfirm = formData.get("password_confirmation")?.toString() || "";

    if (password !== passwordConfirm) {
      setFieldErrors({ password_confirmation: "Passwords do not match." });
      setLoading(false);
      return;
    }

    try {
      await register(name, email, password, phone || undefined);
      router.push("/account");
    } catch (err) {
      if (err instanceof Error && "errors" in err) {
        const apiErr = err as Error & { errors?: Record<string, string[]> };
        if (apiErr.errors) {
          const mapped: Record<string, string> = {};
          Object.entries(apiErr.errors).forEach(([k, v]) => {
            mapped[k] = v[0];
          });
          setFieldErrors(mapped);
        } else {
          setError(apiErr.message || "Registration failed.");
        }
      } else {
        setError("An unexpected error occurred.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-[#f8fafc] py-12">
      <Container className="max-w-md w-full">
        <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-8 lg:p-10">
          <div className="text-center mb-8">
            <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-2">Create Account</h1>
            <p className="text-[#64748b]">Join VESTRA for a better shopping experience</p>
          </div>

          {error && (
            <div className="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm text-center">{error}</div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5" noValidate>
            <div>
              <label htmlFor="name" className="block text-sm font-semibold text-[#0a1628] mb-1.5">Full Name</label>
              <input id="name" name="name" type="text" required
                className={cn("w-full px-4 py-3 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all", fieldErrors.name ? "border-red-300" : "border-[#e2e8f0]")}
                placeholder="John Doe" />
              {fieldErrors.name && <p className="mt-1 text-xs text-red-500">{fieldErrors.name}</p>}
            </div>

            <div>
              <label htmlFor="email" className="block text-sm font-semibold text-[#0a1628] mb-1.5">Email</label>
              <input id="email" name="email" type="email" required
                className={cn("w-full px-4 py-3 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all", fieldErrors.email ? "border-red-300" : "border-[#e2e8f0]")}
                placeholder="you@example.com" />
              {fieldErrors.email && <p className="mt-1 text-xs text-red-500">{fieldErrors.email}</p>}
            </div>

            <div>
              <label htmlFor="phone" className="block text-sm font-semibold text-[#0a1628] mb-1.5">Phone (optional)</label>
              <input id="phone" name="phone" type="tel"
                className="w-full px-4 py-3 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                placeholder="+256 707 128 442" />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-semibold text-[#0a1628] mb-1.5">Password</label>
              <div className="relative">
                <input id="password" name="password" type={showPassword ? "text" : "password"} required
                  className={cn("w-full px-4 py-3 pr-12 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all", fieldErrors.password ? "border-red-300" : "border-[#e2e8f0]")}
                  placeholder="Min. 8 characters" />
                <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3 top-1/2 -translate-y-1/2 text-[#94a3b8]">
                  {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                </button>
              </div>
              {fieldErrors.password && <p className="mt-1 text-xs text-red-500">{fieldErrors.password}</p>}
            </div>

            <div>
              <label htmlFor="password_confirmation" className="block text-sm font-semibold text-[#0a1628] mb-1.5">Confirm Password</label>
              <input id="password_confirmation" name="password_confirmation" type={showPassword ? "text" : "password"} required
                className={cn("w-full px-4 py-3 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all", fieldErrors.password_confirmation ? "border-red-300" : "border-[#e2e8f0]")}
                placeholder="Confirm your password" />
              {fieldErrors.password_confirmation && <p className="mt-1 text-xs text-red-500">{fieldErrors.password_confirmation}</p>}
            </div>

            <button type="submit" disabled={loading}
              className={cn("w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all", loading && "opacity-70 cursor-not-allowed")}>
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <UserPlus className="w-4 h-4" />}
              {loading ? "Creating account..." : "Create Account"}
            </button>
          </form>

          <div className="mt-6 text-center text-sm text-[#64748b]">
            Already have an account?{" "}
            <Link href="/auth/login" className="font-semibold text-green-600 hover:text-green-700">Sign in</Link>
          </div>
        </div>
      </Container>
    </div>
  );
}
