import type { MetadataRoute } from "next";

export default function robots(): MetadataRoute.Robots {
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || "https://vestradetergents.com";

  return {
    rules: [
      {
        userAgent: "*",
        allow: "/",
        disallow: [
          "/account",
          "/auth",
          "/checkout",
          "/cart",
          "/compare",
          "/bulk-orders",
          "/api",
          "/admin",
        ],
      },
      {
        userAgent: "Googlebot",
        allow: "/",
        disallow: ["/account", "/auth", "/checkout", "/cart", "/compare", "/bulk-orders", "/api", "/admin"],
      },
    ],
    sitemap: `${baseUrl}/sitemap.xml`,
    host: baseUrl,
  };
}
