"use client";

import { useState, useEffect, useRef } from "react";
import Link from "next/link";
import Image from "next/image";
import { usePathname, useRouter } from "next/navigation";
import { Menu, X, Search, User, ChevronDown, LogIn, MapPin } from "lucide-react";
import { useAuth } from "@/lib/auth-context";
import { useSearchSuggestions } from "@/hooks/use-products";
import { useCategories } from "@/hooks/use-categories";
import { NotificationBell } from "@/components/notifications/notification-bell";
import { cn } from "@/lib/utils";

const adminDashboardUrl = "/admin";

const navLinks = [
  { label: "Home", href: "/" },
  { label: "About Us", href: "/about" },
  { label: "Products", href: "/products" },
  { label: "Become a Distributor", href: "/distributor" },
  { label: "Request a Quote", href: "/request-quote" },
  { label: "Where to Buy", href: "/where-to-buy" },
  { label: "Blog", href: "/blog" },
  { label: "Contact", href: "/contact" },
];

export function Navbar() {
  const router = useRouter();
  const pathname = usePathname();
  const { data: categories } = useCategories();
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const { user, isAuthenticated, logout } = useAuth();
  const searchInputRef = useRef<HTMLInputElement>(null);
  const { data: suggestions } = useSearchSuggestions(searchQuery);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 20);
    handleScroll();
    window.addEventListener("scroll", handleScroll, { passive: true });
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

  // Keep the page behind the mobile menu from scrolling, and unlock on close.
  useEffect(() => {
    if (!isOpen && !searchOpen) return;

    const previousOverflow = document.body.style.overflow;
    const previousPaddingRight = document.body.style.paddingRight;
    const scrollbarGap = window.innerWidth - document.documentElement.clientWidth;

    document.body.style.overflow = "hidden";
    if (scrollbarGap > 0) {
      document.body.style.paddingRight = `${scrollbarGap}px`;
    }

    return () => {
      document.body.style.overflow = previousOverflow;
      document.body.style.paddingRight = previousPaddingRight;
    };
  }, [isOpen, searchOpen]);

  const isActive = (href: string) => {
    if (href === "/") return pathname === "/";
    return pathname === href || pathname.startsWith(`${href}/`);
  };

  const isHome = pathname === "/";

  const productChildren = (categories ?? []).map((category) => ({
    label: category.name,
    href: `/products?category=${encodeURIComponent(category.slug)}`,
  }));

  const links = navLinks.map((link) =>
    link.label === "Products"
      ? { ...link, children: productChildren.length > 0 ? productChildren : undefined }
      : link
  );

  return (
    <header
      className={cn(
        // Fixed height so the bar does not shrink/jump when mobile browser chrome collapses on scroll.
        "fixed top-0 left-0 right-0 z-50 h-[72px] pt-[env(safe-area-inset-top)] transition-colors duration-300",
        isHome && !scrolled
          ? "bg-transparent"
          : "bg-primary-900 shadow-md"
      )}
    >
      <div className="container mx-auto flex h-full items-center gap-2 px-3 sm:px-4 lg:px-8">
        <Link href="/" className="min-w-0 flex-shrink-0">
          <Image
            src="/assets/images/branding/vestra-logo.png"
            alt="VESTRA"
            width={140}
            height={60}
            sizes="(max-width: 640px) 112px, 140px"
            className="h-10 w-auto object-contain sm:h-12"
            priority
          />
        </Link>

        <nav className="hidden lg:flex items-center gap-5 xl:gap-6 absolute left-1/2 -translate-x-1/2">
          {links.map((link) => {
            const active = isActive(link.href);
            return (
              <div key={link.href} className="group relative">
                <Link
                  href={link.href}
                  aria-current={active ? "page" : undefined}
                  aria-haspopup={link.children ? "true" : undefined}
                  className={cn(
                    "flex items-center gap-1 font-medium text-sm py-2 whitespace-nowrap transition-colors-base relative rounded-sm",
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

        {/* Mobile/tablet action cluster: Search → Where to Buy → Account → Menu */}
        <div className="ml-auto flex min-w-0 items-center gap-0.5 sm:gap-1.5">
          <button
            aria-label="Search products"
            onClick={() => setSearchOpen(true)}
            className="flex-shrink-0 rounded-full p-2 text-white transition-colors-base hover:text-secondary-400 focus-visible:ring-2 focus-visible:ring-secondary-500"
          >
            <Search className="h-5 w-5" aria-hidden="true" />
          </button>

          <Link
            href="/where-to-buy"
            data-track="mobile-header-where-to-buy"
            aria-label="Where to Buy"
            aria-current={isActive("/where-to-buy") ? "page" : undefined}
            className={cn(
              "lg:hidden inline-flex flex-shrink-0 items-center gap-1 rounded-full px-2.5 py-1.5 text-[11px] font-semibold leading-none whitespace-nowrap shadow-sm transition-colors-base sm:gap-1.5 sm:px-3 sm:text-xs",
              "bg-secondary-500 text-white hover:bg-secondary-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary-300 focus-visible:ring-offset-2 focus-visible:ring-offset-primary-900",
              isActive("/where-to-buy") && "ring-2 ring-white/40"
            )}
          >
            <MapPin className="h-3.5 w-3.5 flex-shrink-0" aria-hidden="true" />
            <span className="max-[359px]:hidden">Where to Buy</span>
            <span className="min-[360px]:hidden">Buy</span>
          </Link>

          {isAuthenticated && (
            <div className="hidden min-[400px]:block flex-shrink-0">
              <NotificationBell />
            </div>
          )}

          {isAuthenticated ? (
            <div className="group relative flex-shrink-0">
              <Link
                href={user?.is_admin ? adminDashboardUrl : "/account"}
                aria-label={user?.is_admin ? "Admin dashboard" : "My account"}
                className="flex items-center gap-1 rounded-full p-2 text-white transition-colors-base hover:text-secondary-400 focus-visible:ring-2 focus-visible:ring-secondary-500"
              >
                <User className="h-5 w-5" />
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
              className="flex-shrink-0 rounded-full p-2 text-white transition-colors-base hover:text-secondary-400 focus-visible:ring-2 focus-visible:ring-secondary-500"
            >
              <LogIn className="h-5 w-5" />
            </Link>
          )}

          <button
            className="lg:hidden flex-shrink-0 rounded-full p-2 text-white focus-visible:ring-2 focus-visible:ring-secondary-500"
            onClick={() => setIsOpen(true)}
            aria-label="Open menu"
            aria-expanded={isOpen}
            aria-controls="mobile-menu"
          >
            <Menu className="h-6 w-6" aria-hidden="true" />
          </button>
        </div>
      </div>

      {/* Mobile menu — full viewport panel, scrollable from any page scroll position */}
      <div
        id="mobile-menu"
        className={cn(
          "fixed inset-0 z-[60] lg:hidden flex flex-col bg-primary-900 transition-transform duration-300 ease-out",
          "pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)]",
          isOpen ? "translate-x-0" : "-translate-x-full pointer-events-none"
        )}
        style={{ height: "100dvh", top: 0 }}
        aria-hidden={!isOpen}
        role="dialog"
        aria-modal={isOpen}
        aria-label="Site navigation"
      >
        <div className="flex items-center justify-between px-4 h-[72px] flex-shrink-0 border-b border-white/10">
          <Link href="/" className="flex-shrink-0" onClick={() => setIsOpen(false)}>
            <Image
              src="/assets/images/branding/vestra-logo.png"
              alt="VESTRA"
              width={120}
              height={48}
              sizes="120px"
              className="h-10 w-auto object-contain"
            />
          </Link>
          <button
            type="button"
            onClick={() => setIsOpen(false)}
            className="p-2 rounded-full text-secondary-300 hover:text-secondary-200 focus-visible:ring-2 focus-visible:ring-secondary-500"
            aria-label="Close menu"
          >
            <X className="w-6 h-6" aria-hidden="true" />
          </button>
        </div>

        <nav className="flex-1 overflow-y-auto overscroll-contain px-6 py-6">
          <ul className="flex flex-col gap-1">
            {links.map((link) => (
              <li key={link.href}>
                <Link
                  href={link.href}
                  onClick={() => setIsOpen(false)}
                  className={cn(
                    "block py-3 text-xl font-medium transition-colors-base",
                    isActive(link.href) ? "text-secondary-300" : "text-secondary-400 hover:text-secondary-300"
                  )}
                >
                  {link.label}
                </Link>
                {link.children && (
                  <ul className="mb-2 ml-3 flex flex-col gap-1 border-l border-white/10 pl-4">
                    {link.children.map((child) => (
                      <li key={child.href}>
                        <Link
                          href={child.href}
                          onClick={() => setIsOpen(false)}
                          className={cn(
                            "block py-2 text-sm transition-colors-base",
                            pathname === child.href
                              ? "text-secondary-300"
                              : "text-secondary-400/80 hover:text-secondary-300"
                          )}
                        >
                          {child.label}
                        </Link>
                      </li>
                    ))}
                  </ul>
                )}
              </li>
            ))}
          </ul>

          {!isAuthenticated && (
            <Link
              href="/auth/login"
              onClick={() => setIsOpen(false)}
              className="mt-6 inline-flex text-secondary-300 font-semibold text-lg"
            >
              Sign In
            </Link>
          )}
        </nav>
      </div>

      {/* Search overlay */}
      {searchOpen && (
        <div className="fixed inset-0 z-[70] bg-primary-900/95 backdrop-blur-sm flex items-start justify-center pt-24 px-4">
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

