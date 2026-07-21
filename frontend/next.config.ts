import type { NextConfig } from "next";

function getApiOrigin(): string {
  const url = process.env.NEXT_PUBLIC_API_URL?.replace(/\/+$/, "");
  if (!url) return "http://localhost:8000";
  try {
    return new URL(url).origin;
  } catch {
    return "http://localhost:8000";
  }
}

function getBackendOrigin(): string {
  const url = process.env.NEXT_PUBLIC_BACKEND_URL?.replace(/\/+$/, "");
  if (!url) return "http://localhost:8000";
  try {
    return new URL(url).origin;
  } catch {
    return "http://localhost:8000";
  }
}

const API_ORIGIN = getApiOrigin();
const BACKEND_ORIGIN = getBackendOrigin();

const nextConfig: NextConfig = {
  output: "standalone",

  images: {
    remotePatterns: [
      {
        protocol: "http",
        hostname: "localhost",
        port: "8000",
        pathname: "/storage/**",
      },
      {
        protocol: "https",
        hostname: process.env.NEXT_PUBLIC_CDN_HOST || "**",
      },
    ],
    // Enable Next.js Image Optimization in production
    unoptimized: process.env.NODE_ENV === "development",
    formats: ["image/avif", "image/webp"],
    deviceSizes: [640, 750, 828, 1080, 1200, 1920, 2048, 3840],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
  },

  // Security headers
  async headers() {
    return [
      {
        source: "/(.*)",
        headers: [
          {
            key: "X-Frame-Options",
            value: "DENY",
          },
          {
            key: "X-Content-Type-Options",
            value: "nosniff",
          },
          {
            key: "Referrer-Policy",
            value: "strict-origin-when-cross-origin",
          },
          {
            key: "Permissions-Policy",
            value: "camera=(), microphone=(), geolocation=()",
          },
          {
            key: "Content-Security-Policy",
            value: [
              "default-src 'self'",
              "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
              "style-src 'self' 'unsafe-inline'",
              `img-src 'self' data: https: blob: ${API_ORIGIN}`,
              "font-src 'self' data:",
              `connect-src 'self' ${API_ORIGIN}`,
              "frame-ancestors 'none'",
              "base-uri 'self'",
              `form-action 'self' ${BACKEND_ORIGIN}`,
            ].join("; "),
          },
        ],
      },
    ];
  },

  // Compression
  compress: true,

  // Experimental features
  experimental: {
    optimizePackageImports: ["lucide-react"],
  },
};

export default nextConfig;
