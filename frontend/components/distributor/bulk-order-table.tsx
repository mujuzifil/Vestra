"use client";

import { useState } from "react";
import { Minus, Plus, Trash2 } from "lucide-react";
import type { DistributorProduct } from "@/types";

export interface BulkOrderLine {
  product_id: number;
  product_name: string;
  product_sku: string;
  quantity: number;
  unit_price: string;
  line_total: string;
}

interface BulkOrderTableProps {
  products: DistributorProduct[];
  lines: BulkOrderLine[];
  onChange: (lines: BulkOrderLine[]) => void;
}

export function BulkOrderTable({ products, lines, onChange }: BulkOrderTableProps) {
  const [selectedProductId, setSelectedProductId] = useState<string>("");

  function addLine() {
    const product = products.find((p) => p.id.toString() === selectedProductId);
    if (!product) return;

    const existing = lines.find((l) => l.product_id === product.id);
    if (existing) {
      updateQuantity(existing.product_id, existing.quantity + 1);
      setSelectedProductId("");
      return;
    }

    const price = product.distributor_price || product.price;
    onChange([
      ...lines,
      {
        product_id: product.id,
        product_name: product.name,
        product_sku: product.sku,
        quantity: 1,
        unit_price: price,
        line_total: price,
      },
    ]);
    setSelectedProductId("");
  }

  function updateQuantity(productId: number, quantity: number) {
    if (quantity < 1) return;
    onChange(
      lines.map((line) => {
        if (line.product_id !== productId) return line;
        const total = (Number(line.unit_price) * quantity).toFixed(2);
        return { ...line, quantity, line_total: total };
      })
    );
  }

  function removeLine(productId: number) {
    onChange(lines.filter((line) => line.product_id !== productId));
  }

  const total = lines.reduce((sum, line) => sum + Number(line.line_total), 0);

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row gap-3">
        <select
          value={selectedProductId}
          onChange={(e) => setSelectedProductId(e.target.value)}
          className="flex-1 px-4 py-3 rounded-xl border border-border text-sm focus:outline-none focus:ring-2 focus:ring-secondary-500 bg-surface-card"
        >
          <option value="">Select a product</option>
          {products.map((product) => (
            <option key={product.id} value={product.id}>
              {product.name} ({product.sku}) — UGX {product.distributor_price || product.price}
            </option>
          ))}
        </select>
        <button
          type="button"
          onClick={addLine}
          disabled={!selectedProductId}
          className="inline-flex items-center justify-center gap-2 px-5 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600/90 disabled:opacity-60 transition-colors-base"
        >
          <Plus className="w-4 h-4" />
          Add Product
        </button>
      </div>

      {lines.length === 0 ? (
        <div className="text-center py-8 rounded-xl bg-surface-page border border-border text-muted text-sm">
          No products added yet.
        </div>
      ) : (
        <div className="overflow-x-auto rounded-[20px] border border-border">
          <table className="w-full text-left text-sm">
            <thead className="bg-surface-page">
              <tr>
                <th className="px-4 py-3 font-semibold text-text-heading">Product</th>
                <th className="px-4 py-3 font-semibold text-text-heading">SKU</th>
                <th className="px-4 py-3 font-semibold text-text-heading">Unit Price</th>
                <th className="px-4 py-3 font-semibold text-text-heading">Qty</th>
                <th className="px-4 py-3 font-semibold text-text-heading">Total</th>
                <th className="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody>
              {lines.map((line) => (
                <tr key={line.product_id} className="border-t border-neutral-100">
                  <td className="px-4 py-3 font-medium text-text-heading">{line.product_name}</td>
                  <td className="px-4 py-3 text-muted">{line.product_sku}</td>
                  <td className="px-4 py-3 text-muted">UGX {line.unit_price}</td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-2">
                      <button
                        type="button"
                        onClick={() => updateQuantity(line.product_id, line.quantity - 1)}
                        className="p-1 rounded-lg border border-border hover:bg-surface-page transition-colors-base"
                        aria-label="Decrease quantity"
                      >
                        <Minus className="w-3.5 h-3.5" />
                      </button>
                      <span className="w-8 text-center font-medium">{line.quantity}</span>
                      <button
                        type="button"
                        onClick={() => updateQuantity(line.product_id, line.quantity + 1)}
                        className="p-1 rounded-lg border border-border hover:bg-surface-page transition-colors-base"
                        aria-label="Increase quantity"
                      >
                        <Plus className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                  <td className="px-4 py-3 font-bold text-text-heading">UGX {line.line_total}</td>
                  <td className="px-4 py-3">
                    <button
                      type="button"
                      onClick={() => removeLine(line.product_id)}
                      className="p-1.5 text-text-muted hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors-base"
                      aria-label="Remove product"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
            <tfoot className="bg-surface-page">
              <tr>
                <td colSpan={4} className="px-4 py-3 text-right font-bold text-text-heading">
                  Total
                </td>
                <td className="px-4 py-3 font-extrabold text-secondary-600">UGX {total.toFixed(2)}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      )}
    </div>
  );
}
