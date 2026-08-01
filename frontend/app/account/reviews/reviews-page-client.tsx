"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { Star, Loader2, Trash2, Edit, ShoppingBag, MessageSquare, Check, X } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useMyReviews, useUpdateReview, useDeleteReview } from "@/hooks/use-reviews";
import { ReviewForm } from "@/components/reviews/review-form";
import { StarRating } from "@/components/reviews/star-rating";
import type { Review, ReviewFormData } from "@/lib/api/reviews";

function ReviewCard({
  review,
  onEdit,
  onDelete,
}: {
  review: Review;
  onEdit: (review: Review) => void;
  onDelete: (id: number) => void;
}) {
  return (
    <div className="p-4 rounded-xl border border-border bg-surface-card">
      <div className="flex items-start gap-3 mb-3">
        <div className="relative w-16 h-16 rounded-lg overflow-hidden border border-border flex-shrink-0 bg-surface-page">
          {review.product?.image ? (
            <Image
              src={review.product.image}
              alt={review.product.name}
              fill
              className="object-contain p-1"
              sizes="64px"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-muted">
              <ShoppingBag className="w-6 h-6" />
            </div>
          )}
        </div>
        <div className="flex-1 min-w-0">
          <Link
            href={`/products/${review.product?.slug}`}
            className="font-semibold text-primary-900 hover:text-secondary-600 truncate block"
          >
            {review.product?.name ?? "Unknown Product"}
          </Link>
          <div className="flex items-center gap-2 mt-1">
            <StarRating rating={review.rating} size="sm" />
            <span className="text-xs text-muted">{new Date(review.created_at).toLocaleDateString()}</span>
          </div>
          <div className="flex flex-wrap gap-2 mt-2">
            <span
              className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium ${
                review.status === "approved"
                  ? "bg-success-100 text-success-700"
                  : review.status === "rejected"
                  ? "bg-danger-100 text-danger-700"
                  : "bg-warning-100 text-warning-700"
              }`}
            >
              {review.status === "approved" && <Check className="w-3 h-3" />}
              {review.status === "rejected" && <X className="w-3 h-3" />}
              {review.status === "pending" && <Loader2 className="w-3 h-3" />}
              {review.status.charAt(0).toUpperCase() + review.status.slice(1)}
            </span>
            {review.is_featured && (
              <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-700">
                <Star className="w-3 h-3" /> Featured
              </span>
            )}
            {review.admin_reply && (
              <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-700">
                <MessageSquare className="w-3 h-3" /> Replied
              </span>
            )}
          </div>
        </div>
      </div>

      {review.title && <p className="font-semibold text-primary-900 mb-1">{review.title}</p>}
      {review.comment && <p className="text-sm text-muted mb-3">{review.comment}</p>}

      {(review.pros?.length > 0 || review.cons?.length > 0) && (
        <div className="grid sm:grid-cols-2 gap-3 mb-3">
          {review.pros?.length > 0 && (
            <div>
              <p className="text-xs font-semibold text-success-700 mb-1">Pros</p>
              <ul className="space-y-1">
                {review.pros.map((pro, idx) => (
                  <li key={idx} className="flex items-start gap-1.5 text-sm text-muted">
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
                  <li key={idx} className="flex items-start gap-1.5 text-sm text-muted">
                    <X className="w-3.5 h-3.5 text-danger-500 mt-0.5 flex-shrink-0" />
                    {con}
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}

      {review.images?.length > 0 && (
        <div className="flex flex-wrap gap-2 mb-3">
          {review.images.map((image) => (
            <div key={image.id} className="relative w-16 h-16 rounded-lg overflow-hidden border border-border">
              <Image src={image.url} alt="Review photo" fill className="object-cover" sizes="64px" />
            </div>
          ))}
        </div>
      )}

      {review.admin_reply && (
        <div className="mb-3 p-3 rounded-xl bg-info-50 border border-info-100">
          <div className="flex items-center gap-2 mb-1">
            <MessageSquare className="w-3.5 h-3.5 text-info-600" />
            <span className="text-xs font-semibold text-info-700">VESTRA Response</span>
          </div>
          <p className="text-sm text-info-900">{review.admin_reply.content}</p>
        </div>
      )}

      <div className="flex items-center justify-end gap-2 pt-3 border-t border-default">
        <button
          type="button"
          onClick={() => onEdit(review)}
          className="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-secondary-600 bg-secondary-50 rounded-lg hover:bg-secondary-100"
        >
          <Edit className="w-3.5 h-3.5" />
          Edit
        </button>
        <button
          type="button"
          onClick={() => onDelete(review.id)}
          className="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-danger-600 bg-danger-50 rounded-lg hover:bg-danger-100"
        >
          <Trash2 className="w-3.5 h-3.5" />
          Delete
        </button>
      </div>
    </div>
  );
}

export function ReviewsPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: reviewsData, isLoading: reviewsLoading } = useMyReviews();
  const updateReview = useUpdateReview();
  const deleteReview = useDeleteReview();
  const [editingReview, setEditingReview] = useState<Review | null>(null);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleUpdate = async (data: ReviewFormData) => {
    if (!editingReview) return;
    await updateReview.mutateAsync({ id: editingReview.id, data });
    setEditingReview(null);
  };

  const handleDelete = (id: number) => {
    if (confirm("Are you sure you want to delete this review?")) {
      deleteReview.mutate(id);
    }
  };

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  const reviews = reviewsData?.data ?? [];

  return (
    <>
      <PageHero
        title="My Reviews"
        subtitle="Manage your product reviews and feedback"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Reviews" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            {reviewsLoading ? (
              <div className="py-12 text-center">
                <Loader2 className="w-8 h-8 animate-spin text-secondary-500 mx-auto" />
              </div>
            ) : reviews.length === 0 ? (
              <div className="py-16 text-center">
                <Star className="w-14 h-14 mx-auto mb-4 text-placeholder" />
                <h3 className="text-lg font-bold text-primary-900 mb-2">No reviews yet</h3>
                <p className="text-muted mb-6">You haven&apos;t reviewed any products yet.</p>
                <Link
                  href="/products"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base"
                >
                  <ShoppingBag className="w-4 h-4" />
                  View Products
                </Link>
              </div>
            ) : editingReview ? (
              <div className="max-w-2xl">
                <h2 className="text-lg font-bold text-primary-900 mb-4">Edit Review</h2>
                <ReviewForm
                  productId={editingReview.product?.id}
                  initialData={{
                    product_id: editingReview.product?.id,
                    rating: editingReview.rating,
                    title: editingReview.title ?? undefined,
                    comment: editingReview.comment ?? undefined,
                    pros: editingReview.pros,
                    cons: editingReview.cons,
                  }}
                  onSubmit={handleUpdate}
                  onCancel={() => setEditingReview(null)}
                />
              </div>
            ) : (
              <div className="space-y-4">
                {reviews.map((review) => (
                  <ReviewCard
                    key={review.id}
                    review={review}
                    onEdit={setEditingReview}
                    onDelete={handleDelete}
                  />
                ))}
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
