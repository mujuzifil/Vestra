"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { FolderOpen, Loader2, ArrowRight, FileText, Download } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useAccountDocuments } from "@/hooks/use-account-documents";
import { getAccountDocumentDownloadUrl } from "@/lib/api/account-documents";

const documentTypes = [
  "Quotations",
  "Certificates",
  "Product Catalogues",
  "Technical Sheets",
  "Safety Data Sheets",
];

function formatFileSize(bytes?: number | null): string {
  if (!bytes) return "Unknown size";
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function DocumentsPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data, isLoading: documentsLoading } = useAccountDocuments(1);
  const documents = data?.data ?? [];

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading || documentsLoading) {
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

            {documents.length === 0 ? (
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
            ) : (
              <div className="space-y-3">
                {documents.map((document) => (
                  <div
                    key={document.id}
                    className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-default bg-surface-page"
                  >
                    <div className="flex items-start gap-3 min-w-0">
                      <div className="p-2 rounded-lg bg-secondary-50 text-secondary-600">
                        <FileText className="w-5 h-5" />
                      </div>
                      <div className="min-w-0">
                        <p className="font-semibold text-text-heading truncate">{document.title}</p>
                        <p className="text-sm text-muted">
                          {document.type} • {formatFileSize(document.size)} •{" "}
                          {new Date(document.created_at).toLocaleDateString()}
                        </p>
                      </div>
                    </div>
                    {document.download_url && (
                      <a
                        href={getAccountDocumentDownloadUrl(document.id)}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-secondary-600 bg-secondary-50 rounded-xl hover:bg-secondary-100 transition-colors-base"
                      >
                        <Download className="w-4 h-4" />
                        Download
                      </a>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
