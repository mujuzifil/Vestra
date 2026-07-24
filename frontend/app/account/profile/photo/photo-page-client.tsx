"use client";

import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { ChevronLeft, Loader2, Camera, Trash2, User, AlertCircle } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useAvatar } from "@/hooks/use-avatar";
import { toastError, toastSuccess } from "@/lib/toast-utils";

const MAX_SIZE_MB = 5;
const ACCEPTED_TYPES = ["image/jpeg", "image/png", "image/webp"];

export function PhotoPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const { upload, isUploading, remove, isDeleting } = useAvatar();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setError("");

    if (!ACCEPTED_TYPES.includes(file.type)) {
      setError("Please upload a JPEG, PNG, or WebP image.");
      return;
    }
    if (file.size > MAX_SIZE_MB * 1024 * 1024) {
      setError(`Image must be smaller than ${MAX_SIZE_MB}MB.`);
      return;
    }

    try {
      await upload(file);
      toastSuccess("Profile photo updated.");
      if (fileInputRef.current) fileInputRef.current.value = "";
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to upload photo.";
      setError(message);
      toastError(message);
    }
  };

  const handleDelete = async () => {
    if (!confirm("Are you sure you want to remove your profile photo?")) return;
    setError("");
    try {
      await remove();
      toastSuccess("Profile photo removed.");
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to remove photo.";
      setError(message);
      toastError(message);
    }
  };

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user) return null;

  return (
    <>
      <PageHero
        title="Profile Photo"
        subtitle="Upload or remove your profile picture"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Settings", href: "/account/settings" }, { label: "Photo" }]}
      />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <Link
            href="/account/settings"
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628] mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Settings
          </Link>

          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8 max-w-xl">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-green-50 text-green-600">
                <Camera className="w-5 h-5" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-[#0a1628]">Profile Photo</h1>
                <p className="text-sm text-[#64748b]">A photo helps personalize your account.</p>
              </div>
            </div>

            <div className="flex flex-col items-center gap-6">
              <div className="relative w-32 h-32 rounded-full overflow-hidden bg-green-50 border-4 border-white shadow-lg">
                {user.avatar_url ? (
                  <Image
                    src={user.avatar_url}
                    alt={user.name}
                    fill
                    className="object-cover"
                    sizes="128px"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-green-600 text-3xl font-bold">
                    <User className="w-12 h-12" />
                  </div>
                )}
              </div>

              <div className="text-center">
                <p className="font-semibold text-[#0a1628]">{user.name}</p>
                <p className="text-sm text-[#64748b]">{user.email}</p>
              </div>

              <input
                ref={fileInputRef}
                type="file"
                accept={ACCEPTED_TYPES.join(",")}
                onChange={handleFileChange}
                className="hidden"
              />

              <div className="flex flex-wrap justify-center gap-3">
                <button
                  type="button"
                  onClick={() => fileInputRef.current?.click()}
                  disabled={isUploading || isDeleting}
                  className="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50"
                >
                  {isUploading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Camera className="w-4 h-4" />}
                  {isUploading ? "Uploading..." : user.avatar_url ? "Change Photo" : "Upload Photo"}
                </button>
                {user.avatar_url && (
                  <button
                    type="button"
                    onClick={handleDelete}
                    disabled={isUploading || isDeleting}
                    className="inline-flex items-center gap-2 px-5 py-2.5 border border-red-200 text-red-600 font-semibold rounded-xl hover:bg-red-50 transition-colors disabled:opacity-50"
                  >
                    {isDeleting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Trash2 className="w-4 h-4" />}
                    {isDeleting ? "Removing..." : "Remove Photo"}
                  </button>
                )}
              </div>

              {error && (
                <div className="flex items-center gap-2 text-sm text-red-600 bg-red-50 p-3 rounded-xl w-full">
                  <AlertCircle className="w-4 h-4" />
                  {error}
                </div>
              )}

              <div className="text-xs text-[#94a3b8] text-center">
                Recommended: square image, at least 400×400px. Max {MAX_SIZE_MB}MB. JPG, PNG, or WebP.
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
