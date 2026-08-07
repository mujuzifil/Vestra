"use client";

import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  AlertCircle,
  Camera,
  CheckCircle2,
  ChevronLeft,
  Loader2,
  Trash2,
  Upload,
  User,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useAvatar } from "@/hooks/use-avatar";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import { ApiRequestError } from "@/lib/api/client";

const ACCEPTED_TYPES = ["image/jpeg", "image/png", "image/webp"];
const MAX_BYTES = 2 * 1024 * 1024;

export function PhotoPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const { upload, isUploading, remove, isDeleting } = useAvatar();
  const inputRef = useRef<HTMLInputElement>(null);

  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  useEffect(() => {
    return () => {
      if (previewUrl) URL.revokeObjectURL(previewUrl);
    };
  }, [previewUrl]);

  const clearSelection = () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    setPreviewUrl(null);
    setSelectedFile(null);
    if (inputRef.current) inputRef.current.value = "";
  };

  const validateFile = (file: File): string | null => {
    if (!ACCEPTED_TYPES.includes(file.type)) {
      return "Please choose a JPG, PNG, or WEBP image.";
    }
    if (file.size > MAX_BYTES) {
      return "Image must be 2 MB or smaller.";
    }
    return null;
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    setError("");
    if (!file) return;

    const validationError = validateFile(file);
    if (validationError) {
      setError(validationError);
      clearSelection();
      return;
    }

    if (previewUrl) URL.revokeObjectURL(previewUrl);
    setSelectedFile(file);
    setPreviewUrl(URL.createObjectURL(file));
  };

  const handleUpload = async () => {
    if (!selectedFile) {
      setError("Choose a photo to upload.");
      return;
    }

    setError("");
    try {
      await upload(selectedFile);
      toastSuccess("Profile photo updated.");
      clearSelection();
    } catch (err: unknown) {
      const message =
        err instanceof ApiRequestError
          ? err.message
          : err instanceof Error
            ? err.message
            : "Failed to upload photo.";
      setError(message);
      toastError(message);
    }
  };

  const handleRemove = async () => {
    setError("");
    try {
      await remove();
      clearSelection();
      toastSuccess("Profile photo removed.");
    } catch (err: unknown) {
      const message =
        err instanceof ApiRequestError
          ? err.message
          : err instanceof Error
            ? err.message
            : "Failed to remove photo.";
      setError(message);
      toastError(message);
    }
  };

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user) return null;

  const displaySrc = previewUrl || user.avatar_url;
  const busy = isUploading || isDeleting;

  return (
    <>
      <PageHero
        title="Profile Photo"
        subtitle="Add or update the photo shown on your business portal"
        breadcrumb={[
          { label: "Account", href: "/account" },
          { label: "Profile", href: "/account/profile" },
          { label: "Photo" },
        ]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-text-heading mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Account
          </Link>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8 max-w-xl mx-auto">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                <Camera className="w-5 h-5" />
              </div>
              <div className="min-w-0">
                <h1 className="text-lg font-bold text-text-heading">
                  {user.avatar_url ? "Change Photo" : "Add Photo"}
                </h1>
                <p className="text-sm text-muted">
                  JPG, PNG, or WEBP up to 2 MB. Works on phone and desktop.
                </p>
              </div>
            </div>

            <div className="flex flex-col items-center gap-5">
              <div className="relative w-36 h-36 sm:w-44 sm:h-44 rounded-full overflow-hidden bg-secondary-50 border-4 border-white shadow-md">
                {displaySrc ? (
                  // eslint-disable-next-line @next/next/no-img-element -- remote avatar hosts vary; preview uses blob URLs
                  <img
                    src={displaySrc}
                    alt={user.name}
                    className="absolute inset-0 w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-secondary-600">
                    <User className="w-16 h-16" />
                  </div>
                )}
              </div>

              <input
                ref={inputRef}
                type="file"
                accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                capture="user"
                className="sr-only"
                onChange={handleFileChange}
                disabled={busy}
              />

              <div className="flex flex-col sm:flex-row w-full gap-3">
                <button
                  type="button"
                  onClick={() => inputRef.current?.click()}
                  disabled={busy}
                  className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-default bg-white text-sm font-semibold text-text-heading hover:bg-surface-page transition-colors-base disabled:opacity-50 w-full sm:flex-1"
                >
                  <Upload className="w-4 h-4" />
                  Choose Photo
                </button>

                <button
                  type="button"
                  onClick={handleUpload}
                  disabled={busy || !selectedFile}
                  className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-secondary-600 text-white text-sm font-semibold hover:bg-secondary-700 transition-colors-base disabled:opacity-50 w-full sm:flex-1"
                >
                  {isUploading ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <CheckCircle2 className="w-4 h-4" />
                  )}
                  {isUploading ? "Uploading…" : "Save Photo"}
                </button>
              </div>

              {user.avatar_url && (
                <button
                  type="button"
                  onClick={handleRemove}
                  disabled={busy}
                  className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-danger-600 hover:bg-danger-50 transition-colors-base disabled:opacity-50 w-full"
                >
                  {isDeleting ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <Trash2 className="w-4 h-4" />
                  )}
                  Remove Photo
                </button>
              )}

              {selectedFile && (
                <p className="text-xs text-muted text-center break-all">
                  Selected: {selectedFile.name} ({Math.round(selectedFile.size / 1024)} KB)
                </p>
              )}

              {error && (
                <div className="w-full flex items-start gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                  <AlertCircle className="w-4 h-4 flex-shrink-0 mt-0.5" />
                  <span className="min-w-0 break-words">{error}</span>
                </div>
              )}

              <Link
                href="/account/profile"
                className="text-sm font-semibold text-secondary-600 hover:text-secondary-700"
              >
                Edit profile information instead
              </Link>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
