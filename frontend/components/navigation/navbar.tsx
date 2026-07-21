"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import Image from "next/image";
import { usePathname } from "next/navigation";
import { Menu, X, Search, User, ShoppingCart, ChevronDown, LogIn } from "lucide-react";
import { useAuth } from "@/lib/auth-context";
import { useCartContext } from "@/lib/cart-context";
import { CartDrawer } from "@/components/cart/cart-drawer";
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
  { label: "Distributor", href: "/distributor" },
  { label: "Contact Us", href: "/contact" },
];

export function Navbar() {
  const pathname = usePathname();
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [cartOpen, setCartOpen] = useState(false);
  const { user, isAuthenticated, logout } = useAuth();
  const { itemCount } = useCartContext();

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  useEffect(() => {
    setIsOpen(false);
  }, [pathname]);

  const isActive = (href: string) => {
    if (href === "/") return pathname === "/";
    return pathname === href || pathname.startsWith(`${href}/`);
  };

  return (
    <header
      className={cn(
        "fixed top-0 left-0 right-0 z-50 transition-all duration-300",
        scrolled
          ? "bg-[rgba(5,13,24,0.98)] backdrop-blur-md py-2 shadow-md"
          : "bg-[rgba(5,13,24,0.98)] py-3.5"
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
                    "flex items-center gap-1 font-medium text-sm py-2 transition-colors relative rounded-sm",
                    active ? "text-green-400" : "text-white hover:text-green-400"
                  )}
                >
                  {link.label}
                  {link.children && (
                    <ChevronDown className="w-3 h-3 group-hover:rotate-180 transition-transform" aria-hidden="true" />
                  )}
                  {active && (
                    <span className="absolute bottom-0 left-0 w-full h-0.5 bg-green-500 rounded-full" />
                  )}
                </Link>
                {link.children && (
                  <div className="absolute top-full left-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible focus-within:opacity-100 focus-within:visible transition-all duration-200">
                    <div className="bg-[#0a1628] border border-white/10 rounded-lg py-2 min-w-[220px] shadow-xl">
                      {link.children.map((child) => (
                        <Link
                          key={child.href}
                          href={child.href}
                          aria-current={pathname === child.href ? "page" : undefined}
                          className={cn(
                            "block px-4 py-2 text-sm transition-colors rounded-sm",
                            pathname === child.href
                              ? "text-green-400 bg-white/5"
                              : "text-white/90 hover:text-green-400 hover:bg-white/5"
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
          <button aria-label="Search products" className="text-white hover:text-green-400 transition-colors p-2 rounded-full focus-visible:ring-2 focus-visible:ring-green-500">
            <Search className="w-5 h-5" aria-hidden="true" />
          </button>

          <button
            onClick={() => setCartOpen(!cartOpen)}
            aria-label={`Shopping cart (${itemCount} items)`}
            className="relative text-white hover:text-green-400 transition-colors p-2 rounded-full focus-visible:ring-2 focus-visible:ring-green-500"
          >
            <ShoppingCart className="w-5 h-5" aria-hidden="true" />
            {itemCount > 0 && (
              <span className="absolute top-0 right-0 w-4 h-4 bg-green-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                {itemCount > 99 ? "99+" : itemCount}
              </span>
            )}
          </button>

          {isAuthenticated ? (
            <div className="group relative">
              <Link
                href={user?.is_admin ? adminDashboardUrl : "/account"}
                aria-label={user?.is_admin ? "Admin dashboard" : "My account"}
                className="flex items-center gap-1 text-white hover:text-green-400 transition-colors p-2 rounded-full focus-visible:ring-2 focus-visible:ring-green-500"
              >
                <User className="w-5 h-5" />
                <span className="hidden xl:inline text-sm font-medium">{user?.name?.split(" ")[0]}</span>
              </Link>
              <div className="absolute top-full right-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                <div className="bg-[#0a1628] border border-white/10 rounded-lg py-2 min-w-[180px] shadow-xl">
                  {user?.is_admin ? (
                    <Link href={adminDashboardUrl} className="block px-4 py-2 text-sm text-white/90 hover:text-green-400 hover:bg-white/5">
                      Admin Dashboard
                    </Link>
                  ) : (
                    <>
                      <Link href="/account" className="block px-4 py-2 text-sm text-white/90 hover:text-green-400 hover:bg-white/5">
                        Dashboard
                      </Link>
                      <Link href="/account/orders" className="block px-4 py-2 text-sm text-white/90 hover:text-green-400 hover:bg-white/5">
                        My Orders
                      </Link>
                    </>
                  )}
                  <button
                    onClick={() => logout()}
                    className="w-full text-left block px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-white/5"
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
              className="text-white hover:text-green-400 transition-colors p-2 rounded-full focus-visible:ring-2 focus-visible:ring-green-500"
            >
              <LogIn className="w-5 h-5" />
            </Link>
          )}

          <button
            className="lg:hidden text-white p-2 z-50 rounded-full focus-visible:ring-2 focus-visible:ring-green-500"
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
          "fixed inset-0 bg-[rgba(10,22,40,0.98)] flex flex-col items-center justify-center gap-8 transition-transform duration-400 lg:hidden",
          isOpen ? "translate-x-0" : "-translate-x-full"
        )}
        aria-hidden={!isOpen}
      >
        {navLinks.map((link) => (
          <div key={link.href} className="text-center">
            <Link
              href={link.href}
              className={cn(
                "text-xl font-medium transition-colors",
                isActive(link.href) ? "text-green-400" : "text-white hover:text-green-400"
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
                      "text-sm transition-colors",
                      pathname === child.href ? "text-green-400" : "text-white/70 hover:text-green-400"
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
          <Link href="/auth/login" className="text-green-400 font-medium text-lg">
            Sign In
          </Link>
        )}
      </div>

      <CartDrawer open={cartOpen} onClose={() => setCartOpen(false)} />
    </header>
  );
}
