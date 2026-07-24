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
          className="flex-1 px-4 py-3 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
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
          className="inline-flex items-center justify-center gap-2 px-5 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 disabled:opacity-60 transition-colors"
        >
          <Plus className="w-4 h-4" />
          Add Product
        </button>
      </div>

      {lines.length === 0 ? (
        <div className="text-center py-8 rounded-xl bg-[#f8fafc] border border-[#e2e8f0] text-[#64748b] text-sm">
          No products added yet.
        </div>
      ) : (
        <div className="overflow-x-auto rounded-[20px] border border-[#e2e8f0]">
          <table className="w-full text-left text-sm">
            <thead className="bg-[#f8fafc]">
              <tr>
                <th className="px-4 py-3 font-semibold text-[#0a1628]">Product</th>
                <th className="px-4 py-3 font-semibold text-[#0a1628]">SKU</th>
                <th className="px-4 py-3 font-semibold text-[#0a1628]">Unit Price</th>
                <th className="px-4 py-3 font-semibold text-[#0a1628]">Qty</th>
                <th className="px-4 py-3 font-semibold text-[#0a1628]">Total</th>
                <th className="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody>
              {lines.map((line) => (
                <tr key={line.product_id} className="border-t border-[#f1f5f9]">
                  <td className="px-4 py-3 font-medium text-[#0a1628]">{line.product_name}</td>
                  <td className="px-4 py-3 text-[#64748b]">{line.product_sku}</td>
                  <td className="px-4 py-3 text-[#64748b]">UGX {line.unit_price}</td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-2">
                      <button
                        type="button"
                        onClick={() => updateQuantity(line.product_id, line.quantity - 1)}
                        className="p-1 rounded-lg border border-[#e2e8f0] hover:bg-[#f8fafc]"
                        aria-label="Decrease quantity"
                      >
                        <Minus className="w-3.5 h-3.5" />
                      </button>
                      <span className="w-8 text-center font-medium">{line.quantity}</span>
                      <button
                        type="button"
                        onClick={() => updateQuantity(line.product_id, line.quantity + 1)}
                        className="p-1 rounded-lg border border-[#e2e8f0] hover:bg-[#f8fafc]"
                        aria-label="Increase quantity"
                      >
                        <Plus className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                  <td className="px-4 py-3 font-bold text-[#0a1628]">UGX {line.line_total}</td>
                  <td className="px-4 py-3">
                    <button
                      type="button"
                      onClick={() => removeLine(line.product_id)}
                      className="p-1.5 text-[#94a3b8] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                      aria-label="Remove product"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
            <tfoot className="bg-[#f8fafc]">
              <tr>
                <td colSpan={4} className="px-4 py-3 text-right font-bold text-[#0a1628]">
                  Total
                </td>
                <td className="px-4 py-3 font-extrabold text-green-600">UGX {total.toFixed(2)}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      )}
    </div>
  );
}
