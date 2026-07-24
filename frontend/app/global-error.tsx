"use client";

import { useEffect } from "react";

interface GlobalErrorProps {
  error: Error & { digest?: string };
  reset: () => void;
}

/**
 * Last-resort error boundary.
 *
 * Catches failures in the root layout itself, which app/error.tsx cannot — it
 * lives inside that layout. Next.js replaces the entire document when this
 * renders, so it must supply its own <html> and <body>.
 *
 * Styles are inline rather than Tailwind on purpose: this boundary has to
 * render even when the CSS bundle is one of the things that failed to load.
 */
export default function GlobalError({ error, reset }: GlobalErrorProps) {
  useEffect(() => {
    console.error("Global error boundary:", error);
  }, [error]);

  return (
    <html lang="en">
      <body
        style={{
          margin: 0,
          minHeight: "100vh",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          backgroundColor: "var(--neutral-50)",
          fontFamily:
            "system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif",
          color: "var(--primary-900)",
          padding: "1.5rem",
        }}
      >
        <main style={{ textAlign: "center", maxWidth: "36rem" }}>
          <div
            style={{
              width: "5rem",
              height: "5rem",
              borderRadius: "9999px",
              backgroundColor: "var(--danger-100)",
              color: "var(--danger-600)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              margin: "0 auto 1.5rem",
              fontSize: "2.25rem",
              lineHeight: 1,
            }}
            aria-hidden="true"
          >
            !
          </div>

          <h1
            style={{
              fontSize: "1.875rem",
              fontWeight: 700,
              margin: "0 0 1rem",
            }}
          >
            Something went wrong
          </h1>

          <p
            style={{
              color: "var(--text-muted)",
              fontSize: "1.0625rem",
              lineHeight: 1.6,
              margin: "0 0 2rem",
            }}
          >
            VESTRA ran into an unexpected problem. Please try again — if this
            keeps happening, contact us and we&apos;ll help.
          </p>

          {error.digest ? (
            <p
              style={{
                color: "var(--text-placeholder)",
                fontSize: "0.8125rem",
                margin: "0 0 2rem",
                fontFamily: "ui-monospace, SFMono-Regular, Menlo, monospace",
              }}
            >
              Reference: {error.digest}
            </p>
          ) : null}

          <div
            style={{
              display: "flex",
              flexWrap: "wrap",
              justifyContent: "center",
              gap: "1rem",
            }}
          >
            <button
              onClick={reset}
              style={{
                padding: "0.75rem 1.5rem",
                borderRadius: "9999px",
                fontWeight: 600,
                fontSize: "1rem",
                color: "var(--surface-card)",
                background: "linear-gradient(to bottom right, var(--secondary-500), var(--secondary-600))",
                border: "none",
                cursor: "pointer",
              }}
            >
              Try Again
            </button>
            {/*
              A plain anchor, not next/link, on purpose: this boundary catches
              root-layout failures, so the client router is exactly what cannot
              be trusted here. A full document load is the recovery path.
            */}
            {/* eslint-disable-next-line @next/next/no-html-link-for-pages */}
            <a
              href="/"
              style={{
                padding: "0.75rem 1.5rem",
                borderRadius: "9999px",
                fontWeight: 600,
                fontSize: "1rem",
                color: "var(--primary-900)",
                backgroundColor: "var(--surface-card)",
                border: "1px solid var(--neutral-200)",
                textDecoration: "none",
                display: "inline-block",
              }}
            >
              Back to Home
            </a>
          </div>
        </main>
      </body>
    </html>
  );
}
