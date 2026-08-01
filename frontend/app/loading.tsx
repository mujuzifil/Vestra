import { Container } from "@/components/common/container";

export default function Loading() {
  return (
    <main className="min-h-screen flex items-center justify-center bg-surface-page pt-24">
      <Container className="text-center">
        <div className="relative w-16 h-16 mx-auto mb-6">
          <div className="absolute inset-0 rounded-full border-4 border-default" />
          <div className="absolute inset-0 rounded-full border-4 border-green-500 border-t-transparent animate-spin" />
        </div>
        <p className="text-text-muted font-medium">Loading VESTRA...</p>
      </Container>
    </main>
  );
}
