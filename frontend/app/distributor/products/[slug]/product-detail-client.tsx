"use client";

import Link from "next/link";
import Image from "next/image";
import { Package, ChevronLeft, Loader2, AlertCircle, CheckCircle2 } from "lucide-react";
import { useDistributorProduct } from "@/hooks/use-distributor-products";

interface Props {
  slug: string;
}

export function ProductDetailPageClient({ slug }: Props) {
  const { data: product, isLoading } = useDistributorProduct(slug);

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!product) {
    return (
      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-8 text-center">
        <AlertCircle className="w-10 h-10 mx-auto mb-3 text-placeholder" />
        <h2 className="text-lg font-bold text-primary-900">Product not found</h2>
        <p className="text-sm text-muted mb-4">The product you are looking for does not exist.</p>
        <Link href="/distributor/products" className="text-green-600 font-semibold hover:text-green-700">
          Back to Products
        </Link>
      </div>
    );
  }

  const price = product.distributor_price || product.negotiated_price || product.tier_price || product.price;
  const image = product.images?.[0];

  return (
    <div className="space-y-6">
      <Link
        href="/distributor/products"
        className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-primary-900"
      >
        <ChevronLeft className="w-4 h-4" />
        Back to Products
      </Link>

      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm overflow-hidden">
        <div className="grid lg:grid-cols-2 gap-8 p-6 lg:p-10">
          <div className="relative aspect-square bg-neutral-50 rounded-[20px] overflow-hidden">
            {image ? (
              <Image src={image.image} alt={image.alt_text || product.name} fill className="object-cover" />
            ) : (
              <div className="absolute inset-0 flex items-center justify-center text-placeholder">
                <Package className="w-20 h-20" />
              </div>
            )}
          </div>

          <div className="space-y-6">
            <div>
              <p className="text-sm font-semibold text-green-600 uppercase tracking-wider mb-2">{product.category?.name}</p>
              <h1 className="text-2xl lg:text-3xl font-extrabold text-primary-900">{product.name}</h1>
              <p className="text-sm text-muted mt-2">SKU: {product.sku}</p>
            </div>

            <div className="flex items-baseline gap-3">
              <span className="text-3xl font-extrabold text-primary-900">UGX {price}</span>
              {product.distributor_price && product.distributor_price !== product.price && (
                <span className="text-lg text-placeholder line-through">UGX {product.price}</span>
              )}
            </div>

            {product.moq && (
              <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-sm font-semibold">
                <Package className="w-4 h-4" />
                Minimum order quantity: {product.moq}
              </div>
            )}

            <p className="text-body leading-relaxed">{product.description}</p>

            {product.features && product.features.length > 0 && (
              <div>
                <h3 className="font-bold text-primary-900 mb-3">Features</h3>
                <ul className="space-y-2">
                  {product.features.map((feature, i) => (
                    <li key={i} className="flex items-start gap-2 text-body">
                      <CheckCircle2 className="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" />
                      {feature}
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {product.specifications && Object.keys(product.specifications).length > 0 && (
              <div>
                <h3 className="font-bold text-primary-900 mb-3">Specifications</h3>
                <dl className="grid grid-cols-2 gap-3 text-sm">
                  {Object.entries(product.specifications).map(([key, value]) => (
                    <div key={key} className="p-3 rounded-xl bg-neutral-50">
                      <dt className="text-placeholder capitalize">{key}</dt>
                      <dd className="font-medium text-primary-900">{value}</dd>
                    </div>
                  ))}
                </dl>
              </div>
            )}

            <Link
              href="/distributor/quotes/new"
              className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors-base"
            >
              Request Quote
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
