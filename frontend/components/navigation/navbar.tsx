"use client";

import { useState, useEffect, useRef } from "react";
import Link from "next/link";
import Image from "next/image";
import { usePathname, useRouter } from "next/navigation";
import { Menu, X, Search, User, ChevronDown, LogIn } from "lucide-react";
import { useAuth } from "@/lib/auth-context";
import { useSearchSuggestions } from "@/hooks/use-products";
import { NotificationBell } from "@/components/notifications/notification-bell";
import { cn } from "@/lib/utils";

const adminDashboardUrl = "/admin";

const navLinks = [
  { label: "Home", href: "/" },
  { label: "About Us", href: "/about" },
  {
    label: "Products",
    href: "/products",
    children: [
      { label: "Heavy Duty Detergent", href: "/products/heavy-duty-detergent" },
      { label: "Silk Care", href: "/products/silk-care" },
      { label: "EcoSuit Cleaner", href: "/products/ecosuit-cleaner" },
      { label: "Pro Finish", href: "/products/pro-finish" },
    ],
  },
  { label: "Become a Distributor", href: "/distributor" },
  { label: "Request a Quote", href: "/request-quote" },
  { label: "Where to Buy", href: "/where-to-buy" },
  { label: "Blog", href: "/blog" },
  { label: "Contact", href: "/contact" },
];

