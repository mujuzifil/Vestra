"use client";

import { useState } from "react";
import Image from "next/image";
import { Send, Loader2, X, Upload } from "lucide-react";
import { StarRating } from "./star-rating";
import { useAuth } from "@/lib/auth-context";
import type { ReviewFormData } from "@/lib/api/reviews";

interface ReviewFormProps {
  productId?: number;
  initialData?: Partial<ReviewFormData>;
  onSubmit: (data: ReviewFormData) => Promise<void>;
  onCancel?: () => void;
}

export function ReviewForm({ productId, initialData, onSubmit, onCancel }: ReviewFormProps) {
  const { isAuthenticated } = useAuth();
  const [rating, setRating] = useState(initialData?.rating ?? 0);
  const [title, setTitle] = useState(initialData?.title ?? "");
  const [comment, setComment] = useState(initialData?.comment ?? "");
  const [pros, setPros] = useState<string[]>(initialData?.pros ?? []);
  const [cons, setCons] = useState<string[]>(initialData?.cons ?? []);
  const [proInput, setProInput] = useState("");
  const [conInput, setConInput] = useState("");
  const [images, setImages] = useState<File[]>([]);
  const [previews, setPreviews] = useState<string[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);

  if (!isAuthenticated) {
    return (
      <div className="p-4 rounded-xl bg-surface-page text-center">
        <p className="text-sm text-text-muted">Please sign in to leave a review.</p>
      </div>
    );
  }

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    if (files.length === 0) return;
    const combined = [...images, ...files].slice(0, 5);
    setImages(combined);
    setPreviews(combined.map((file) => URL.createObjectURL(file)));
  };

  const removeImage = (index: number) => {
    const next = images.filter((_, i) => i !== index);
    setImages(next);
    setPreviews(next.map((file) => URL.createObjectURL(file)));
  };

  const addTag = (type: "pro" | "con") => {
    const value = type === "pro" ? proInput.trim() : conInput.trim();
    if (!value) return;
    if (type === "pro") {
      setPros((prev) => [...prev, value]);
      setProInput("");
    } else {
      setCons((prev) => [...prev, value]);
      setConInput("");
    }
  };

  const removeTag = (type: "pro" | "con", index: number) => {
    if (type === "pro") {
      setPros((prev) => prev.filter((_, i) => i !== index));
    } else {
      setCons((prev) => prev.filter((_, i) => i !== index));
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (rating === 0) {
      setError("Please select a star rating.");
      return;
    }
    if (!productId && !initialData?.product_id) {
      setError("Product information is missing.");
      return;
    }
    setError("");
    setSubmitting(true);
    try {
      await onSubmit({
        product_id: productId ?? (initialData?.product_id as number),
        rating,
        title,
        comment,
        pros,
        cons,
        images,
      });
      setSuccess(true);
      if (!initialData) {
        setRating(0);
        setTitle("");
        setComment("");
        setPros([]);
        setCons([]);
        setImages([]);
        setPreviews([]);
      }
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to submit review.";
      setError(message);
    } finally {
      setSubmitting(false);
    }
  };

  if (success && !initialData) {
    return (
      <div className="p-4 rounded-xl bg-success-50 text-center">
        <p className="text-sm font-medium text-success-600">
          Thank you! Your review has been submitted and will be visible after moderation.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label className="block text-sm font-medium text-text-heading mb-2">Your Rating</label>
        <StarRating rating={rating} interactive size="lg" onChange={setRating} />
      </div>

      <div>
        <label className="block text-sm font-medium text-text-heading mb-1">Title (optional)</label>
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          maxLength={255}
          placeholder="Summarize your experience"
          className="w-full px-4 py-2.5 rounded-xl border border-border focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
        />
      </div>

      <div>
        <label className="block text-sm font-medium text-text-heading mb-1">Review</label>
        <textarea
          value={comment}
          onChange={(e) => setComment(e.target.value)}
          maxLength={1000}
          rows={4}
          placeholder="Share your experience with this product..."
          className="w-full px-4 py-2.5 rounded-xl border border-border focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none resize-none"
        />
        <p className="text-xs text-text-muted mt-1">{comment.length}/1000</p>
      </div>

      {/* Pros */}
      <div>
        <label className="block text-sm font-medium text-text-heading mb-1">Pros</label>
        <div className="flex flex-wrap gap-2 mb-2">
          {pros.map((pro, idx) => (
            <span key={idx} className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-success-50 text-success-700">
              {pro}
              <button type="button" onClick={() => removeTag("pro", idx)} className="hover:text-success-900">
                <X className="w-3 h-3" />
              </button>
            </span>
          ))}
        </div>
        <div className="flex gap-2">
          <input
            type="text"
            value={proInput}
            onChange={(e) => setProInput(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter") {
                e.preventDefault();
                addTag("pro");
              }
            }}
            placeholder="Add a pro and press Enter"
            className="flex-1 px-4 py-2 rounded-xl border border-border focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none text-sm"
          />
          <button
            type="button"
            onClick={() => addTag("pro")}
            className="px-4 py-2 text-sm font-semibold text-secondary-700 bg-secondary-50 rounded-xl hover:bg-secondary-100"
          >
            Add
          </button>
        </div>
      </div>

      {/* Cons */}
      <div>
        <label className="block text-sm font-medium text-text-heading mb-1">Cons</label>
        <div className="flex flex-wrap gap-2 mb-2">
          {cons.map((con, idx) => (
            <span key={idx} className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-danger-50 text-danger-700">
              {con}
              <button type="button" onClick={() => removeTag("con", idx)} className="hover:text-danger-900">
                <X className="w-3 h-3" />
              </button>
            </span>
          ))}
        </div>
        <div className="flex gap-2">
          <input
            type="text"
            value={conInput}
            onChange={(e) => setConInput(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter") {
                e.preventDefault();
                addTag("con");
              }
            }}
            placeholder="Add a con and press Enter"
            className="flex-1 px-4 py-2 rounded-xl border border-border focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none text-sm"
          />
          <button
            type="button"
            onClick={() => addTag("con")}
            className="px-4 py-2 text-sm font-semibold text-secondary-700 bg-secondary-50 rounded-xl hover:bg-secondary-100"
          >
            Add
          </button>
        </div>
      </div>

      {/* Images */}
      <div>
        <label className="block text-sm font-medium text-text-heading mb-2">Photos (optional, max 5)</label>
        <label className="flex items-center justify-center gap-2 w-full px-4 py-6 border-2 border-dashed border-border rounded-xl cursor-pointer hover:bg-surface-page transition-colors">
          <Upload className="w-5 h-5 text-text-muted" />
          <span className="text-sm text-text-muted">Click to upload images</span>
          <input
            type="file"
            accept="image/*"
            multiple
            onChange={handleImageChange}
            className="hidden"
          />
        </label>
        {previews.length > 0 && (
          <div className="flex flex-wrap gap-3 mt-3">
            {previews.map((src, idx) => (
              <div key={idx} className="relative w-16 h-16 rounded-lg overflow-hidden border border-border">
                <Image src={src} alt="" fill className="object-cover" sizes="64px" />
                <button
                  type="button"
                  onClick={() => removeImage(idx)}
                  className="absolute top-0.5 right-0.5 p-0.5 bg-black/50 text-white rounded-full"
                >
                  <X className="w-3 h-3" />
                </button>
              </div>
            ))}
          </div>
        )}
      </div>

      {error && <p className="text-sm text-danger-600">{error}</p>}

      <div className="flex items-center gap-3">
        <button
          type="submit"
          disabled={submitting}
          className="inline-flex items-center gap-2 px-6 py-2.5 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600/90 transition-colors-base disabled:opacity-50"
        >
          {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
          {submitting ? "Submitting..." : initialData ? "Update Review" : "Submit Review"}
        </button>
        {onCancel && (
          <button
            type="button"
            onClick={onCancel}
            className="inline-flex items-center gap-2 px-6 py-2.5 border border-border font-semibold rounded-xl hover:bg-surface-page transition-colors-base"
          >
            Cancel
          </button>
        )}
      </div>
    </form>
  );
}
