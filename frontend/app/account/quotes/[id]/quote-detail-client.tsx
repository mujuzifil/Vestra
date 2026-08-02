"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, AlertCircle, Loader2, FileText, Download } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useAccountQuote } from "@/hooks/use-account-quote";
import { Badge } from "@/components/ui/badge";

function statusVariant(status: string): "default" | "secondary" | "outline" | "danger" {
  switch (status) {
    case "approved":
    case "quoted":
      return "default";
    case "pending":
      return "secondary";
    case "rejected":
      return "danger";
    default:
      return "outline";
  }
}

interface Props {
  id: number;
}

export function QuoteDetailPageClient({ id }: Props) {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: quote, isLoading: quoteLoading, error } = useAccountQuote(id);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading || quoteLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  if (error || !quote) {
    return (
      <>
        <PageHero
          title="Quote Request Details"
          breadcrumb={[
            { label: "Account", href: "/account" },
            { label: "My Quotes", href: "/account/quotes" },
            { label: `#${id}` },
          ]}
        />

        <section className="py-12 lg:py-20 bg-surface-page">
          <Container>
            <Link
              href="/account/quotes"
              className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-text-heading mb-6"
            >
              <ChevronLeft className="w-4 h-4" />
              Back to Quotes
            </Link>

            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm py-16 text-center">
              <AlertCircle className="w-14 h-14 mx-auto mb-4 text-placeholder" />
              <h2 className="text-lg font-bold text-text-heading mb-2">Quote not found</h2>
              <p className="text-muted mb-6 max-w-md mx-auto">
                The quote request you are looking for does not exist or you do not have access to it.
              </p>
              <Link
                href="/account/quotes"
                className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
              >
                <ChevronLeft className="w-4 h-4" />
                Back to Quotes
              </Link>
            </div>
          </Container>
        </section>
      </>
    );
  }

  return (
    <>
      <PageHero
        title={`Quote ${quote.reference_number}`}
        breadcrumb={[
          { label: "Account", href: "/account" },
          { label: "My Quotes", href: "/account/quotes" },
          { label: quote.reference_number },
        ]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account/quotes"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-text-heading mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Quotes
          </Link>

          <div className="grid lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 space-y-6">
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                  <div>
                    <h2 className="text-xl font-bold text-text-heading">{quote.reference_number}</h2>
                    <p className="text-sm text-muted mt-1">
                      Submitted on {new Date(quote.created_at).toLocaleDateString()}
                    </p>
                  </div>
                  <Badge variant={statusVariant(quote.status)} className="w-fit">
                    {quote.status_label}
                  </Badge>
                </div>

                <div className="space-y-6">
                  <div>
                    <h3 className="text-sm font-semibold text-text-heading mb-3">Products Requested</h3>
                    {quote.items.length === 0 ? (
                      <p className="text-sm text-muted">No products listed.</p>
                    ) : (
                      <div className="space-y-3">
                        {quote.items.map((item) => (
                          <div
                            key={item.id}
                            className="flex items-start justify-between gap-4 p-4 rounded-xl bg-surface-page border border-default"
                          >
                            <div>
                              <p className="font-medium text-text-heading">{item.product_name}</p>
                              {item.package_size && (
                                <p className="text-sm text-muted">Package: {item.package_size}</p>
                              )}
                              {item.notes && <p className="text-sm text-muted mt-1">{item.notes}</p>}
                            </div>
                            <span className="font-semibold text-text-heading whitespace-nowrap">
                              Qty: {item.quantity}
                            </span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>

                  {quote.requirements && (
                    <div>
                      <h3 className="text-sm font-semibold text-text-heading mb-2">Requirements</h3>
                      <p className="text-sm text-muted whitespace-pre-line">{quote.requirements}</p>
                    </div>
                  )}

                  {quote.attachments && quote.attachments.length > 0 && (
                    <div>
                      <h3 className="text-sm font-semibold text-text-heading mb-3">Attachments</h3>
                      <div className="flex flex-wrap gap-2">
                        {quote.attachments.map((url, index) => (
                          <a
                            key={index}
                            href={url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-secondary-700 bg-secondary-50 rounded-xl hover:bg-secondary-100"
                          >
                            <Download className="w-4 h-4" />
                            Attachment {index + 1}
                          </a>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>

            <div className="space-y-6">
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <h3 className="text-lg font-bold text-text-heading mb-4">Quote Summary</h3>
                <div className="space-y-3 text-sm">
                  <div className="flex justify-between">
                    <span className="text-muted">Status</span>
                    <span className="font-medium text-text-heading">{quote.status_label}</span>
                  </div>
                  {quote.priority && (
                    <div className="flex justify-between">
                      <span className="text-muted">Priority</span>
                      <span className="font-medium text-text-heading capitalize">{quote.priority}</span>
                    </div>
                  )}
                  {quote.estimated_value && (
                    <div className="flex justify-between">
                      <span className="text-muted">Estimated Value</span>
                      <span className="font-medium text-text-heading">{quote.estimated_value}</span>
                    </div>
                  )}
                  {quote.sales_representative && (
                    <div className="pt-3 border-t border-default">
                      <span className="text-muted">Assigned Representative</span>
                      <p className="font-medium text-text-heading mt-1">{quote.sales_representative.name}</p>
                      <p className="text-muted">{quote.sales_representative.email}</p>
                    </div>
                  )}
                </div>
              </div>

              <div className="bg-secondary-50 border border-secondary-100 rounded-[20px] p-6">
                <div className="flex items-start gap-3">
                  <FileText className="w-5 h-5 text-secondary-600 mt-0.5" />
                  <div>
                    <p className="font-semibold text-text-heading">Need an update?</p>
                    <p className="text-sm text-muted mt-1">
                      Contact our sales team for the latest status on your quote request.
                    </p>
                    <Link
                      href="/contact"
                      className="inline-flex items-center gap-1.5 text-sm font-semibold text-secondary-600 hover:text-secondary-700 mt-3"
                    >
                      Contact Sales
                      <ChevronLeft className="w-3.5 h-3.5 rotate-180" />
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
