"use client";

import { useEffect, useState } from "react";
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
    ...products.map((product) => ({
      value: String(product.id),
      label: product.name,
    })),
  ];

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
      {items.map((item, index) => (
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
              onChange={(e) => updateItem(index, { product_name: e.target.value })}
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
        </div>
      ))}

      <Button
        type="button"
        variant="outline"
        onClick={addItem}
        className="inline-flex items-center gap-2"
      >
        <Plus className="w-4 h-4" />
        Add Another Product
      </Button>

      {error && <p className="text-sm text-danger-500">{error}</p>}
    </div>
  );
}
