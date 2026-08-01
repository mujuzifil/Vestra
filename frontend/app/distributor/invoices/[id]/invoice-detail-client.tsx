"use client";

import Link from "next/link";
import { ChevronLeft, Loader2, AlertCircle, Download, Receipt } from "lucide-react";
import { useDistributorInvoice } from "@/hooks/use-distributor-invoices";

interface Props {
  invoiceId: number;
}

export function InvoiceDetailPageClient({ invoiceId }: Props) {
  const { data: invoice, isLoading } = useDistributorInvoice(invoiceId);

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!invoice) {
    return (
      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-8 text-center">
        <AlertCircle className="w-10 h-10 mx-auto mb-3 text-placeholder" />
        <h2 className="text-lg font-bold text-text-heading">Invoice not found</h2>
        <Link href="/distributor/invoices" className="text-green-600 font-semibold hover:text-green-700">
          Back to Invoices
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <Link
        href="/distributor/invoices"
        className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-text-heading"
      >
        <ChevronLeft className="w-4 h-4" />
        Back to Invoices
      </Link>

      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-6 lg:p-10">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
          <div className="flex items-center gap-3">
            <div className="p-3 rounded-xl bg-green-600">
              <Receipt className="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-extrabold text-text-heading">{invoice.invoice_number}</h1>
              <p className="text-sm text-muted">Issued {new Date(invoice.created_at).toLocaleDateString()}</p>
            </div>
          </div>
          <a
            href={`${process.env.NEXT_PUBLIC_API_URL}/invoices/${invoice.id}/download`}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-900 text-white font-semibold rounded-xl hover:bg-primary-900 transition-colors-base"
          >
            <Download className="w-4 h-4" />
            Download PDF
          </a>
        </div>

        <div className="grid sm:grid-cols-3 gap-6 mb-8">
          <div className="p-4 rounded-xl bg-neutral-50">
            <p className="text-sm text-muted mb-1">Amount</p>
            <p className="text-xl font-extrabold text-text-heading">UGX {invoice.amount}</p>
          </div>
          <div className="p-4 rounded-xl bg-neutral-50">
            <p className="text-sm text-muted mb-1">Amount Paid</p>
            <p className="text-xl font-extrabold text-green-600">UGX {invoice.amount_paid}</p>
          </div>
          <div className="p-4 rounded-xl bg-neutral-50">
            <p className="text-sm text-muted mb-1">Balance Due</p>
            <p className="text-xl font-extrabold text-primary-500">UGX {invoice.balance_due}</p>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <span
            className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold capitalize ${
              invoice.status === "paid" ? "bg-green-100 text-green-700" : "bg-amber-100 text-amber-700"
            }`}
          >
            {invoice.status}
          </span>
          {invoice.due_date && <span className="text-sm text-muted">Due: {new Date(invoice.due_date).toLocaleDateString()}</span>}
        </div>
      </div>
    </div>
  );
}
