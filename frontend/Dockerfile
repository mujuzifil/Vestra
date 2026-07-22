# Multi-stage Dockerfile for Next.js frontend

FROM node:22-alpine AS base

# --- Dependencies stage ---
FROM base AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app

# `npm ci` installs exactly the committed lockfile and fails if package.json and
# package-lock.json have drifted. `npm install` would silently resolve new
# versions, so two builds of the same commit could ship different dependencies.
COPY package.json package-lock.json ./
RUN npm ci

# --- Build stage ---
FROM base AS builder
WORKDIR /app

COPY --from=deps /app/node_modules ./node_modules
COPY . .

# NEXT_PUBLIC_* values are inlined into the client bundle by the compiler at
# BUILD time. They must be present here as build args — supplying them only as
# container environment at run time leaves the shipped JavaScript pointing at
# whatever the fallback in lib/api/client.ts is (http://localhost:8000/api/v1).
#
# Changing any of these requires rebuilding this image; restarting the
# container has no effect.
ARG NEXT_PUBLIC_API_URL
ARG NEXT_PUBLIC_SITE_URL
ARG NEXT_PUBLIC_BACKEND_URL
ARG NEXT_PUBLIC_CDN_HOST

ENV NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL}
ENV NEXT_PUBLIC_SITE_URL=${NEXT_PUBLIC_SITE_URL}
ENV NEXT_PUBLIC_BACKEND_URL=${NEXT_PUBLIC_BACKEND_URL}
ENV NEXT_PUBLIC_CDN_HOST=${NEXT_PUBLIC_CDN_HOST}

ENV NEXT_TELEMETRY_DISABLED=1
ENV NODE_ENV=production

# Fail the build loudly rather than shipping a bundle that calls localhost.
RUN test -n "$NEXT_PUBLIC_API_URL" || \
    (echo "ERROR: NEXT_PUBLIC_API_URL build arg is required." && exit 1)

RUN npm run build

# --- Runtime stage ---
FROM base AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
ENV PORT=3000
ENV HOSTNAME=0.0.0.0

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

# Copy standalone output
COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

# 127.0.0.1, not localhost: `localhost` resolves to ::1 first inside the
# container and the Next.js server binds IPv4 only, so the probe would fail on
# a healthy container.
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://127.0.0.1:3000/api/health || exit 1

CMD ["node", "server.js"]
