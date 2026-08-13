import { Metadata } from "next";

export const siteConfig = {
  name: "VESTRA",
  tagline: "Professional Fabric Care",
  description:
    "VESTRA is a premium fabric care brand dedicated to developing high-performance cleaning solutions that combine advanced chemistry, innovation, and exceptional garment care.",
  url: "https://vestradetergents.com",
  locale: "en_US",
  twitterHandle: "@vestracare",
};

/** Cache-bust query so browsers/crawlers pick up the updated VESTRA favicon. */
export const FAVICON_VERSION = "260814";

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
        { url: `/favicon-48x48.png?v=${v}`, sizes: "48x48", type: "image/png" },
        { url: `/favicon-96x96.png?v=${v}`, sizes: "96x96", type: "image/png" },
        { url: `/icon-192x192.png?v=${v}`, sizes: "192x192", type: "image/png" },
        { url: `/icon-512x512.png?v=${v}`, sizes: "512x512", type: "image/png" },
      ],
      apple: [{ url: `/apple-touch-icon.png?v=${v}`, sizes: "180x180", type: "image/png" }],
      shortcut: [`/favicon.ico?v=${v}`],
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
