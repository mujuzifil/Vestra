"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Phone,
  MessageCircle,
  Mail,
  Clock,
  Loader2,
  Headphones,
  ArrowRight,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";

const contactCards = [
  {
    icon: Phone,
    title: "Phone",
    value: "+256 707 128 442",
    href: "tel:+256707128442",
    label: "Call now",
  },
  {
    icon: MessageCircle,
    title: "WhatsApp",
    value: "+256 707 128 442",
    href: "https://wa.me/256707128442",
    label: "Message",
  },
  {
    icon: Mail,
    title: "Email",
    value: "info@vestradetergents.com",
    href: "mailto:info@vestradetergents.com",
    label: "Send email",
  },
  {
    icon: Clock,
    title: "Business Hours",
    value: "Mon – Fri: 8:00 AM – 6:00 PM",
    href: null,
    label: null,
  },
];

export function SupportPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  return (
    <>
      <PageHero
        title="Support Centre"
        subtitle="We are here to help with your enquiries"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Support" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {contactCards.map((card) => (
              <div
                key={card.title}
                className="bg-surface-card rounded-[20px] border border-default shadow-sm p-5"
              >
                <div className="w-10 h-10 rounded-xl bg-secondary-50 flex items-center justify-center mb-4">
                  <card.icon className="w-5 h-5 text-secondary-600" />
                </div>
                <p className="text-sm text-muted mb-1">{card.title}</p>
                <p className="font-semibold text-text-heading mb-3">{card.value}</p>
                {card.href && card.label && (
                  <a
                    href={card.href}
                    target={card.href.startsWith("http") ? "_blank" : undefined}
                    rel={card.href.startsWith("http") ? "noopener noreferrer" : undefined}
                    className="inline-flex items-center gap-1.5 text-sm font-semibold text-secondary-600 hover:text-secondary-600"
                  >
                    {card.label}
                    <ArrowRight className="w-3.5 h-3.5" />
                  </a>
                )}
              </div>
            ))}
          </div>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            <div className="py-12 text-center">
              <Headphones className="w-14 h-14 mx-auto mb-4 text-placeholder" />
              <h3 className="text-lg font-bold text-text-heading mb-2">No support enquiries yet</h3>
              <p className="text-muted mb-6 max-w-md mx-auto">
                Need help? Contact our sales team and we will respond as soon as possible.
              </p>
              <Link
                href="/contact"
                className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
              >
                <ArrowRight className="w-4 h-4" />
                Contact Sales
              </Link>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
