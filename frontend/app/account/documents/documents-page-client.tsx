"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { FolderOpen, Loader2, ArrowRight, FileText } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";

const documentTypes = [
  "Quotations",
  "Certificates",
  "Product Catalogues",
  "Technical Sheets",
  "Safety Data Sheets",
];

export function DocumentsPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();

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

  if (!isAuthenticated) return null;

  return (
    <>
      <PageHero
        title="Documents"
        subtitle="Your quotations, certificates, and catalogues in one place"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Documents" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            <div className="flex flex-wrap gap-2 mb-8">
              {documentTypes.map((type) => (
                <span
                  key={type}
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-surface-page text-muted border border-default"
                >
                  <FileText className="w-3.5 h-3.5" />
                  {type}
                </span>
              ))}
            </div>

            <div className="py-16 text-center">
              <div className="w-16 h-16 rounded-full bg-surface-page flex items-center justify-center mx-auto mb-5 border border-default">
                <FolderOpen className="w-8 h-8 text-placeholder" />
              </div>
              <h3 className="text-lg font-bold text-text-heading mb-2">No documents available</h3>
              <p className="text-muted mb-6 max-w-md mx-auto">
                Your quotations, certificates, and catalogues will appear here once available.
              </p>
              <Link
                href="/products"
                className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
              >
                <ArrowRight className="w-4 h-4" />
                Browse Products
              </Link>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
