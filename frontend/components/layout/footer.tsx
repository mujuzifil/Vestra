"use client";

import Link from "next/link";
import Image from "next/image";
import { Globe, Mail, Phone, MapPin } from "lucide-react";
import { useContactInfo } from "@/hooks/use-settings";

const quickLinks = [
  { label: "Home", href: "/" },
  { label: "About Us", href: "/about" },
  { label: "Products", href: "/products" },
  { label: "Distributor", href: "/distributor" },
  { label: "Contact Us", href: "/contact" },
];

const productLinks = [
  { label: "Heavy Duty Detergent", href: "/products/heavy-duty-detergent" },
  { label: "Silk Care", href: "/products/silk-care" },
  { label: "EcoSuit Cleaner", href: "/products/ecosuit-cleaner" },
  { label: "Pro Finish", href: "/products/pro-finish" },
];

const socialIcons = [
  { Icon: Globe, href: "#", label: "Facebook" },
  { Icon: Globe, href: "#", label: "Instagram" },
  { Icon: Globe, href: "#", label: "WhatsApp" },
];

export function Footer() {
  const { contactInfo } = useContactInfo();

  const phone = contactInfo?.phone || "+256 707 128 442";
  const email = contactInfo?.email || "vestradetergent@gmail.com";
  const location = contactInfo?.location || "Kampala, Uganda";

  return (
    <footer className="bg-[#050d18] text-white pt-20 pb-8">
      <div className="container mx-auto px-4 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
          <div>
            <Image
              src="/assets/images/branding/vestra-logo.png"
              alt="VESTRA"
              width={180}
              height={80}
              sizes="180px"
              className="h-16 w-auto object-contain mb-6"
            />
            <p className="text-white/70 leading-relaxed mb-6">
              Premium fabric care solutions engineered for professional results and fabric protection.
            </p>
            <div className="flex gap-3">
              {socialIcons.map(({ Icon, href, label }) => (
                <a
                  key={label}
                  href={href}
                  aria-label={label}
                  className="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-green-500 hover:-translate-y-1 transition-all"
                >
                  <Icon className="w-4 h-4" />
                </a>
              ))}
            </div>
          </div>

          <div>
            <h4 className="text-lg font-bold mb-6">Quick Links</h4>
            <ul className="space-y-3">
              {quickLinks.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-white/70 hover:text-green-400 hover:translate-x-1 inline-block transition-all"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-lg font-bold mb-6">Products</h4>
            <ul className="space-y-3">
              {productLinks.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-white/70 hover:text-green-400 hover:translate-x-1 inline-block transition-all"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-lg font-bold mb-6">Contact Us</h4>
            <ul className="space-y-4">
              <li className="flex items-start gap-3 text-white/80">
                <MapPin className="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
                <span>{location}</span>
              </li>
              <li className="flex items-start gap-3 text-white/80">
                <Phone className="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
                <span>{phone}</span>
              </li>
              <li className="flex items-start gap-3 text-white/80">
                <Mail className="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
                <span>{email}</span>
              </li>
            </ul>
          </div>
        </div>

        <div className="border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-white/55 text-sm">
            &copy; {new Date().getFullYear()} VESTRA. All rights reserved.
          </p>
          <div className="flex gap-7">
            <Link href="/privacy-policy" className="text-white/55 text-sm hover:text-green-400 transition-colors">
              Privacy Policy
            </Link>
            <Link href="/terms" className="text-white/55 text-sm hover:text-green-400 transition-colors">
              Terms & Conditions
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
