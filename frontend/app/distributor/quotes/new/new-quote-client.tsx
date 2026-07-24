"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2 } from "lucide-react";
import { TextareaField } from "@/components/common/form-field";
import { BulkOrderTable, type BulkOrderLine } from "@/components/distributor/bulk-order-table";
import { useDistributorProducts } from "@/hooks/use-distributor-products";
import { useDistributorQuotes } from "@/hooks/use-distributor-quotes";
import { toastSuccess, toastError } from "@/lib/toast-utils";

export function NewQuoteClient() {
  const router = useRouter();
  const { data: products, isLoading: productsLoading } = useDistributorProducts();
  const { create } = useDistributorQuotes();
  const [lines, setLines] = useState<BulkOrderLine[]>([]);
  const [notes, setNotes] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (lines.length === 0) {
      toastError("Please add at least one product.");
      return;
    }

    setIsSubmitting(true);
    try {
      const quote = await create({
        notes,
        items: lines.map((line) => ({
          product_id: line.product_id,
          quantity: line.quantity,
          unit_price: line.unit_price,
        })),
      });
      toastSuccess("Quote created.");
      router.push(`/distributor/quotes/${quote.id}`);
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Could not create quote.");
      setIsSubmitting(false);
    }
  }

  if (productsLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <Link
        href="/distributor/quotes"
        className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628]"
      >
        <ChevronLeft className="w-4 h-4" />
        Back to Quotes
      </Link>

      <form onSubmit={handleSubmit} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8 space-y-6">
        <div>
          <h1 className="text-2xl font-extrabold text-[#0a1628]">New Quote Request</h1>
          <p className="text-[#64748b]">Build your quote from distributor-priced products.</p>
        </div>

        <TextareaField
          id="notes"
          label="Notes"
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
          placeholder="Any special requirements or delivery instructions..."
          rows={3}
        />

        <div>
          <h3 className="text-lg font-bold text-[#0a1628] mb-4">Products</h3>
          {products && <BulkOrderTable products={products} lines={lines} onChange={setLines} />}
        </div>

        <div className="flex justify-end pt-4">
          <button
            type="submit"
            disabled={isSubmitting || lines.length === 0}
            className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 disabled:opacity-60 transition-colors"
          >
            {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
            {isSubmitting ? "Creating..." : "Create Quote Request"}
          </button>
        </div>
      </form>
    </div>
  );
}
