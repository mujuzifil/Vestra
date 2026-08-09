"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  LayoutDashboard,
  Building2,
  MapPin,
  Users,
  FileText,
  Package,
  ShoppingCart,
  FileSpreadsheet,
  Receipt,
  CreditCard,
  BarChart3,
  Settings,
  LogOut,
  Menu,
  X,
  ChevronRight,
  Bell,
} from "lucide-react";
import { useAuth } from "@/lib/auth-context";
import { useDistributorProfile } from "@/hooks/use-distributor-profile";
import { DistributorTierBadge } from "@/components/distributor/distributor-tier-badge";
import { cn } from "@/lib/utils";

interface NavItem {
  label: string;
  href: string;
  icon: React.ElementType;
}

const navItems: NavItem[] = [
  { label: "Dashboard", href: "/distributor/dashboard", icon: LayoutDashboard },
  { label: "Company", href: "/distributor/company", icon: Building2 },
  { label: "Branches", href: "/distributor/branches", icon: MapPin },
  { label: "Contacts", href: "/distributor/contacts", icon: Users },
  { label: "Documents", href: "/distributor/documents", icon: FileText },
  { label: "Products", href: "/distributor/products", icon: Package },
  { label: "Quotes", href: "/distributor/quotes", icon: FileSpreadsheet },
  { label: "Orders", href: "/distributor/orders", icon: ShoppingCart },
  { label: "Invoices", href: "/distributor/invoices", icon: Receipt },
  { label: "Statements", href: "/distributor/statements", icon: CreditCard },
  { label: "Payments", href: "/distributor/payments", icon: CreditCard },
  { label: "Analytics", href: "/distributor/analytics", icon: BarChart3 },
  { label: "Notifications", href: "/notifications", icon: Bell },
  { label: "Settings", href: "/distributor/settings", icon: Settings },
];

function resolveActiveLabel(pathname: string): string {
  const exact = navItems.find((item) => pathname === item.href);
  if (exact) return exact.label;
  const nested = navItems
    .filter((item) => pathname.startsWith(`${item.href}/`))
    .sort((a, b) => b.href.length - a.href.length)[0];
  return nested?.label || "Distributor";
}

export function DistributorSidebar({
  mobileOpen,
  setMobileOpen,
}: {
  mobileOpen: boolean;
  setMobileOpen: (open: boolean) => void;
}) {
  const pathname = usePathname();
  const { logout } = useAuth();
  const { data: profile } = useDistributorProfile();

  const NavContent = ({ onNavigate }: { onNavigate?: () => void }) => (
    <nav className="flex flex-col h-full min-h-0">
      <div className="flex items-center justify-between p-4 lg:p-6 border-b border-border-default gap-2">
        <Link href="/distributor/dashboard" className="flex min-w-0 flex-col gap-2" onClick={onNavigate}>
          <span className="flex items-center gap-2 min-w-0">
            <span className="text-xl font-extrabold text-text-heading">VESTRA</span>
            <span className="px-2 py-0.5 text-xs font-semibold text-white bg-secondary-600 rounded-full">Dist</span>
          </span>
          <DistributorTierBadge
            tier={profile?.tier}
            label={profile?.tier_label}
            className="w-fit"
          />
        </Link>
        <button
          type="button"
          onClick={() => setMobileOpen(false)}
          className="lg:hidden p-2 text-text-muted hover:text-text-heading flex-shrink-0"
          aria-label="Close menu"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      <div className="flex-1 overflow-y-auto overscroll-contain p-3 lg:p-4 space-y-1">
        {navItems.map((item) => {
          const isActive = pathname === item.href || pathname.startsWith(`${item.href}/`);
          return (
            <Link
              key={item.href}
              href={item.href}
              onClick={onNavigate}
              className={cn(
                "flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors-base",
                isActive
                  ? "bg-secondary-50 text-secondary-600"
                  : "text-text-body hover:bg-surface-page hover:text-text-heading"
              )}
              aria-current={isActive ? "page" : undefined}
            >
              <item.icon className="w-5 h-5 flex-shrink-0" />
              <span className="min-w-0 truncate">{item.label}</span>
              {isActive && <ChevronRight className="w-4 h-4 ml-auto flex-shrink-0" />}
            </Link>
          );
        })}
      </div>

      <div className="p-3 lg:p-4 border-t border-border-default">
        <button
          type="button"
          onClick={() => logout().then(() => (window.location.href = "/"))}
          className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-danger-600 hover:bg-danger-50 transition-colors-base"
        >
          <LogOut className="w-5 h-5" />
          <span>Sign Out</span>
        </button>
      </div>
    </nav>
  );

  return (
    <>
      <aside className="hidden lg:flex flex-col w-64 h-[calc(100vh-72px)] sticky top-[72px] bg-surface-card border-r border-border-default">
        <NavContent />
      </aside>

      {mobileOpen && (
        <div className="lg:hidden fixed inset-0 z-[60] flex">
          <div
            className="absolute inset-0 bg-black/40"
            onClick={() => setMobileOpen(false)}
            aria-hidden="true"
          />
          <div
            className="relative w-[min(300px,88vw)] max-w-full bg-surface-card h-full shadow-xl animate-slide-right flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Distributor navigation"
          >
            <NavContent onNavigate={() => setMobileOpen(false)} />
          </div>
        </div>
      )}
    </>
  );
}

export function DistributorMobileNavBar({
  activeLabel,
  onOpen,
  mobileOpen,
}: {
  activeLabel: string;
  onOpen: () => void;
  mobileOpen: boolean;
}) {
  return (
    <div className="lg:hidden fixed top-[72px] left-0 right-0 z-40 border-b border-border-default bg-surface-card/95 backdrop-blur-sm">
      <div className="flex items-center gap-3 px-4 py-3 min-w-0 max-w-full">
        <button
          type="button"
          onClick={onOpen}
          className="p-2.5 rounded-xl border border-border-default bg-white text-primary-900 shadow-sm flex-shrink-0"
          aria-label="Open distributor menu"
          aria-expanded={mobileOpen}
        >
          <Menu className="w-5 h-5" />
        </button>
        <div className="min-w-0 flex-1">
          <p className="text-xs font-medium text-text-muted">Distributor Portal</p>
          <p className="text-sm font-semibold text-text-heading truncate">{activeLabel}</p>
        </div>
        <button
          type="button"
          onClick={onOpen}
          className="text-sm font-semibold text-secondary-600 flex-shrink-0 px-2"
        >
          Menu
        </button>
      </div>
    </div>
  );
}

/** Hook-friendly active label for layout */
export function useDistributorActiveLabel() {
  const pathname = usePathname();
  return resolveActiveLabel(pathname);
}
