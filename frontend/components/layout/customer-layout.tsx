"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { usePathname } from "next/navigation";
import {
  LayoutDashboard,
  FileText,
  Handshake,
  Bookmark,
  FolderOpen,
  HeadphonesIcon,
  Building2,
  MapPin,
  User,
  Shield,
  SlidersHorizontal,
  History,
  Bell,
  LogOut,
  Menu,
  X,
  ChevronRight,
} from "lucide-react";
import { useAuth } from "@/lib/auth-context";
import { useDistributorApplicationStatus } from "@/hooks/use-distributor-application-status";
import { cn } from "@/lib/utils";

interface NavItem {
  label: string;
  href: string;
  icon: React.ElementType;
}

const baseNavItems: NavItem[] = [
  { label: "Dashboard", href: "/account", icon: LayoutDashboard },
  { label: "My Quotes", href: "/account/quotes", icon: FileText },
  { label: "Distributor Application", href: "/account/distributor", icon: Handshake },
  { label: "Saved Products", href: "/account/saved-products", icon: Bookmark },
  { label: "Documents", href: "/account/documents", icon: FolderOpen },
  { label: "Support", href: "/account/support", icon: HeadphonesIcon },
  { label: "Company Information", href: "/account/company", icon: Building2 },
  { label: "Addresses", href: "/account/addresses", icon: MapPin },
  { label: "Profile", href: "/account/profile", icon: User },
  { label: "Security", href: "/account/security", icon: Shield },
  { label: "Preferences", href: "/account/preferences", icon: SlidersHorizontal },
  { label: "Activity", href: "/account/activity", icon: History },
  { label: "Notifications", href: "/notifications", icon: Bell },
];

function resolveActiveLabel(pathname: string, items: NavItem[]): string {
  const exact = items.find((item) => pathname === item.href);
  if (exact) return exact.label;
  const nested = items
    .filter((item) => item.href !== "/account" && pathname.startsWith(`${item.href}/`))
    .sort((a, b) => b.href.length - a.href.length)[0];
  return nested?.label || "Account";
}

function CustomerSidebar({
  mobileOpen,
  setMobileOpen,
}: {
  mobileOpen: boolean;
  setMobileOpen: (open: boolean) => void;
}) {
  const pathname = usePathname();
  const { logout } = useAuth();
  const { data: application } = useDistributorApplicationStatus();

  const navItems = baseNavItems.map((item) =>
    item.href === "/account/distributor" && application?.status === "approved"
      ? { ...item, label: "Distributor" }
      : item
  );

  const NavContent = ({ onNavigate }: { onNavigate?: () => void }) => (
    <nav className="flex flex-col h-full min-h-0">
      <div className="flex items-center justify-between p-4 lg:p-6 border-b border-border-default gap-2">
        <Link href="/account" className="flex items-center gap-2 min-w-0" onClick={onNavigate}>
          <Image
            src="/assets/images/branding/vestra-logo.png"
            alt="VESTRA"
            width={120}
            height={48}
            sizes="120px"
            className="h-10 w-auto object-contain"
            priority
          />
          <span className="px-2 py-0.5 text-xs font-semibold text-white bg-primary-500 rounded-full whitespace-nowrap">
            Business Portal
          </span>
        </Link>
        <button
          type="button"
          onClick={() => setMobileOpen(false)}
          className="lg:hidden p-2 text-text-muted hover:text-primary-900 flex-shrink-0"
          aria-label="Close menu"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      <div className="flex-1 overflow-y-auto overscroll-contain p-3 lg:p-4 space-y-1">
        {navItems.map((item) => {
          const isActive =
            pathname === item.href ||
            (item.href !== "/account" && pathname.startsWith(`${item.href}/`));
          return (
            <Link
              key={item.href}
              href={item.href}
              onClick={onNavigate}
              className={cn(
                "flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors-base",
                isActive
                  ? "bg-primary-50 text-primary-600"
                  : "text-neutral-600 hover:bg-neutral-50 hover:text-primary-900"
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
            aria-label="Account navigation"
          >
            <NavContent onNavigate={() => setMobileOpen(false)} />
          </div>
        </div>
      )}
    </>
  );
}

export function CustomerLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const { data: application } = useDistributorApplicationStatus();
  const [mobileOpen, setMobileOpen] = useState(false);

  const navItems = baseNavItems.map((item) =>
    item.href === "/account/distributor" && application?.status === "approved"
      ? { ...item, label: "Distributor" }
      : item
  );
  const activeLabel = resolveActiveLabel(pathname, navItems);

  useEffect(() => {
    setMobileOpen(false);
  }, [pathname]);

  useEffect(() => {
    if (!mobileOpen) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = previousOverflow;
    };
  }, [mobileOpen]);

  return (
    <div className="min-h-screen w-full max-w-full min-w-0 overflow-x-clip bg-surface-page">
      {/* Fixed below site header so the menu is never hidden behind the navbar */}
      <div className="lg:hidden fixed top-[72px] left-0 right-0 z-40 border-b border-border-default bg-surface-card/95 backdrop-blur-sm">
        <div className="flex items-center gap-3 px-4 py-3 min-w-0 max-w-full">
          <button
            type="button"
            onClick={() => setMobileOpen(true)}
            className="p-2.5 rounded-xl border border-border-default bg-white text-primary-900 shadow-sm flex-shrink-0"
            aria-label="Open account menu"
            aria-expanded={mobileOpen}
          >
            <Menu className="w-5 h-5" />
          </button>
          <div className="min-w-0 flex-1">
            <p className="text-xs font-medium text-text-muted">Business Portal</p>
            <p className="text-sm font-semibold text-text-heading truncate">{activeLabel}</p>
          </div>
          <button
            type="button"
            onClick={() => setMobileOpen(true)}
            className="text-sm font-semibold text-primary-600 flex-shrink-0 px-2"
          >
            Menu
          </button>
        </div>
      </div>

      <div className="flex w-full min-w-0">
        <CustomerSidebar mobileOpen={mobileOpen} setMobileOpen={setMobileOpen} />
        <main className="flex-1 min-w-0 max-w-full overflow-x-clip pt-[calc(72px+56px)] lg:pt-[72px]">
          {children}
        </main>
      </div>
    </div>
  );
}
