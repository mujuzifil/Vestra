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

  return {
    title: fullTitle,
    description,
    keywords: ["VESTRA", "fabric care", "detergent", "cleaning solutions", "garment care", ...keywords],
    metadataBase: new URL(siteConfig.url),
    alternates: {
      canonical: pathname,
    },
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
