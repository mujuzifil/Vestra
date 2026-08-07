"use client";

import Link from "next/link";
import Image from "next/image";
import { Facebook, Instagram, Linkedin, MessageCircle, Youtube, Mail, Phone, MapPin, Clock } from "lucide-react";
import { useContactInfo } from "@/hooks/use-settings";

const quickLinks = [
  { label: "Home", href: "/" },
  { label: "About Us", href: "/about" },
  { label: "Products", href: "/products" },
  { label: "Request a Quote", href: "/request-quote" },
  { label: "Become a Distributor", href: "/distributor" },
  { label: "Where to Buy", href: "/where-to-buy" },
  { label: "Blog", href: "/blog" },
  { label: "Contact", href: "/contact" },
];

const productLinks = [
  { label: "Heavy Duty Detergent", href: "/products/heavy-duty-detergent" },
  { label: "Silk Care", href: "/products/silk-care" },
  { label: "EcoSuit Cleaner", href: "/products/ecosuit-cleaner" },
  { label: "Pro Finish", href: "/products/pro-finish" },
];

const socialLinks = [
  { Icon: Facebook, href: "https://www.facebook.com/share/1LZTmjZC3J/", label: "Facebook" },
  { Icon: Instagram, href: "https://www.instagram.com/vestradetergent", label: "Instagram" },
  { Icon: Linkedin, href: "https://www.linkedin.com/company/vestra%E2%84%A2/", label: "LinkedIn" },
  { Icon: Youtube, href: "https://youtube.com/@vestradetergent", label: "YouTube" },
  { Icon: MessageCircle, href: "https://whatsapp.com/channel/0029VbCSQuZ6WaKmC6z76a3n", label: "WhatsApp Channel" },
];

export function Footer() {
  const { contactInfo } = useContactInfo();

  const phone = contactInfo?.phone || "+256 707 128 442";
  const email = contactInfo?.email || "info@vestradetergents.com";
  const location = contactInfo?.location || "Kampala, Uganda";
  const businessHours = contactInfo?.businessHours || "Mon – Fri: 8:00 AM – 5:00 PM";

  return (
    <footer className="bg-primary-900 text-white pt-20 pb-8 overflow-x-clip w-full max-w-full">
      <div className="container mx-auto px-4 lg:px-8 max-w-[1320px] min-w-0 box-border">
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
              VESTRA® manufactures professional cleaning and fabric care solutions for businesses,
              institutions, and distribution partners across Uganda.
            </p>
            <div className="flex gap-3">
              {socialLinks.map(({ Icon, href, label }) => (
                <a
                  key={label}
                  href={href}
                  aria-label={label}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-secondary-500 hover:-translate-y-1 transition-all-base"
                >
                  <Icon className="w-4 h-4" aria-hidden="true" />
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
                    className="text-white/70 hover:text-secondary-400 hover:translate-x-1 inline-block transition-all-base"
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
                    className="text-white/70 hover:text-secondary-400 hover:translate-x-1 inline-block transition-all-base"
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
                <MapPin className="w-5 h-5 text-secondary-500 mt-0.5 flex-shrink-0" aria-hidden="true" />
                <span>{location}</span>
              </li>
              <li className="flex items-start gap-3 text-white/80">
                <Phone className="w-5 h-5 text-secondary-500 mt-0.5 flex-shrink-0" aria-hidden="true" />
                <span>{phone}</span>
              </li>
              <li className="flex items-start gap-3 text-white/80">
                <Mail className="w-5 h-5 text-secondary-500 mt-0.5 flex-shrink-0" aria-hidden="true" />
                <span>{email}</span>
              </li>
              <li className="flex items-start gap-3 text-white/80">
                <Clock className="w-5 h-5 text-secondary-500 mt-0.5 flex-shrink-0" aria-hidden="true" />
                <span>{businessHours}</span>
              </li>
            </ul>
          </div>
        </div>

        <div className="border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-white/55 text-sm">
            &copy; {new Date().getFullYear()} VESTRA®. All rights reserved.
          </p>
          <div className="flex gap-7">
            <Link
              href="/privacy-policy"
              className="text-white/55 text-sm hover:text-secondary-400 transition-colors-base"
            >
              Privacy Policy
            </Link>
            <Link
              href="/terms"
              className="text-white/55 text-sm hover:text-secondary-400 transition-colors-base"
            >
              Terms & Conditions
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
