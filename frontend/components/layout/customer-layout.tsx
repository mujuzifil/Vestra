"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  LayoutDashboard,
  User,
  Camera,
  Shield,
  Lock,
  Settings,
  MapPin,
  Bell,
  History,
  Clock,
  ShoppingBag,
  Star,
  Heart,
  LogOut,
  Menu,
  X,
  ChevronRight,
} from "lucide-react";
import { useAuth } from "@/lib/auth-context";
import { cn } from "@/lib/utils";

interface NavItem {
  label: string;
  href: string;
  icon: React.ElementType;
}

const navItems: NavItem[] = [
  { label: "Dashboard", href: "/account", icon: LayoutDashboard },
  { label: "Orders", href: "/account/orders", icon: ShoppingBag },
  { label: "Reviews", href: "/account/reviews", icon: Star },
  { label: "Wishlist", href: "/account/wishlist", icon: Heart },
  { label: "Recently Viewed", href: "/account/recently-viewed", icon: Clock },
  { label: "Profile", href: "/account/profile", icon: User },
  { label: "Photo", href: "/account/profile/photo", icon: Camera },
  { label: "Security", href: "/account/security", icon: Shield },
  { label: "Password", href: "/account/password", icon: Lock },
  { label: "Preferences", href: "/account/preferences", icon: Settings },
  { label: "Addresses", href: "/account/addresses", icon: MapPin },
  { label: "Activity", href: "/account/activity", icon: History },
  { label: "Notifications", href: "/notifications", icon: Bell },
];

function CustomerSidebar() {
  const pathname = usePathname();
  const { logout } = useAuth();
  const [mobileOpen, setMobileOpen] = useState(false);

  const NavContent = ({ onNavigate }: { onNavigate?: () => void }) => (
    <nav className="flex flex-col h-full">
      <div className="flex items-center justify-between p-4 lg:p-6 border-b border-border-default">
        <Link href="/account" className="flex items-center gap-2" onClick={onNavigate}>
          <span className="text-xl font-extrabold text-primary-900">VESTRA</span>
          <span className="px-2 py-0.5 text-xs font-semibold text-white bg-primary-500 rounded-full">Account</span>
        </Link>
        <button
          type="button"
          onClick={() => setMobileOpen(false)}
          className="lg:hidden p-2 text-text-muted hover:text-primary-900"
          aria-label="Close menu"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      <div className="flex-1 overflow-y-auto p-3 lg:p-4 space-y-1">
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
                  ? "bg-primary-50 text-primary-600"
                  : "text-neutral-600 hover:bg-neutral-50 hover:text-primary-900"
              )}
              aria-current={isActive ? "page" : undefined}
            >
              <item.icon className="w-5 h-5 flex-shrink-0" />
              <span>{item.label}</span>
              {isActive && <ChevronRight className="w-4 h-4 ml-auto flex-shrink-0" />}
            </Link>
          );
        })}
      </div>

      <div className="p-3 lg:p-4 border-t border-border-default space-y-1">
        <Link
          href="/distributor"
          className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-neutral-600 hover:bg-neutral-50 hover:text-primary-900 transition-colors-base"
        >
          <LayoutDashboard className="w-5 h-5" />
          <span>Distributor Portal</span>
        </Link>
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
      <div className="lg:hidden fixed top-4 left-4 z-40">
        <button
          type="button"
          onClick={() => setMobileOpen(true)}
          className="p-2.5 bg-surface-card border border-border-default rounded-xl shadow-sm text-primary-900"
          aria-label="Open menu"
        >
          <Menu className="w-5 h-5" />
        </button>
      </div>

      <aside className="hidden lg:flex flex-col w-64 h-[calc(100vh-88px)] sticky top-[88px] bg-surface-card border-r border-border-default">
        <NavContent />
      </aside>

      {mobileOpen && (
        <div className="lg:hidden fixed inset-0 z-50 flex">
          <div
            className="absolute inset-0 bg-black/40"
            onClick={() => setMobileOpen(false)}
            aria-hidden="true"
          />
          <div className="relative w-[280px] max-w-[80vw] bg-surface-card h-full shadow-xl animate-slide-right">
            <NavContent onNavigate={() => setMobileOpen(false)} />
          </div>
        </div>
      )}
    </>
  );
}

export function CustomerLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-surface-page">
      <div className="flex">
        <CustomerSidebar />
        <main className="flex-1 min-w-0 pt-16 lg:pt-6 p-4 sm:p-6 lg:p-8">
          {children}
        </main>
      </div>
    </div>
  );
}
