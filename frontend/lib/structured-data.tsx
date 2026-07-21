import { Product } from "@/types";

const COMPANY_NAME = "VESTRA";
const COMPANY_DESCRIPTION = "VESTRA is a premium fabric care brand dedicated to developing high-performance cleaning solutions that combine advanced chemistry, innovation, and exceptional garment care.";
const CONTACT_PHONE = "+256 707 128 442";
const CONTACT_EMAIL = "vestradetergent@gmail.com";
const CONTACT_LOCATION = "Kampala, Uganda";

export function organizationSchema() {
  return {
    "@context": "https://schema.org",
    "@type": "Organization",
    name: COMPANY_NAME,
    url: "https://vestra.com",
    logo: "https://vestra.com/assets/images/branding/vestra-logo.png",
    description: COMPANY_DESCRIPTION,
    contactPoint: {
      "@type": "ContactPoint",
      telephone: CONTACT_PHONE.replace(/\s/g, ""),
      contactType: "Customer Service",
      email: CONTACT_EMAIL,
      areaServed: "UG",
      availableLanguage: ["English"],
    },
    sameAs: [],
  };
}

export function websiteSchema() {
  return {
    "@context": "https://schema.org",
    "@type": "WebSite",
    name: COMPANY_NAME,
    url: "https://vestra.com",
    potentialAction: {
      "@type": "SearchAction",
      target: {
        "@type": "EntryPoint",
        urlTemplate: "https://vestra.com/products?search={search_term_string}",
      },
      "query-input": "required name=search_term_string",
    },
  };
}

export function breadcrumbSchema(items: { name: string; url: string }[]) {
  return {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: items.map((item, index) => ({
      "@type": "ListItem",
      position: index + 1,
      name: item.name,
      item: item.url,
    })),
  };
}

export function productSchema(product: Product) {
  const imageUrl = product.images?.[0]?.image
    ? (product.images[0].image.startsWith("http") ? product.images[0].image : `https://vestra.com${product.images[0].image}`)
    : "https://vestra.com/assets/images/branding/vestra-logo.png";

  return {
    "@context": "https://schema.org",
    "@type": "Product",
    name: product.name,
    image: imageUrl,
    description: product.short_description || product.description,
    brand: {
      "@type": "Brand",
      name: COMPANY_NAME,
    },
    offers: {
      "@type": "Offer",
      url: `https://vestra.com/products/${product.slug}`,
      priceCurrency: "UGX",
      price: product.price.toString(),
      availability: "https://schema.org/InStock",
      itemCondition: "https://schema.org/NewCondition",
    },
  };
}

export function contactPageSchema() {
  return {
    "@context": "https://schema.org",
    "@type": "ContactPage",
    name: "Contact VESTRA",
    url: "https://vestra.com/contact",
    mainEntity: {
      "@type": "Organization",
      name: COMPANY_NAME,
      telephone: CONTACT_PHONE.replace(/\s/g, ""),
      email: CONTACT_EMAIL,
      address: {
        "@type": "PostalAddress",
        addressLocality: CONTACT_LOCATION,
        addressCountry: "UG",
      },
    },
  };
}

export function JsonLd({ data }: { data: Record<string, unknown> }) {
  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: JSON.stringify(data) }}
    />
  );
}
