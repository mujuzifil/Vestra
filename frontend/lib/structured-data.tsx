import { BlogPost, Product } from "@/types";
import { getBlogImageUrl } from "@/lib/api/blog";

const COMPANY_NAME = "VESTRA";
const COMPANY_DESCRIPTION =
  "VESTRA® is a Ugandan manufacturer of professional cleaning solutions, supplying premium detergents and fabric care products to businesses, institutions, and distribution partners across Uganda.";
const CONTACT_PHONE = "+256 707 128 442";
const CONTACT_EMAIL = "vestradetergent@gmail.com";
const CONTACT_LOCATION = "Kampala, Uganda";
const SITE_URL = "https://vestradetergents.com";

export function organizationSchema() {
  return {
    "@context": "https://schema.org",
    "@type": ["Organization", "Manufacturer"],
    name: COMPANY_NAME,
    url: SITE_URL,
    logo: `${SITE_URL}/assets/images/branding/vestra-logo.png`,
    description: COMPANY_DESCRIPTION,
    contactPoint: {
      "@type": "ContactPoint",
      telephone: CONTACT_PHONE.replace(/\s/g, ""),
      contactType: "Sales",
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
    url: SITE_URL,
    potentialAction: {
      "@type": "SearchAction",
      target: {
        "@type": "EntryPoint",
        urlTemplate: `${SITE_URL}/products?search={search_term_string}`,
      },
      "query-input": "required name=search_term_string",
    },
  };
}

export function manufacturerSchema() {
  return {
    "@context": "https://schema.org",
    "@type": ["Organization", "Manufacturer"],
    name: COMPANY_NAME,
    url: SITE_URL,
    logo: `${SITE_URL}/assets/images/branding/vestra-logo.png`,
    description: COMPANY_DESCRIPTION,
    address: {
      "@type": "PostalAddress",
      addressLocality: CONTACT_LOCATION,
      addressCountry: "UG",
    },
    contactPoint: {
      "@type": "ContactPoint",
      telephone: CONTACT_PHONE.replace(/\s/g, ""),
      contactType: "Sales",
      email: CONTACT_EMAIL,
      areaServed: "UG",
      availableLanguage: ["English"],
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
    ? (product.images[0].image.startsWith("http") ? product.images[0].image : `${SITE_URL}${product.images[0].image}`)
    : `${SITE_URL}/assets/images/branding/vestra-logo.png`;

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
      url: `${SITE_URL}/products/${product.slug}`,
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
    url: `${SITE_URL}/contact`,
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

export function blogPostSchema(post: BlogPost) {
  const imageUrl = getBlogImageUrl(post.featured_image) ?? `${SITE_URL}/assets/images/branding/vestra-logo.png`;

  return {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    headline: post.meta_title ?? post.title,
    description: post.meta_description ?? post.excerpt,
    image: imageUrl,
    url: `${SITE_URL}/blog/${post.slug}`,
    datePublished: post.published_at,
    dateModified: post.updated_at,
    author: post.author
      ? {
          "@type": "Person",
          name: post.author.name,
          url: `${SITE_URL}/blog?author=${post.author.slug}`,
        }
      : {
          "@type": "Organization",
          name: COMPANY_NAME,
          url: SITE_URL,
        },
    publisher: {
      "@type": "Organization",
      name: COMPANY_NAME,
      logo: {
        "@type": "ImageObject",
        url: `${SITE_URL}/assets/images/branding/vestra-logo.png`,
      },
    },
    mainEntityOfPage: {
      "@type": "WebPage",
      "@id": `${SITE_URL}/blog/${post.slug}`,
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
