import { Metadata } from "next";

export const siteConfig = {
  name: "VESTRA",
  tagline: "Professional Fabric Care",
  description:
    "VESTRA is a premium fabric care brand dedicated to developing high-performance cleaning solutions that combine advanced chemistry, innovation, and exceptional garment care.",
  url: "https://vestra.com",
  locale: "en_US",
  twitterHandle: "@vestracare",
};

/** Cache-bust query so browsers pick up Stage 24.12A favicon assets after deploy. */
export const FAVICON_VERSION = "2412a";

export function createMetadata({
  title,
  description,
  keywords = [],
  pathname = "/",
  image = "/assets/images/branding/vestra-logo.png",
}: {
  title: string;
  description: string;
  keywords?: string[];
  pathname?: string;
  image?: string;
}): Metadata {
  const fullTitle = title === siteConfig.name ? title : `${title} | ${siteConfig.name}`;
  const v = FAVICON_VERSION;

  return {
    title: fullTitle,
    description,
    keywords: ["VESTRA", "fabric care", "detergent", "cleaning solutions", "garment care", ...keywords],
    metadataBase: new URL(siteConfig.url),
    alternates: {
      canonical: pathname,
    },
    icons: {
      icon: [
        { url: `/favicon.ico?v=${v}`, sizes: "any" },
        { url: `/favicon-16x16.png?v=${v}`, sizes: "16x16", type: "image/png" },
        { url: `/favicon-32x32.png?v=${v}`, sizes: "32x32", type: "image/png" },
        { url: `/icon-192x192.png?v=${v}`, sizes: "192x192", type: "image/png" },
        { url: `/icon-512x512.png?v=${v}`, sizes: "512x512", type: "image/png" },
      ],
      apple: [{ url: `/apple-touch-icon.png?v=${v}`, sizes: "180x180", type: "image/png" }],
    },
    manifest: `/manifest.json?v=${v}`,
    openGraph: {
      title: fullTitle,
      description,
      url: pathname,
      siteName: siteConfig.name,
      locale: siteConfig.locale,
      type: "website",
      images: [
        {
          url: image,
          width: 1200,
          height: 630,
          alt: `${siteConfig.name} - ${siteConfig.tagline}`,
        },
      ],
    },
    twitter: {
      card: "summary_large_image",
      title: fullTitle,
      description,
      creator: siteConfig.twitterHandle,
      images: [image],
    },
  };
}
