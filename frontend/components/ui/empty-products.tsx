import Link from "next/link";
import { Button } from "@/components/ui/button";
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
      <h3 className="text-xl font-bold text-text-heading mb-2">{title}</h3>
      <p className="text-text-muted max-w-md mb-6 leading-relaxed">{description}</p>
      <div className="flex flex-wrap justify-center gap-3">
        {onReset && (
          <Button variant="outline" className="rounded-full px-5 py-2.5 h-auto text-sm" leftIcon={<RotateCcw className="w-4 h-4" aria-hidden="true" />} onClick={onReset}>
            Reset Filters
          </Button>
        )}
        <Button asChild variant="default" className="rounded-full px-5 py-2.5 h-auto text-sm" leftIcon={<FileText className="w-4 h-4" aria-hidden="true" />}>
          <Link href="/request-quote">Request a Quote</Link>
        </Button>
      </div>
    </div>
  );
}
