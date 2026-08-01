"use client";

import { useState } from "react";
import Image from "next/image";
import { ThumbsUp, ThumbsDown, Flag, MessageSquare, X, Check } from "lucide-react";
import { StarRating } from "./star-rating";
import { useAuth } from "@/lib/auth-context";
import { useVoteReview, useReportReview } from "@/hooks/use-reviews";
import type { Review } from "@/lib/api/reviews";

interface ReviewListProps {
  reviews: Review[];
  averageRating: number;
  reviewCount: number;
  ratingDistribution?: { rating: number; count: number }[];
}

export function ReviewList({ reviews, averageRating, reviewCount, ratingDistribution }: ReviewListProps) {
  const { isAuthenticated } = useAuth();
  const voteMutation = useVoteReview();
  const reportMutation = useReportReview();
  const [reportingId, setReportingId] = useState<number | null>(null);
  const [reportReason, setReportReason] = useState("");
  const [reportDetails, setReportDetails] = useState("");
  const [lightboxImage, setLightboxImage] = useState<string | null>(null);

  if (reviewCount === 0) {
    return (
      <div className="py-8 text-center text-text-muted">
        <p>No reviews yet. Be the first to review this product!</p>
      </div>
    );
  }

  const maxDistribution = Math.max(1, ...(ratingDistribution ?? []).map((d) => d.count));

  const handleVote = (review: Review, isHelpful: boolean) => {
    if (!isAuthenticated) return;
    voteMutation.mutate({ id: review.id, isHelpful });
  };

  const submitReport = (reviewId: number) => {
    if (!reportReason.trim()) return;
    reportMutation.mutate(
      { id: reviewId, reason: reportReason, details: reportDetails },
      {
        onSuccess: () => {
          setReportingId(null);
          setReportReason("");
          setReportDetails("");
        },
      }
    );
  };

  return (
    <div className="space-y-6">
      {/* Summary */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl bg-surface-page">
        <div className="text-center min-w-[100px]">
          <p className="text-3xl font-extrabold text-text-heading">{averageRating.toFixed(1)}</p>
          <StarRating rating={Math.round(averageRating)} size="sm" />
          <p className="text-sm text-text-muted mt-1">{reviewCount} review{reviewCount !== 1 ? "s" : ""}</p>
        </div>
        {ratingDistribution && (
          <div className="flex-1 w-full space-y-1">
            {ratingDistribution.map((item) => (
              <div key={item.rating} className="flex items-center gap-2 text-sm">
                <span className="w-8 font-medium text-text-heading">{item.rating}★</span>
                <div className="flex-1 h-2 bg-neutral-100 rounded-full overflow-hidden">
                  <div
                    className="h-full bg-amber-400 rounded-full"
                    style={{ width: `${(item.count / maxDistribution) * 100}%` }}
                  />
                </div>
                <span className="w-8 text-right text-text-muted">{item.count}</span>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Reviews */}
      <div className="space-y-4">
        {reviews.map((review) => (
          <div key={review.id} className="p-4 rounded-xl border border-border bg-surface-card">
            <div className="flex items-center justify-between mb-2">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-full bg-secondary-100 text-secondary-600 flex items-center justify-center text-sm font-bold">
                  {review.user.name.charAt(0).toUpperCase()}
                </div>
                <div>
                  <p className="font-medium text-text-heading">{review.user.name}</p>
                  <p className="text-xs text-text-muted">
                    {new Date(review.created_at).toLocaleDateString()}
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <StarRating rating={review.rating} size="sm" />
                {review.is_featured && (
                  <span className="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-700">
                    <Check className="w-3 h-3" /> Featured
                  </span>
                )}
              </div>
            </div>

            {review.title && <p className="font-semibold text-text-heading mb-1">{review.title}</p>}
            {review.comment && <p className="text-sm text-text-muted mb-3">{review.comment}</p>}

            {/* Pros / Cons */}
            {(review.pros?.length > 0 || review.cons?.length > 0) && (
              <div className="grid sm:grid-cols-2 gap-3 mb-3">
                {review.pros?.length > 0 && (
                  <div>
                    <p className="text-xs font-semibold text-success-700 mb-1">Pros</p>
                    <ul className="space-y-1">
                      {review.pros.map((pro, idx) => (
                        <li key={idx} className="flex items-start gap-1.5 text-sm text-text-muted">
                          <Check className="w-3.5 h-3.5 text-success-500 mt-0.5 flex-shrink-0" />
                          {pro}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
                {review.cons?.length > 0 && (
                  <div>
                    <p className="text-xs font-semibold text-danger-700 mb-1">Cons</p>
                    <ul className="space-y-1">
                      {review.cons.map((con, idx) => (
                        <li key={idx} className="flex items-start gap-1.5 text-sm text-text-muted">
                          <X className="w-3.5 h-3.5 text-danger-500 mt-0.5 flex-shrink-0" />
                          {con}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </div>
            )}

            {/* Images */}
            {review.images?.length > 0 && (
              <div className="flex flex-wrap gap-2 mb-3">
                {review.images.map((image) => (
                  <button
                    key={image.id}
                    type="button"
                    onClick={() => setLightboxImage(image.url)}
                    className="relative w-16 h-16 rounded-lg overflow-hidden border border-border hover:opacity-90"
                  >
                    <Image src={image.url} alt="Review photo" fill className="object-cover" sizes="64px" />
                  </button>
                ))}
              </div>
            )}

            {/* Admin reply */}
            {review.admin_reply && (
              <div className="mb-3 p-3 rounded-xl bg-info-50 border border-info-100">
                <div className="flex items-center gap-2 mb-1">
                  <MessageSquare className="w-3.5 h-3.5 text-info-600" />
                  <span className="text-xs font-semibold text-info-700">VESTRA Response</span>
                </div>
                <p className="text-sm text-info-900">{review.admin_reply.content}</p>
                <p className="text-xs text-info-600 mt-1">
                  {review.admin_reply.replied_by && `${review.admin_reply.replied_by} • `}
                  {new Date(review.admin_reply.replied_at).toLocaleDateString()}
                </p>
              </div>
            )}

            {/* Actions */}
            <div className="flex flex-wrap items-center gap-3 pt-3 border-t border-default">
              <button
                type="button"
                onClick={() => handleVote(review, true)}
                disabled={!isAuthenticated || voteMutation.isPending}
                className={`inline-flex items-center gap-1.5 text-sm ${
                  review.user_vote === true ? "text-secondary-600 font-semibold" : "text-text-muted hover:text-text-heading"
                }`}
              >
                <ThumbsUp className="w-4 h-4" />
                Helpful ({review.helpful_count})
              </button>
              <button
                type="button"
                onClick={() => handleVote(review, false)}
                disabled={!isAuthenticated || voteMutation.isPending}
                className={`inline-flex items-center gap-1.5 text-sm ${
                  review.user_vote === false ? "text-danger-600 font-semibold" : "text-text-muted hover:text-text-heading"
                }`}
              >
                <ThumbsDown className="w-4 h-4" />
                Not helpful
              </button>
              <button
                type="button"
                onClick={() => setReportingId(review.id)}
                disabled={!isAuthenticated || reportMutation.isPending || review.user_reported}
                className={`inline-flex items-center gap-1.5 text-sm ml-auto ${
                  review.user_reported ? "text-danger-600 font-semibold" : "text-text-muted hover:text-danger-600"
                }`}
              >
                <Flag className="w-4 h-4" />
                {review.user_reported ? "Reported" : "Report"}
              </button>
            </div>

            {/* Report form */}
            {reportingId === review.id && (
              <div className="mt-3 p-3 rounded-xl bg-surface-page border border-default">
                <p className="text-sm font-medium text-text-heading mb-2">Report this review</p>
                <input
                  type="text"
                  value={reportReason}
                  onChange={(e) => setReportReason(e.target.value)}
                  placeholder="Reason (e.g. spam, offensive)"
                  className="w-full px-3 py-2 rounded-lg border border-border text-sm mb-2 focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                />
                <textarea
                  value={reportDetails}
                  onChange={(e) => setReportDetails(e.target.value)}
                  placeholder="Additional details (optional)"
                  rows={2}
                  className="w-full px-3 py-2 rounded-lg border border-border text-sm mb-2 resize-none focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                />
                <div className="flex gap-2">
                  <button
                    type="button"
                    onClick={() => submitReport(review.id)}
                    disabled={!reportReason.trim() || reportMutation.isPending}
                    className="px-4 py-1.5 text-sm font-semibold text-white bg-danger-600 rounded-lg hover:bg-danger-600/90 disabled:opacity-50"
                  >
                    Submit Report
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setReportingId(null);
                      setReportReason("");
                      setReportDetails("");
                    }}
                    className="px-4 py-1.5 text-sm font-semibold text-text-heading bg-surface-card border border-default rounded-lg hover:bg-surface-page"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Lightbox */}
      {lightboxImage && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
          onClick={() => setLightboxImage(null)}
        >
          <div className="relative w-full h-[80vh]">
            <Image src={lightboxImage} alt="Review photo" fill className="object-contain rounded-xl" sizes="(max-width: 768px) 100vw, 80vw" />
            <button
              type="button"
              onClick={() => setLightboxImage(null)}
              className="absolute -top-10 right-0 p-2 text-white hover:text-neutral-300"
            >
              <X className="w-6 h-6" />
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
