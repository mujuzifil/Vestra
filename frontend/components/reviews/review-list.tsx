"use client";

import { StarRating } from "./star-rating";
import type { Review } from "@/lib/api/reviews";

interface ReviewListProps {
  reviews: Review[];
  averageRating: number;
  reviewCount: number;
}

export function ReviewList({ reviews, averageRating, reviewCount }: ReviewListProps) {
  if (reviewCount === 0) {
    return (
      <div className="py-8 text-center text-[#64748b]">
        <p>No reviews yet. Be the first to review this product!</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4 p-4 rounded-xl bg-[#f8fafc]">
        <div className="text-center">
          <p className="text-3xl font-extrabold text-[#0a1628]">{averageRating.toFixed(1)}</p>
          <StarRating rating={Math.round(averageRating)} size="sm" />
          <p className="text-sm text-[#64748b] mt-1">{reviewCount} review{reviewCount !== 1 ? "s" : ""}</p>
        </div>
      </div>

      <div className="space-y-4">
        {reviews.map((review) => (
          <div key={review.id} className="p-4 rounded-xl border border-[#e2e8f0]">
            <div className="flex items-center justify-between mb-2">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-sm font-bold">
                  {review.user.name.charAt(0).toUpperCase()}
                </div>
                <div>
                  <p className="font-medium text-[#0a1628]">{review.user.name}</p>
                  <p className="text-xs text-[#94a3b8]">
                    {new Date(review.created_at).toLocaleDateString()}
                  </p>
                </div>
              </div>
              <StarRating rating={review.rating} size="sm" />
            </div>
            {review.title && <p className="font-semibold text-[#0a1628] mb-1">{review.title}</p>}
            {review.comment && <p className="text-sm text-[#64748b]">{review.comment}</p>}
          </div>
        ))}
      </div>
    </div>
  );
}
