import { apiGet } from "./client";
import type {
  ApiResponse,
  BlogAuthor,
  BlogCategory,
  BlogPost,
  BlogPostFilters,
  BlogTag,
  PaginatedBlogPosts,
} from "@/types";

function getApiOrigin(): string {
  const url = process.env.NEXT_PUBLIC_API_URL?.replace(/\/+$/, "");
  if (!url) return "http://localhost:8000";
  try {
    return new URL(url).origin;
  } catch {
    return "http://localhost:8000";
  }
}

export function getBlogImageUrl(path: string | null | undefined): string | null {
  if (!path) return null;
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  if (path.startsWith("/")) return path;
  return `${getApiOrigin()}/storage/${path}`;
}

function buildQueryParams(filters: BlogPostFilters): URLSearchParams {
  const params = new URLSearchParams();

  if (filters.search) params.set("search", filters.search);
  if (filters.category) params.set("category", filters.category);
  if (filters.tag) params.set("tag", filters.tag);
  if (filters.sort) params.set("sort", filters.sort);
  if (filters.per_page) params.set("per_page", String(filters.per_page));
  if (filters.page && filters.page > 1) params.set("page", String(filters.page));

  return params;
}

export async function getBlogPosts(filters: BlogPostFilters = {}): Promise<PaginatedBlogPosts> {
  const query = buildQueryParams(filters).toString();
  const response = await apiGet<ApiResponse<PaginatedBlogPosts>>(`/blog/posts${query ? `?${query}` : ""}`);
  return response.data;
}

export async function getFeaturedPost(): Promise<BlogPost | null> {
  const response = await apiGet<ApiResponse<BlogPost | null>>("/blog/posts/featured");
  return response.data;
}

export async function getHomepagePosts(limit = 6): Promise<BlogPost[]> {
  const response = await apiGet<ApiResponse<BlogPost[]>>(`/blog/posts/homepage?limit=${limit}`);
  return Array.isArray(response.data) ? response.data : [];
}

export async function getBlogPost(slug: string): Promise<BlogPost> {
  const response = await apiGet<ApiResponse<BlogPost>>(`/blog/posts/${slug}`);
  return response.data;
}

export async function getBlogCategories(): Promise<BlogCategory[]> {
  const response = await apiGet<ApiResponse<BlogCategory[]>>("/blog/categories");
  return response.data;
}

export async function getBlogTags(): Promise<BlogTag[]> {
  const response = await apiGet<ApiResponse<BlogTag[]>>("/blog/tags");
  return response.data;
}

export type { BlogAuthor, BlogCategory, BlogPost, BlogTag };
