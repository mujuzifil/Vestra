"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import { Search, Loader2, ArrowRight, Download } from "lucide-react";
import { useDistributorInvoices } from "@/hooks/use-distributor-invoices";
import { EmptyState } from "@/components/common/empty-state";
import type { DistributorInvoice } from "@/types";

function InvoiceCard({ invoice }: { invoice: DistributorInvoice }) {
  return (
    <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-5">
      <div className="flex items-start justify-between mb-3">
        <div>
          <p className="font-bold text-[#0a1628]">{invoice.invoice_number}</p>
          <p className="text-xs text-[#64748b]">{new Date(invoice.created_at).toLocaleDateString()}</p>
        </div>
        <span
          className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
            invoice.status === "paid" ? "bg-green-100 text-green-700" : "bg-amber-100 text-amber-700"
          }`}
        >
          {invoice.status}
        </span>
      </div>
      <div className="space-y-1 text-sm text-[#64748b] mb-4">
        <p>Amount: <span className="font-medium text-[#0a1628]">UGX {invoice.amount}</span></p>
        <p>Balance Due: <span className="font-medium text-[#0a1628]">UGX {invoice.balance_due}</span></p>
      </div>
      <div className="flex items-center gap-2">
        <Link
          href={`/distributor/invoices/${invoice.id}`}
          className="inline-flex items-center gap-1 text-sm font-semibold text-green-600 hover:text-green-700"
        >
          View
          <ArrowRight className="w-3 h-3" />
        </Link>
        <a
          href={`${process.env.NEXT_PUBLIC_API_URL}/api/v1/invoices/${invoice.id}/download`}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center gap-1 text-sm font-semibold text-[#64748b] hover:text-[#0a1628]"
        >
          <Download className="w-3 h-3" />
          PDF
        </a>
      </div>
    </div>
  );
}

export function InvoicesPageClient() {
  const { data: invoices, isLoading } = useDistributorInvoices();
  const [search, setSearch] = useState("");

  const filtered = useMemo(() => {
    const term = search.toLowerCase().trim();
    if (!term) return invoices || [];
    return (invoices || []).filter((i) => i.invoice_number.toLowerCase().includes(term));
  }, [invoices, search]);

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-[#0a1628]">Invoices</h1>
        <p className="text-[#64748b]">View and download your distributor invoices.</p>
      </div>

      <div className="relative max-w-sm">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
        <input
          type="text"
          placeholder="Search invoices..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
        />
      </div>

      {isLoading ? (
        <div className="min-h-[50vh] flex items-center justify-center">
          <Loader2 className="w-8 h-8 animate-spin text-green-500" />
        </div>
      ) : filtered.length === 0 ? (
        <EmptyState title="No invoices found" description="Your invoices will appear here once orders are billed." />
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {filtered.map((invoice) => (
            <InvoiceCard key={invoice.id} invoice={invoice} />
          ))}
        </div>
      )}
    </div>
  );
}
