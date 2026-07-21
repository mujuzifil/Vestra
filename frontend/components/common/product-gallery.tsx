"use client";

import Image from "next/image";
import { useState } from "react";
import { cn } from "@/lib/utils";

interface ProductGalleryProps {
  images: string[];
  productName: string;
  className?: string;
}

export function ProductGallery({ images, productName, className }: ProductGalleryProps) {
  const [selected, setSelected] = useState(0);
  const displayImages = images.length > 0 ? images : ["/assets/images/branding/vestra-logo.png"];

  return (
    <div className={cn("space-y-4", className)}>
      <div className="relative aspect-square rounded-[24px] bg-gradient-to-b from-[#f8fafc] to-white border border-[#e2e8f0] overflow-hidden p-6 lg:p-10">
        <div className="absolute inset-0 flex items-center justify-center">
          <div className="w-48 h-48 rounded-full bg-[radial-gradient(circle,rgba(13,59,102,0.05)_0%,transparent_70%)]" />
        </div>
        <Image
          src={displayImages[selected]}
          alt={productName}
          fill
          sizes="(max-width: 1024px) 100vw, 50vw"
          className="object-contain p-4 lg:p-8"
          priority
        />
      </div>

      {displayImages.length > 1 && (
        <div className="flex gap-3 overflow-x-auto pb-2">
          {displayImages.map((image, index) => (
            <button
              key={index}
              onClick={() => setSelected(index)}
              aria-label={`View ${productName} image ${index + 1}`}
              aria-pressed={selected === index}
              className={cn(
                "relative w-20 h-20 lg:w-24 lg:h-24 rounded-xl border-2 overflow-hidden flex-shrink-0 transition-all",
                selected === index
                  ? "border-green-500 shadow-md"
                  : "border-[#e2e8f0] hover:border-[#7db8ec]"
              )}
            >
              <Image
                src={image}
                alt={`${productName} view ${index + 1}`}
                fill
                sizes="96px"
                className="object-contain p-2"
              />
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
