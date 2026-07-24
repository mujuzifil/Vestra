"use client";

import { useState } from "react";
import { Send, Loader2 } from "lucide-react";
import { StarRating } from "./star-rating";
import { useAuth } from "@/lib/auth-context";

interface ReviewFormProps {
  productId: number;
  onSubmit: (data: { product_id: number; rating: number; title: string; comment: string }) => Promise<void>;
}

export function ReviewForm({ productId, onSubmit }: ReviewFormProps) {
  const { isAuthenticated } = useAuth();
  const [rating, setRating] = useState(0);
  const [title, setTitle] = useState("");
  const [comment, setComment] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);

  if (!isAuthenticated) {
    return (
      <div className="p-4 rounded-xl bg-surface-page text-center">
        <p className="text-sm text-muted">Please sign in to leave a review.</p>
      </div>
    );
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (rating === 0) {
      setError("Please select a star rating.");
      return;
    }
    setError("");
    setSubmitting(true);
    try {
      await onSubmit({ product_id: productId, rating, title, comment });
      setSuccess(true);
      setRating(0);
      setTitle("");
      setComment("");
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to submit review.";
      setError(message);
    } finally {
      setSubmitting(false);
    }
  };

  if (success) {
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
        <label className="block text-sm font-medium text-primary-900 mb-2">Your Rating</label>
        <StarRating rating={rating} interactive size="lg" onChange={setRating} />
      </div>

      <div>
        <label className="block text-sm font-medium text-primary-900 mb-1">Title (optional)</label>
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
        <label className="block text-sm font-medium text-primary-900 mb-1">Review</label>
        <textarea
          value={comment}
          onChange={(e) => setComment(e.target.value)}
          maxLength={1000}
          rows={4}
          placeholder="Share your experience with this product..."
          className="w-full px-4 py-2.5 rounded-xl border border-border focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none resize-none"
        />
        <p className="text-xs text-neutral-400 mt-1">{comment.length}/1000</p>
      </div>

      {error && <p className="text-sm text-danger-600">{error}</p>}

      <button
        type="submit"
        disabled={submitting}
        className="inline-flex items-center gap-2 px-6 py-2.5 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600/90 transition-colors-base disabled:opacity-50"
      >
        {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
        Submit Review
      </button>
    </form>
  );
}
