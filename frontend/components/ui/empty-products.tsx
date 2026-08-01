import Link from "next/link";
import { PackageX, RotateCcw, FileText } from "lucide-react";

interface EmptyProductsProps {
  title?: string;
  description?: string;
  onReset?: () => void;
}

export function EmptyProducts({
  title = "No products found",
  description = "Try adjusting your search or filters to find what you need.",
  onReset,
}: EmptyProductsProps) {
  return (
    <div className="flex flex-col items-center justify-center py-16 px-4 text-center">
      <div className="w-16 h-16 rounded-full bg-primary-50 flex items-center justify-center mb-5">
        <PackageX className="w-8 h-8 text-primary-300" aria-hidden="true" />
      </div>
      <h3 className="text-xl font-bold text-primary-900 mb-2">{title}</h3>
      <p className="text-muted max-w-md mb-6 leading-relaxed">{description}</p>
      <div className="flex flex-wrap justify-center gap-3">
        {onReset && (
          <button
            type="button"
            onClick={onReset}
            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-semibold text-sm text-primary-900 bg-white border border-default hover:bg-surface-page transition-colors-base"
          >
            <RotateCcw className="w-4 h-4" aria-hidden="true" />
            Reset Filters
          </button>
        )}
        <Link
          href="/request-quote"
          className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-semibold text-sm text-white bg-secondary-600 hover:bg-secondary-600/90 transition-colors-base"
        >
          <FileText className="w-4 h-4" aria-hidden="true" />
          Request a Quote
        </Link>
      </div>
    </div>
  );
}
