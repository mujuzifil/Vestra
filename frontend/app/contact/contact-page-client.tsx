"use client";

import { Suspense } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Phone, Mail, MessageCircle, MapPin, ArrowRight, ExternalLink } from "lucide-react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ContactCard } from "@/components/common/contact-card";
import { FAQAccordion } from "@/components/common/faq-accordion";
import { AnimatedSection } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";
import { ContactForm } from "@/components/forms/contact-form";
import { ApiError } from "@/components/ui/api-error";
import { Button } from "@/components/ui/button";
import { useContactInfo } from "@/hooks/use-settings";
import { JsonLd, contactPageSchema, localBusinessSchema } from "@/lib/structured-data";
import type { ContactEnquiryType } from "@/types";

const contactFaqs = [
  {
    question: "What are your business hours?",
    answer:
      "Our office is open Monday to Friday, 8:00 AM – 5:00 PM. Emergency support is available for institutional customers through our sales hotline.",
  },
  {
    question: "How quickly do you respond to enquiries?",
    answer:
      "We aim to respond to all website enquiries within 24–48 business hours. Sales and distributor enquiries are prioritised.",
  },
  {
    question: "Can I request a quotation through this page?",
    answer:
      "Yes. Select 'Quote' as the enquiry type or visit our Request a Quote page for a tailored commercial quotation.",
  },
  {
    question: "Do you supply institutions and businesses?",
    answer:
      "Yes. We supply hotels, hospitals, schools, commercial laundries, cleaning companies, supermarkets, and government institutions.",
  },
  {
    question: "Where are you located?",
    answer:
      "Our headquarters and manufacturing operations are in Kampala, Uganda. Directions are available on the map above.",
  },
  {
    question: "Can I become a distributor?",
    answer:
      "Absolutely. Complete the distributor application on our Become a Distributor page or select 'Distributor' in the contact form.",
  },
];

const socialLinks = [
  {
    name: "Facebook",
    description: "Follow product updates and company news.",
    href: "https://www.facebook.com/share/1LZTmjZC3J/",
    icon: "Facebook",
  },
  {
    name: "Instagram",
    description: "See VESTRA® in action across Uganda.",
    href: "https://www.instagram.com/vestradetergent",
    icon: "Instagram",
  },
  {
    name: "LinkedIn",
    description: "Connect with us for business and partnerships.",
    href: "https://www.linkedin.com/company/vestra%E2%84%A2/",
    icon: "Linkedin",
  },
  {
    name: "TikTok",
    description: "Watch tips, demos, and behind-the-scenes content.",
    href: "https://www.tiktok.com/@vestra.256707128442",
    icon: "Smartphone",
  },
  {
    name: "YouTube",
    description: "Product tutorials and commercial cleaning guides.",
    href: "https://youtube.com/@vestradetergent",
    icon: "Youtube",
  },
  {
    name: "WhatsApp Channel",
    description: "Subscribe for updates and quick announcements.",
    href: "https://whatsapp.com/channel/0029VbCSQuZ6WaKmC6z76a3n",
    icon: "MessageCircle",
  },
];

const resources = [
  { icon: "Package", title: "Products", description: "Browse the VESTRA® range.", href: "/products" },
  { icon: "FileText", title: "Request a Quote", description: "Get a tailored quotation.", href: "/request-quote" },
  { icon: "Truck", title: "Become a Distributor", description: "Partner with VESTRA®.", href: "/distributor" },
  { icon: "Newspaper", title: "Knowledge Centre", description: "Read articles and guides.", href: "/blog" },
  { icon: "MapPin", title: "Where to Buy", description: "Find authorised distributors.", href: "/where-to-buy" },
];

function ContactFormWithDefaults() {
  const searchParams = useSearchParams();
  const subject = searchParams.get("subject") ?? undefined;
  const enquiryType = (searchParams.get("type") as ContactEnquiryType) ?? undefined;
  return <ContactForm defaultSubject={subject} defaultEnquiryType={enquiryType} />;
}

function ContactHero() {
  return (
    <section
      className="relative pt-28 pb-20 lg:pt-40 lg:pb-32 overflow-hidden"
      style={{
        background:
          "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 50%, var(--primary-500) 100%)",
      }}
    >
      <div
        className="absolute inset-0 pointer-events-none"
        style={{
          background:
            "radial-gradient(circle at 20% 80%, rgba(112,192,80,0.12) 0%, transparent 45%), radial-gradient(circle at 80% 20%, rgba(13,59,102,0.6) 0%, transparent 40%)",
        }}
      />
      <Container className="relative z-10">
        <div className="max-w-3xl mx-auto text-center">
          <h1 className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-white mb-6 tracking-tight leading-tight">
            We&apos;re Here to Help
          </h1>
          <p className="text-base lg:text-xl text-white/75 max-w-2xl mx-auto leading-relaxed mb-8">
            Whether you&apos;re looking for products, distribution opportunities or commercial cleaning advice, our team is ready to assist.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a
              href="tel:+256707128442"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-text-heading bg-white shadow-lg hover:-translate-y-1 transition-transform-base"
            >
              <Phone className="w-4 h-4" aria-hidden="true" />
              Contact Sales
            </a>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50">
              <Link href="/request-quote">Request a Quote</Link>
            </Button>
          </div>
        </div>
      </Container>
    </section>
  );
}

