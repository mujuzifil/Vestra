"use client";

import { useState } from "react";
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
  { label: "Settings", href: "/distributor/settings", icon: Settings },
];

export function DistributorSidebar() {
  const pathname = usePathname();
  const { logout } = useAuth();
  const [mobileOpen, setMobileOpen] = useState(false);

  const NavContent = ({ onNavigate }: { onNavigate?: () => void }) => (
    <nav className="flex flex-col h-full">
      <div className="flex items-center justify-between p-4 lg:p-6 border-b border-[#e2e8f0]">
        <Link href="/distributor/dashboard" className="flex items-center gap-2" onClick={onNavigate}>
          <span className="text-xl font-extrabold text-[#0a1628]">VESTRA</span>
          <span className="px-2 py-0.5 text-xs font-semibold text-white bg-green-600 rounded-full">Dist</span>
        </Link>
        <button
          type="button"
          onClick={() => setMobileOpen(false)}
          className="lg:hidden p-2 text-[#64748b] hover:text-[#0a1628]"
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
                "flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors",
                isActive
                  ? "bg-green-50 text-green-700"
                  : "text-[#475569] hover:bg-[#f8fafc] hover:text-[#0a1628]"
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

      <div className="p-3 lg:p-4 border-t border-[#e2e8f0] space-y-1">
        <Link
          href="/account"
          className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#475569] hover:bg-[#f8fafc] hover:text-[#0a1628] transition-colors"
        >
          <Bell className="w-5 h-5" />
          <span>Customer Portal</span>
        </Link>
        <button
          type="button"
          onClick={() => logout().then(() => (window.location.href = "/"))}
          className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
        >
          <LogOut className="w-5 h-5" />
          <span>Sign Out</span>
        </button>
      </div>
    </nav>
  );

  return (
    <>
      {/* Mobile toggle */}
      <div className="lg:hidden fixed top-4 left-4 z-40">
        <button
          type="button"
          onClick={() => setMobileOpen(true)}
          className="p-2.5 bg-white border border-[#e2e8f0] rounded-xl shadow-sm text-[#0a1628]"
          aria-label="Open menu"
        >
          <Menu className="w-5 h-5" />
        </button>
      </div>

      {/* Desktop sidebar */}
      <aside className="hidden lg:flex flex-col w-64 h-[calc(100vh-88px)] sticky top-[88px] bg-white border-r border-[#e2e8f0]">
        <NavContent />
      </aside>

      {/* Mobile drawer */}
      {mobileOpen && (
        <div className="lg:hidden fixed inset-0 z-50 flex">
          <div
            className="absolute inset-0 bg-black/40"
            onClick={() => setMobileOpen(false)}
            aria-hidden="true"
          />
          <div className="relative w-[280px] max-w-[80vw] bg-white h-full shadow-xl animate-in slide-in-from-left duration-200">
            <NavContent onNavigate={() => setMobileOpen(false)} />
          </div>
        </div>
      )}
    </>
  );
}
