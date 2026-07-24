"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import { Search, Loader2, Plus, ArrowRight } from "lucide-react";
import { useDistributorQuotes } from "@/hooks/use-distributor-quotes";
import { EmptyState } from "@/components/common/empty-state";
import { QuoteStatusBadge } from "@/components/distributor/quote-status-badge";
import type { DistributorQuotation } from "@/types";

function QuoteCard({ quote }: { quote: DistributorQuotation }) {
  return (
    <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-5">
      <div className="flex items-start justify-between mb-3">
        <div>
          <p className="font-bold text-[#0a1628]">{quote.reference_number}</p>
          <p className="text-xs text-[#64748b]">{new Date(quote.created_at).toLocaleDateString()}</p>
        </div>
        <QuoteStatusBadge status={quote.status} />
      </div>
      <p className="text-sm text-[#64748b] mb-4 line-clamp-2">{quote.notes || "No notes provided."}</p>
      <div className="flex items-center justify-between">
        <span className="font-extrabold text-[#0a1628]">UGX {quote.total_amount}</span>
        <Link
          href={`/distributor/quotes/${quote.id}`}
          className="inline-flex items-center gap-1 text-sm font-semibold text-green-600 hover:text-green-700"
        >
          View
          <ArrowRight className="w-3 h-3" />
        </Link>
      </div>
    </div>
  );
}

export function QuotesPageClient() {
  const { data: quotes, isLoading } = useDistributorQuotes();
  const [search, setSearch] = useState("");

  const filtered = useMemo(() => {
    const term = search.toLowerCase().trim();
    if (!term) return quotes || [];
    return (quotes || []).filter(
      (q) => q.reference_number.toLowerCase().includes(term) || q.status.toLowerCase().includes(term)
    );
  }, [quotes, search]);

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-[#0a1628]">Quotes</h1>
          <p className="text-[#64748b]">Manage quotation requests and responses.</p>
        </div>
        <Link
          href="/distributor/quotes/new"
          className="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
        >
          <Plus className="w-4 h-4" />
          New Quote
        </Link>
      </div>

      <div className="relative max-w-sm">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
        <input
          type="text"
          placeholder="Search quotes..."
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
        <EmptyState title="No quotes found" description="Create your first quotation request." />
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {filtered.map((quote) => (
            <QuoteCard key={quote.id} quote={quote} />
          ))}
        </div>
      )}
    </div>
  );
}
