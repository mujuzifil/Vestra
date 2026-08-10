"use client";

import { useEffect, useMemo, useState } from "react";
import { Plus } from "lucide-react";
import { InputField, SelectField } from "@/components/common/form-field";
import { Icon } from "@/components/common/icon";
import { Button } from "@/components/ui/button";
import { getAllProducts } from "@/lib/api/products";
import type { Product, QuoteRequestItem } from "@/types";

interface QuoteItemsFieldProps {
  items: QuoteRequestItem[];
  onChange: (items: QuoteRequestItem[]) => void;
  error?: string;
}

function formatUgx(amount: number): string {
  return new Intl.NumberFormat("en-UG", {
    maximumFractionDigits: amount % 1 === 0 ? 0 : 2,
  }).format(amount);
}

function parsePrice(price: string | number | null | undefined): number {
  const numeric = typeof price === "number" ? price : Number(String(price ?? "").replace(/,/g, ""));
  return Number.isFinite(numeric) && numeric > 0 ? numeric : 0;
}

export function QuoteItemsField({ items, onChange, error }: QuoteItemsFieldProps) {
  const [products, setProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    getAllProducts()
      .then((data) => setProducts(data))
      .catch(() => setProducts([]))
      .finally(() => setIsLoading(false));
  }, []);

  const productOptions = [
    { value: "", label: isLoading ? "Loading products..." : "Select a product" },
    ...products.map((product) => {
      const price = parsePrice(product.price);
      return {
        value: String(product.id),
        label: price > 0 ? `${product.name} — UGX ${formatUgx(price)}` : product.name,
      };
    }),
  ];

  const pricedLines = useMemo(() => {
    return items.map((item) => {
      const product = item.product_id
        ? products.find((p) => p.id === item.product_id)
        : products.find((p) => p.name.toLowerCase() === item.product_name.trim().toLowerCase());
      const unitPrice = parsePrice(product?.price);
      const quantity = Number(item.quantity) > 0 ? Number(item.quantity) : 0;
      return {
        unitPrice,
        lineTotal: unitPrice * quantity,
        hasCatalogPrice: unitPrice > 0,
      };
    });
  }, [items, products]);

  const catalogSubtotal = pricedLines.reduce((sum, line) => sum + line.lineTotal, 0);
  const pricedItemCount = pricedLines.filter((line) => line.hasCatalogPrice).length;

  const updateItem = (index: number, updates: Partial<QuoteRequestItem>) => {
    const next = items.map((item, i) => (i === index ? { ...item, ...updates } : item));
    onChange(next);
  };

  const removeItem = (index: number) => {
    const next = items.filter((_, i) => i !== index);
    onChange(next);
  };

  const addItem = () => {
    onChange([
      ...items,
      {
        product_id: null,
        product_name: "",
        package_size: "",
        quantity: 1,
        notes: "",
      },
    ]);
  };

  const handleProductChange = (index: number, value: string) => {
    const product = products.find((p) => String(p.id) === value);
    updateItem(index, {
      product_id: product ? product.id : null,
      product_name: product ? product.name : value,
    });
  };

  return (
    <div className="space-y-4">
      {items.map((item, index) => {
        const line = pricedLines[index];
        return (
          <div
            key={index}
            className="p-4 rounded-xl border border-border-default bg-neutral-50 space-y-4"
          >
            <div className="flex items-center justify-between">
              <span className="text-sm font-semibold text-text-heading">Product {index + 1}</span>
              {items.length > 1 && (
                <button
                  type="button"
                  onClick={() => removeItem(index)}
                  className="inline-flex items-center gap-1 text-sm text-danger-600 hover:text-danger-700"
                >
                  <Icon name="Trash2" className="w-4 h-4" />
                  Remove
                </button>
              )}
            </div>
            <div className="grid sm:grid-cols-2 gap-4">
              <SelectField
                id={`product_id_${index}`}
                name={`product_id_${index}`}
                label="Product"
                options={productOptions}
                value={item.product_id ? String(item.product_id) : ""}
                onChange={(e) => handleProductChange(index, e.target.value)}
              />
              <InputField
                id={`product_name_${index}`}
                name={`product_name_${index}`}
                label="Product Name"
                placeholder="Or enter product name manually"
                value={item.product_name}
                onChange={(e) => updateItem(index, { product_name: e.target.value, product_id: null })}
              />
            </div>
            <div className="grid sm:grid-cols-3 gap-4">
              <InputField
                id={`package_size_${index}`}
                name={`package_size_${index}`}
                label="Package Size"
                placeholder="e.g. 5L"
                value={item.package_size ?? ""}
                onChange={(e) => updateItem(index, { package_size: e.target.value })}
              />
              <InputField
                id={`quantity_${index}`}
                name={`quantity_${index}`}
                type="number"
                min={1}
                label="Quantity"
                value={String(item.quantity)}
                onChange={(e) => updateItem(index, { quantity: Number(e.target.value) })}
              />
              <InputField
                id={`item_notes_${index}`}
                name={`item_notes_${index}`}
                label="Notes"
                placeholder="Variant, concentration..."
                value={item.notes ?? ""}
                onChange={(e) => updateItem(index, { notes: e.target.value })}
              />
            </div>

            <div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border-default bg-white px-4 py-3">
              <div className="text-sm text-text-muted">
                {line?.hasCatalogPrice ? (
                  <>
                    Unit price:{" "}
                    <span className="font-semibold text-text-heading">UGX {formatUgx(line.unitPrice)}</span>
                  </>
                ) : (
                  <span>Select a catalog product to show list price</span>
                )}
              </div>
              <div className="text-sm font-bold text-text-heading">
                Line total:{" "}
                {line?.hasCatalogPrice ? `UGX ${formatUgx(line.lineTotal)}` : "—"}
              </div>
            </div>
          </div>
        );
      })}

      <Button
        type="button"
        variant="outline"
        onClick={addItem}
        className="inline-flex items-center gap-2"
      >
        <Plus className="w-4 h-4" />
        Add Another Product
      </Button>

      <div className="rounded-xl border border-border-default bg-white px-4 py-4 sm:px-5">
        <div className="flex flex-wrap items-end justify-between gap-3">
          <div>
            <p className="text-xs font-semibold uppercase tracking-wider text-text-muted">Estimated total</p>
            <p className="mt-1 text-sm text-text-muted">
              Based on published list prices for {pricedItemCount} catalog product
              {pricedItemCount === 1 ? "" : "s"}. Final quote may vary.
            </p>
          </div>
          <p className="text-2xl font-extrabold tracking-tight text-text-heading">
            {catalogSubtotal > 0 ? `UGX ${formatUgx(catalogSubtotal)}` : "UGX —"}
          </p>
        </div>
      </div>

      {error && <p className="text-sm text-danger-500">{error}</p>}
    </div>
  );
}