export function Navbar() {
  const router = useRouter();
  const pathname = usePathname();
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const { user, isAuthenticated, logout } = useAuth();
  const searchInputRef = useRef<HTMLInputElement>(null);
  const { data: suggestions } = useSearchSuggestions(searchQuery);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  useEffect(() => {
    setIsOpen(false);
  }, [pathname]);

  useEffect(() => {
    if (searchOpen) {
      searchInputRef.current?.focus();
    }
  }, [searchOpen]);

  const isActive = (href: string) => {
    if (href === "/") return pathname === "/";
    return pathname === href || pathname.startsWith(`${href}/`);
  };

  const isHome = pathname === "/";

  return (
    <header
      className={cn(
        "fixed top-0 left-0 right-0 z-50 transition-all-base duration-300",
        isHome && !scrolled
          ? "bg-transparent py-3.5"
          : scrolled
            ? "bg-primary-900/98 backdrop-blur-md py-2 shadow-md"
            : "bg-primary-900/98 py-3.5"
      )}
    >
      <div className="container mx-auto flex items-center justify-between px-4 lg:px-8">
        <Link href="/" className="flex-shrink-0">
          <Image
            src="/assets/images/branding/vestra-logo.png"
            alt="VESTRA"
            width={140}
            height={60}
            sizes="140px"
            className="h-12 w-auto object-contain"
            priority
          />
        </Link>

        <nav className="hidden lg:flex items-center gap-8 absolute left-1/2 -translate-x-1/2">
          {navLinks.map((link) => {
            const active = isActive(link.href);
            return (
              <div key={link.href} className="group relative">
                <Link
                  href={link.href}
                  aria-current={active ? "page" : undefined}
                  aria-haspopup={link.children ? "true" : undefined}
                  className={cn(
                    "flex items-center gap-1 font-medium text-sm py-2 transition-colors-base relative rounded-sm",
                    active ? "text-secondary-400" : "text-white hover:text-secondary-400"
                  )}
                >
                  {link.label}
                  {link.children && (
                    <ChevronDown className="w-3 h-3 group-hover:rotate-180 transition-transform-base" aria-hidden="true" />
                  )}
                  {active && (
                    <span className="absolute bottom-0 left-0 w-full h-0.5 bg-secondary-500 rounded-full" />
                  )}
                </Link>
                {link.children && (
                  <div className="absolute top-full left-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible focus-within:opacity-100 focus-within:visible transition-all-base duration-200">
                    <div className="bg-primary-900 border border-white/10 rounded-lg py-2 min-w-[220px] shadow-xl">
                      {link.children.map((child) => (
                        <Link
                          key={child.href}
                          href={child.href}
                          aria-current={pathname === child.href ? "page" : undefined}
                          className={cn(
                            "block px-4 py-2 text-sm transition-colors-base rounded-sm",
                            pathname === child.href
                              ? "text-secondary-400 bg-white/5"
                              : "text-white/90 hover:text-secondary-400 hover:bg-white/5"
                          )}
                        >
                          {child.label}
                        </Link>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
        </nav>

        <div className="flex items-center gap-2 ml-auto">
          <button
            aria-label="Search products"
            onClick={() => setSearchOpen(true)}
            className="text-white hover:text-secondary-400 transition-colors-base p-2 rounded-full focus-visible:ring-2 focus-visible:ring-secondary-500"
          >
            <Search className="w-5 h-5" aria-hidden="true" />
          </button>

          {isAuthenticated && <NotificationBell />}

          {isAuthenticated ? (
            <div className="group relative">
              <Link
                href={user?.is_admin ? adminDashboardUrl : "/account"}
                aria-label={user?.is_admin ? "Admin dashboard" : "My account"}
                className="flex items-center gap-1 text-white hover:text-secondary-400 transition-colors-base p-2 rounded-full focus-visible:ring-2 focus-visible:ring-secondary-500"
              >
                <User className="w-5 h-5" />
                <span className="hidden xl:inline text-sm font-medium">{user?.name?.split(" ")[0]}</span>
              </Link>
              <div className="absolute top-full right-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all-base duration-200">
                <div className="bg-primary-900 border border-white/10 rounded-lg py-2 min-w-[180px] shadow-xl">
                  {user?.is_admin ? (
                    <Link href={adminDashboardUrl} className="block px-4 py-2 text-sm text-white/90 hover:text-secondary-400 hover:bg-white/5 transition-colors-base">
                      Admin Dashboard
                    </Link>
                  ) : (
                    <>
                      <Link href="/account" className="block px-4 py-2 text-sm text-white/90 hover:text-secondary-400 hover:bg-white/5 transition-colors-base">
                        Dashboard
                      </Link>
                      <Link href="/account/orders" className="block px-4 py-2 text-sm text-white/90 hover:text-secondary-400 hover:bg-white/5 transition-colors-base">
                        My Orders
                      </Link>
                      <Link href="/notifications" className="block px-4 py-2 text-sm text-white/90 hover:text-secondary-400 hover:bg-white/5 transition-colors-base">
                        Notifications
                      </Link>
                    </>
                  )}
                  <button
                    onClick={() => logout()}
                    className="w-full text-left block px-4 py-2 text-sm text-danger-400 hover:text-danger-300 hover:bg-white/5 transition-colors-base"
                  >
                    Sign Out
                  </button>
                </div>
              </div>
            </div>
          ) : (
            <Link
              href="/auth/login"
              aria-label="Sign in"
              className="text-white hover:text-secondary-400 transition-colors-base p-2 rounded-full focus-visible:ring-2 focus-visible:ring-secondary-500"
            >
              <LogIn className="w-5 h-5" />
            </Link>
          )}

          <button
            className="lg:hidden text-white p-2 z-50 rounded-full focus-visible:ring-2 focus-visible:ring-secondary-500"
            onClick={() => setIsOpen(!isOpen)}
            aria-label={isOpen ? "Close menu" : "Open menu"}
            aria-expanded={isOpen}
            aria-controls="mobile-menu"
          >
            {isOpen ? <X className="w-6 h-6" aria-hidden="true" /> : <Menu className="w-6 h-6" aria-hidden="true" />}
          </button>
        </div>
      </div>

      {/* Mobile menu */}
      <div
        id="mobile-menu"
        className={cn(
          "fixed inset-0 bg-primary-800/98 flex flex-col items-center justify-center gap-8 transition-transform-base duration-400 lg:hidden",
          isOpen ? "translate-x-0" : "-translate-x-full"
        )}
        aria-hidden={!isOpen}
      >
        {navLinks.map((link) => (
          <div key={link.href} className="text-center">
            <Link
              href={link.href}
              className={cn(
                "text-xl font-medium transition-colors-base",
                isActive(link.href) ? "text-secondary-400" : "text-white hover:text-secondary-400"
              )}
            >
              {link.label}
            </Link>
            {link.children && (
              <div className="mt-2 flex flex-col gap-1">
                {link.children.map((child) => (
                  <Link
                    key={child.href}
                    href={child.href}
                    className={cn(
                      "text-sm transition-colors-base",
                      pathname === child.href ? "text-secondary-400" : "text-white/70 hover:text-secondary-400"
                    )}
                  >
                    {child.label}
                  </Link>
                ))}
              </div>
            )}
          </div>
        ))}
        {!isAuthenticated && (
          <Link href="/auth/login" className="text-secondary-400 font-medium text-lg">
            Sign In
          </Link>
        )}
      </div>

      {/* Search overlay */}
      {searchOpen && (
        <div className="fixed inset-0 z-[60] bg-primary-900/95 backdrop-blur-sm flex items-start justify-center pt-24 px-4">
          <div className="w-full max-w-2xl bg-white rounded-[20px] shadow-2xl overflow-hidden">
            <form
              onSubmit={(e) => {
                e.preventDefault();
                if (searchQuery.trim()) {
                  router.push(`/products?q=${encodeURIComponent(searchQuery.trim())}`);
                  setSearchOpen(false);
                  setSearchQuery("");
                }
              }}
              className="flex items-center gap-3 p-4 border-b border-default"
            >
              <Search className="w-5 h-5 text-placeholder" />
              <input
                ref={searchInputRef}
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search products..."
                className="flex-1 text-lg text-text-heading placeholder:text-placeholder outline-none"
              />
              <button
                type="button"
                onClick={() => setSearchOpen(false)}
                className="p-2 text-text-muted hover:text-text-heading"
                aria-label="Close search"
              >
                <X className="w-5 h-5" />
              </button>
            </form>
            <div className="max-h-[60vh] overflow-y-auto p-2">
              {suggestions && suggestions.length > 0 && (
                <div className="py-2">
                  <p className="px-4 text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Suggestions</p>
                  {suggestions.map((suggestion) => (
                    <Link
                      key={suggestion.id}
                      href={`/products/${suggestion.slug}`}
                      onClick={() => {
                        setSearchOpen(false);
                        setSearchQuery("");
                      }}
                      className="flex items-center gap-3 px-4 py-3 hover:bg-surface-page rounded-xl transition-colors-base"
                    >
                      <Search className="w-4 h-4 text-text-muted" />
                      <span className="text-sm font-medium text-text-heading">{suggestion.name}</span>
                    </Link>
                  ))}
                </div>
              )}
              {searchQuery.trim().length >= 2 && (!suggestions || suggestions.length === 0) && (
                <div className="px-4 py-6 text-center text-sm text-text-muted">No products found.</div>
              )}
            </div>
          </div>
        </div>
      )}
    </header>
  );
}
