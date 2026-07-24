import * as React from "react";
import { cn } from "@/lib/utils";
import { Search, X } from "lucide-react";

interface SearchBarProps
  extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "onChange"> {
  value: string;
  onChange: (value: string) => void;
  onClear?: () => void;
  placeholder?: string;
}

const SearchBar = React.forwardRef<HTMLInputElement, SearchBarProps>(
  (
    {
      className,
      value,
      onChange,
      onClear,
      placeholder = "Search...",
      ...props
    },
    ref
  ) => {
    return (
      <div
        className={cn(
          "relative flex items-center rounded-md border border-border-default bg-surface-card shadow-sm",
          "focus-within:border-border-focus focus-within:ring-1 focus-within:ring-border-focus",
          className
        )}
      >
        <Search className="ml-3 h-4 w-4 text-text-muted" aria-hidden="true" />
        <input
          ref={ref}
          type="text"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          placeholder={placeholder}
          className="h-10 w-full bg-transparent px-3 py-2 text-sm text-text-heading placeholder:text-text-placeholder focus:outline-none"
          {...props}
        />
        {value && (
          <button
            type="button"
            onClick={onClear}
            className="mr-2 rounded-md p-1 text-text-muted hover:bg-neutral-100 hover:text-text-heading transition-colors-base"
            aria-label="Clear search"
          >
            <X className="h-4 w-4" />
          </button>
        )}
      </div>
    );
  }
);
SearchBar.displayName = "SearchBar";

export { SearchBar };
