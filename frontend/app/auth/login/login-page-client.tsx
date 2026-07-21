"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Eye, EyeOff, Loader2, LogIn } from "lucide-react";
import { Container } from "@/components/common/container";
import { useAuth } from "@/lib/auth-context";
import { cn } from "@/lib/utils";

function getExchangeEndpointUrl(): string {
  const backendUrl = process.env.NEXT_PUBLIC_BACKEND_URL?.replace(/\/+$/g, "") ?? "http://localhost:8000";
  return `${backendUrl}/api/v1/auth/exchange`;
}

function submitExchangeToken(exchangeToken: string): void {
  // eslint-disable-next-line no-console
  console.log("[VESTRA] Creating exchange form", { action: getExchangeEndpointUrl() });

  const form = document.createElement("form");
  form.method = "POST";
  form.action = getExchangeEndpointUrl();
  form.style.display = "none";

  const input = document.createElement("input");
  input.type = "hidden";
  input.name = "exchange_token";
  input.value = exchangeToken;

  form.appendChild(input);
  document.body.appendChild(form);

  // eslint-disable-next-line no-console
  console.log("[VESTRA] Appended exchange form, scheduling submit");

  // Defer submission to the next tick so the form is guaranteed to be in the
  // DOM before the browser begins navigation. This prevents React state updates
  // or event-handler cleanup from interfering with the submission.
  setTimeout(() => {
    // eslint-disable-next-line no-console
    console.log("[VESTRA] Submitting exchange form");
    form.submit();
    // eslint-disable-next-line no-console
    console.log("[VESTRA] Exchange form submit called");
  }, 0);
}

export function LoginPageClient() {
  const router = useRouter();
  const { login } = useAuth();
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
    const email = formData.get("email")?.toString() || "";
    const password = formData.get("password")?.toString() || "";

    try {
      const result = await login(email, password);

      // eslint-disable-next-line no-console
      console.group("[VESTRA] Login response");
      // eslint-disable-next-line no-console
      console.log("result", result);
      // eslint-disable-next-line no-console
      console.log("role", result.role);
      // eslint-disable-next-line no-console
      console.log("redirect_to", result.redirect_to);
      // eslint-disable-next-line no-console
      console.log("exchange_token", result.exchange_token);
      // eslint-disable-next-line no-console
      console.log("user.is_admin", result.user?.is_admin);
      // eslint-disable-next-line no-console
      console.log("must_change_password", result.must_change_password);
      // eslint-disable-next-line no-console
      console.groupEnd();

      // Route administrators through the exchange-token bridge into Filament.
      // We use the backend role / is_admin flag as the primary signal so the
      // decision does not depend on an exact redirect string match.
      const isAdministrator = result.user.is_admin || result.role !== "customer";
      // eslint-disable-next-line no-console
      console.log("[VESTRA] isAdministrator", isAdministrator);

      if (isAdministrator) {
        if (!result.exchange_token) {
          throw new Error("Admin exchange token missing. Please try again.");
        }

        // eslint-disable-next-line no-console
        console.log("[VESTRA] Entering administrator exchange flow");

        // Submit a hidden form to the backend exchange endpoint. The server
        // validates the one-time token, creates a Filament web session, and
        // issues an HTTP redirect that the browser follows naturally. No
        // authentication credential appears in the URL.
        submitExchangeToken(result.exchange_token);
        return;
      }

      // eslint-disable-next-line no-console
      console.warn("[VESTRA] Routing customer to", result.redirect_to || "/account");
      router.push(result.redirect_to || "/account");
    } catch (err) {
      if (err instanceof Error && "errors" in err) {
        const apiErr = err as Error & { errors?: Record<string, string[]>; status?: number };
        if (apiErr.errors) {
          const mapped: Record<string, string> = {};
          Object.entries(apiErr.errors).forEach(([k, v]) => {
            mapped[k] = v[0];
          });
          setFieldErrors(mapped);
        } else {
          setError(apiErr.message || "Login failed. Please check your credentials.");
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
            <h1 className="text-2xl lg:text-3xl font-extrabold text-[#0a1628] mb-2">Welcome Back</h1>
            <p className="text-[#64748b]">Sign in to your VESTRA account</p>
          </div>

          {error && (
            <div className="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm text-center">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5" noValidate>
            <div>
              <label htmlFor="email" className="block text-sm font-semibold text-[#0a1628] mb-1.5">
                Email Address
              </label>
              <input
                id="email"
                name="email"
                type="email"
                required
                className={cn(
                  "w-full px-4 py-3 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all",
                  fieldErrors.email ? "border-red-300" : "border-[#e2e8f0]"
                )}
                placeholder="you@example.com"
              />
              {fieldErrors.email && <p className="mt-1 text-xs text-red-500">{fieldErrors.email}</p>}
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-semibold text-[#0a1628] mb-1.5">
                Password
              </label>
              <div className="relative">
                <input
                  id="password"
                  name="password"
                  type={showPassword ? "text" : "password"}
                  required
                  className={cn(
                    "w-full px-4 py-3 pr-12 rounded-xl border bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all",
                    fieldErrors.password ? "border-red-300" : "border-[#e2e8f0]"
                  )}
                  placeholder="Enter your password"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-[#94a3b8] hover:text-[#64748b]"
                >
                  {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                </button>
              </div>
              {fieldErrors.password && <p className="mt-1 text-xs text-red-500">{fieldErrors.password}</p>}
            </div>

            <button
              type="submit"
              disabled={loading}
              className={cn(
                "w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all",
                loading && "opacity-70 cursor-not-allowed"
              )}
            >
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <LogIn className="w-4 h-4" />}
              {loading ? "Signing in..." : "Sign In"}
            </button>
          </form>

          <div className="mt-6 text-center text-sm text-[#64748b]">
            Don&apos;t have an account?{" "}
            <Link href="/auth/register" className="font-semibold text-green-600 hover:text-green-700">
              Create one
            </Link>
          </div>
        </div>
      </Container>
    </div>
  );
}
