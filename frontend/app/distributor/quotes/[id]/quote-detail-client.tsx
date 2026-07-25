"use client";

import Link from "next/link";
import { ChevronLeft, Loader2, AlertCircle, Send, CheckCircle2, FileText } from "lucide-react";
import { useDistributorQuote, useDistributorQuotes } from "@/hooks/use-distributor-quotes";
import { QuoteStatusBadge } from "@/components/distributor/quote-status-badge";
import { toastSuccess, toastError } from "@/lib/toast-utils";

interface Props {
  quoteId: number;
}

export function QuoteDetailPageClient({ quoteId }: Props) {
  const { data: quote, isLoading } = useDistributorQuote(quoteId);
  const { submit, accept } = useDistributorQuotes();

  async function handleSubmit() {
    try {
      await submit(quoteId);
      toastSuccess("Quote submitted for review.");
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Submit failed.");
    }
  }

  async function handleAccept() {
    try {
      await accept(quoteId);
      toastSuccess("Quote accepted.");
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Accept failed.");
    }
  }

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!quote) {
    return (
      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-8 text-center">
        <AlertCircle className="w-10 h-10 mx-auto mb-3 text-placeholder" />
        <h2 className="text-lg font-bold text-primary-900">Quote not found</h2>
        <Link href="/distributor/quotes" className="text-green-600 font-semibold hover:text-green-700">
          Back to Quotes
        </Link>
      </div>
    );
  }

  const canSubmit = quote.status === "draft";
  const canAccept = quote.status === "quoted";

  return (
    <div className="space-y-6">
      <Link
        href="/distributor/quotes"
        className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-primary-900"
      >
        <ChevronLeft className="w-4 h-4" />
        Back to Quotes
      </Link>

      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-6 lg:p-8">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
          <div>
            <h1 className="text-2xl font-extrabold text-primary-900">{quote.reference_number}</h1>
            <p className="text-muted">Created {new Date(quote.created_at).toLocaleDateString()}</p>
          </div>
          <QuoteStatusBadge status={quote.status} />
        </div>

        {quote.notes && (
          <div className="mb-6 p-4 rounded-xl bg-neutral-50">
            <p className="text-sm text-muted">{quote.notes}</p>
          </div>
        )}

        <div className="overflow-x-auto rounded-[20px] border border-neutral-200 mb-6">
          <table className="w-full text-left text-sm">
            <thead className="bg-neutral-50">
              <tr>
                <th className="px-6 py-4 font-semibold text-primary-900">Product</th>
                <th className="px-6 py-4 font-semibold text-primary-900">SKU</th>
                <th className="px-6 py-4 font-semibold text-primary-900">Qty</th>
                <th className="px-6 py-4 font-semibold text-primary-900">Unit Price</th>
                <th className="px-6 py-4 font-semibold text-primary-900 text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              {quote.items.map((item) => (
                <tr key={item.id} className="border-t border-neutral-100">
                  <td className="px-6 py-4 font-medium text-primary-900">{item.product_name}</td>
                  <td className="px-6 py-4 text-muted">{item.product_sku}</td>
                  <td className="px-6 py-4 text-muted">{item.quantity}</td>
                  <td className="px-6 py-4 text-muted">UGX {item.unit_price}</td>
                  <td className="px-6 py-4 font-bold text-primary-900 text-right">UGX {item.line_total}</td>
                </tr>
              ))}
            </tbody>
            <tfoot className="bg-neutral-50">
              <tr>
                <td colSpan={4} className="px-6 py-4 text-right font-semibold text-primary-900">
                  Subtotal
                </td>
                <td className="px-6 py-4 text-right font-semibold text-primary-900">UGX {quote.subtotal}</td>
              </tr>
              <tr>
                <td colSpan={4} className="px-6 py-4 text-right font-semibold text-primary-900">
                  Tax
                </td>
                <td className="px-6 py-4 text-right font-semibold text-primary-900">UGX {quote.tax_amount}</td>
              </tr>
              <tr>
                <td colSpan={4} className="px-6 py-4 text-right font-extrabold text-primary-900">
                  Total
                </td>
                <td className="px-6 py-4 text-right font-extrabold text-green-600">UGX {quote.total_amount}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        {quote.admin_notes && (
          <div className="p-4 rounded-xl bg-blue-50 text-blue-800 text-sm mb-6">
            <p className="font-semibold mb-1">Admin Notes</p>
            <p>{quote.admin_notes}</p>
          </div>
        )}

        <div className="flex flex-wrap gap-3">
          {canSubmit && (
            <button
              type="button"
              onClick={handleSubmit}
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors-base"
            >
              <Send className="w-4 h-4" />
              Submit Quote
            </button>
          )}
          {canAccept && (
            <button
              type="button"
              onClick={handleAccept}
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors-base"
            >
              <CheckCircle2 className="w-4 h-4" />
              Accept Quote
            </button>
          )}
          <button
            type="button"
            onClick={() => window.print()}
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-neutral-200 text-primary-900 font-semibold rounded-xl hover:bg-neutral-50 transition-colors-base"
          >
            <FileText className="w-4 h-4" />
            Print
          </button>
        </div>
      </div>
    </div>
  );
}
