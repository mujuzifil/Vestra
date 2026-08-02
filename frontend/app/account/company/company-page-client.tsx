"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { User, Mail, Phone, Building2, Loader2, ArrowRight } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";

export function CompanyPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user) return null;

  return (
    <>
      <PageHero
        title="Company Information"
        subtitle="Your registered contact and company details"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Company" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="grid lg:grid-cols-2 gap-6">
            {/* Personal Contact Details */}
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-10 h-10 rounded-xl bg-secondary-50 flex items-center justify-center">
                  <User className="w-5 h-5 text-secondary-600" />
                </div>
                <h2 className="text-lg font-bold text-text-heading">Contact Details</h2>
              </div>

              <div className="space-y-4">
                <div className="flex items-start gap-3">
                  <User className="w-5 h-5 text-muted mt-0.5" />
                  <div>
                    <p className="text-sm text-muted">Full Name</p>
                    <p className="font-medium text-text-heading">{user.name}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <Mail className="w-5 h-5 text-muted mt-0.5" />
                  <div>
                    <p className="text-sm text-muted">Email</p>
                    <p className="font-medium text-text-heading">{user.email}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <Phone className="w-5 h-5 text-muted mt-0.5" />
                  <div>
                    <p className="text-sm text-muted">Phone</p>
                    <p className="font-medium text-text-heading">{user.phone || "Not provided"}</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Company Details */}
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-10 h-10 rounded-xl bg-secondary-50 flex items-center justify-center">
                  <Building2 className="w-5 h-5 text-secondary-600" />
                </div>
                <h2 className="text-lg font-bold text-text-heading">Company Details</h2>
              </div>

              <div className="py-10 text-center">
                <Building2 className="w-14 h-14 mx-auto mb-4 text-placeholder" />
                <h3 className="text-lg font-bold text-text-heading mb-2">Company profile not yet configured</h3>
                <p className="text-muted mb-6 max-w-sm mx-auto">
                  Contact sales to add or update your business information.
                </p>
                <Link
                  href="/contact"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
                >
                  <ArrowRight className="w-4 h-4" />
                  Contact Sales
                </Link>
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