export function ContactPageClient() {
  const { contactInfo, isLoading, error } = useContactInfo();

  const phone = contactInfo?.phone || "+256 707 128 442";
  const email = contactInfo?.email || "info@vestradetergents.com";
  const whatsapp = contactInfo?.whatsapp || "+256 707 128 442";
  const location = contactInfo?.location || "Kampala, Uganda";

  return (
    <>
      <JsonLd data={contactPageSchema()} />
      <JsonLd data={localBusinessSchema()} />
      <main>
        <ContactHero />

        {/* Contact Methods */}
        <section className="py-16 lg:py-24 bg-white" aria-labelledby="contact-methods-heading">
          <Container>
            <SectionHeader
              id="contact-methods-heading"
              title="Get in Touch"
              subtitle="Choose the channel that works best for you."
            />
            {isLoading ? (
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {Array.from({ length: 4 }).map((_, i) => (
                  <div key={i} className="h-48 rounded-[20px] bg-neutral-200 animate-pulse" />
                ))}
              </div>
            ) : error ? (
              <ApiError onRetry={() => window.location.reload()} />
            ) : (
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <ContactCard
                  icon="Phone"
                  title="Sales"
                  lines={[
                    {
                      label: "Phone",
                      value: phone,
                      href: `tel:${phone.replace(/\s/g, "")}`,
                    },
                    {
                      label: "WhatsApp",
                      value: whatsapp,
                      href: `https://wa.me/${whatsapp.replace(/\s/g, "")}`,
                    },
                  ]}
                />
                <ContactCard
                  icon="Mail"
                  title="Email"
                  lines={[
                    {
                      label: "General enquiries",
                      value: email,
                      href: `mailto:${email}`,
                    },
                    {
                      label: "Sales",
                      value: "info@vestradetergents.com",
                      href: "mailto:info@vestradetergents.com",
                    },
                  ]}
                />
                <ContactCard
                  icon="Clock"
                  title="Business Hours"
                  lines={[
                    { label: "Monday – Friday", value: "8:00 AM – 5:00 PM" },
                    { label: "Saturday", value: "9:00 AM – 1:00 PM" },
                    { label: "Sunday", value: "Closed" },
                  ]}
                />
                <ContactCard
                  icon="MapPin"
                  title="Office"
                  lines={[{ value: location }]}
                />
              </div>
            )}
          </Container>
        </section>

        {/* Form & Map */}
        <section className="py-16 lg:py-24 bg-surface-page" aria-labelledby="contact-form-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">
              <AnimatedSection direction="left" className="p-6 lg:p-10 rounded-[24px] bg-white border border-default shadow-lg">
                <h2 id="contact-form-heading" className="text-xl lg:text-2xl font-bold text-text-heading mb-2">
                  Send us a Message
                </h2>
                <p className="text-text-muted mb-6">
                  Fill out the form below and our team will get back to you shortly.
                </p>
                <Suspense fallback={<div className="h-64 rounded-xl bg-neutral-100 animate-pulse" />}>
                  <ContactFormWithDefaults />
                </Suspense>
              </AnimatedSection>

              <AnimatedSection
                direction="right"
                className="rounded-[24px] overflow-hidden border border-default bg-white shadow-lg flex flex-col h-full min-h-[480px]"
              >
                <div className="p-6 border-b border-default">
                  <div className="flex items-start gap-3">
                    <MapPin className="w-6 h-6 text-secondary-500 flex-shrink-0" aria-hidden="true" />
                    <div>
                      <h3 className="text-lg font-bold text-text-heading">Our Location</h3>
                      <p className="text-text-muted">{location}</p>
                    </div>
                  </div>
                </div>
                <div className="flex-1 relative bg-neutral-100">
                  <iframe
                    title="VESTRA® location on Google Maps"
                    src="https://maps.google.com/maps?q=VESTRA+Detergents,Kampala,Uganda&output=embed"
                    loading="lazy"
                    className="absolute inset-0 w-full h-full border-0"
                    allowFullScreen
                    referrerPolicy="no-referrer-when-downgrade"
                  />
                </div>
                <div className="p-4 border-t border-default bg-white">
                  <a
                    href="https://maps.app.goo.gl/MvaU1MNxJCGreTcB9?g_st=aw"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 text-sm font-semibold text-secondary-600 hover:text-secondary-700 transition-colors-base"
                  >
                    Get Directions
                    <ExternalLink className="w-4 h-4" aria-hidden="true" />
                  </a>
                </div>
              </AnimatedSection>
            </div>
          </Container>
        </section>

        {/* Social Community */}
        <section className="py-16 lg:py-24 bg-white" aria-labelledby="social-heading">
          <Container>
            <SectionHeader
              id="social-heading"
              title="Join the VESTRA® Community"
              subtitle="Follow us for product tips, industry insights, and company updates."
            />
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {socialLinks.map((social, index) => (
                <AnimatedSection key={social.name} delay={index * 0.05}>
                  <a
                    href={social.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="group flex items-start gap-4 p-6 rounded-[20px] bg-surface-card border border-default shadow-sm hover:-translate-y-1 hover:shadow-md transition-all-base h-full"
                  >
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-400 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary-400/25">
                      <Icon name={social.icon} className="w-6 h-6" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <h3 className="text-lg font-bold text-text-heading mb-1 flex items-center gap-2">
                        {social.name}
                        <ExternalLink className="w-4 h-4 text-text-muted opacity-0 group-hover:opacity-100 transition-opacity-base" aria-hidden="true" />
                      </h3>
                      <p className="text-sm text-text-muted mb-3">{social.description}</p>
                      <span className="inline-flex items-center gap-1 text-sm font-semibold text-secondary-600 group-hover:text-secondary-700 transition-colors-base">
                        Visit
                        <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
                      </span>
                    </div>
                  </a>
                </AnimatedSection>
              ))}
            </div>
          </Container>
        </section>

        {/* FAQ */}
        <section className="py-20 lg:py-28 bg-surface-page" aria-labelledby="faq-heading">
          <Container>
            <div className="grid lg:grid-cols-[0.8fr_1.2fr] gap-12 lg:gap-16">
              <div>
                <h2
                  id="faq-heading"
                  className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold tracking-tight mb-4"
                >
                  Frequently Asked Questions
                </h2>
                <p className="text-base lg:text-lg text-text-muted leading-relaxed">
                  Quick answers to common questions about contacting and working with VESTRA®.
                </p>
              </div>
              <FAQAccordion items={contactFaqs} />
            </div>
          </Container>
        </section>

        {/* Related Resources */}
        <section className="py-16 lg:py-24 bg-white" aria-labelledby="resources-heading">
          <Container>
            <SectionHeader
              id="resources-heading"
              title="Related Resources"
              subtitle="Explore more ways to connect with VESTRA®."
            />
            <div className="grid sm:grid-cols-2 lg:grid-cols-5 gap-6">
              {resources.map((resource, index) => (
                <AnimatedSection key={resource.title} delay={index * 0.05}>
                  <Link
                    href={resource.href}
                    className="group block p-6 rounded-[20px] bg-surface-card border border-default shadow-sm hover:-translate-y-1 hover:shadow-md transition-all-base h-full"
                  >
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-400 text-white flex items-center justify-center mb-4 shadow-lg shadow-primary-400/25">
                      <Icon name={resource.icon} className="w-6 h-6" />
                    </div>
                    <h3 className="text-lg font-bold text-text-heading mb-2">{resource.title}</h3>
                    <p className="text-sm text-text-muted mb-4">{resource.description}</p>
                    <span className="inline-flex items-center gap-1 text-sm font-semibold text-secondary-600 group-hover:text-secondary-700">
                      Learn more
                      <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
                    </span>
                  </Link>
                </AnimatedSection>
              ))}
            </div>
          </Container>
        </section>

        {/* Final CTA */}
        <section
          className="py-20 lg:py-28"
          style={{
            background: "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 100%)",
          }}
        >
          <Container>
            <div className="max-w-3xl mx-auto text-center px-6">
              <h2 className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-4 tracking-tight">
                Need Immediate Assistance?
              </h2>
              <p className="text-base lg:text-lg text-white/75 mb-8">
                Speak directly with our sales team by phone, WhatsApp, or email.
              </p>
              <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a
                  href={`tel:${phone.replace(/\s/g, "")}`}
                  className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold text-text-heading bg-white shadow-lg hover:-translate-y-1 transition-transform-base"
                >
                  <Phone className="w-4 h-4 mr-2" aria-hidden="true" />
                  Call Sales
                </a>
                <a
                  href={`https://wa.me/${whatsapp.replace(/\s/g, "")}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
                >
                  <MessageCircle className="w-4 h-4 mr-2" aria-hidden="true" />
                  WhatsApp
                </a>
                <a
                  href={`mailto:${email}`}
                  className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
                >
                  <Mail className="w-4 h-4 mr-2" aria-hidden="true" />
                  Email
                </a>
              </div>
            </div>
          </Container>
        </section>
      </main>
    </>
  );
}
