function getApiUrl(): string {
  const isServer = typeof window === "undefined";
  const base = isServer ? process.env.API_BASE_URL : process.env.NEXT_PUBLIC_API_URL;
  return base?.replace(/\/+$/, "") ?? "http://localhost:8000/api/v1";
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export class ApiRequestError extends Error {
  constructor(
    message: string,
    public status: number,
    public errors?: Record<string, string[]>
  ) {
    super(message);
    this.name = "ApiRequestError";
  }
}

function getAuthToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem("vestra_auth_token");
}

function handleUnauthenticated(): void {
  if (typeof window === "undefined") {
    return;
  }

  localStorage.removeItem("vestra_auth_token");
  window.location.href = "/auth/login";
}

async function handleResponse<T>(response: Response): Promise<T> {
  const contentType = response.headers.get("content-type") ?? "";
  const isJson = contentType.includes("application/json");

  if (!response.ok) {
    if (response.status === 401) {
      handleUnauthenticated();
    }

    if (isJson) {
      const errorBody = await response.json();
      throw new ApiRequestError(
        errorBody.message || `Request failed with status ${response.status}`,
        response.status,
        errorBody.errors
      );
    }
    throw new ApiRequestError(`Request failed with status ${response.status}`, response.status);
  }

  if (!isJson) {
    throw new ApiRequestError("Unexpected response format from server.", 500);
  }

  return response.json() as Promise<T>;
}

function buildHeaders(): Record<string, string> {
  const headers: Record<string, string> = {
    Accept: "application/json",
  };
  const token = getAuthToken();
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }
  return headers;
}

export async function apiGet<T>(path: string): Promise<T> {
  const response = await fetch(`${getApiUrl()}${path}`, {
    headers: buildHeaders(),
  });
  return handleResponse<T>(response);
}

export async function apiPost<T, B = Record<string, unknown>>(
  path: string,
  body: B
): Promise<T> {
  const response = await fetch(`${getApiUrl()}${path}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      ...buildHeaders(),
    },
    body: JSON.stringify(body as Record<string, unknown>),
  });
  return handleResponse<T>(response);
}

export async function apiPut<T, B = Record<string, unknown>>(
  path: string,
  body: B
): Promise<T> {
  const response = await fetch(`${getApiUrl()}${path}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      ...buildHeaders(),
    },
    body: JSON.stringify(body),
  });
  return handleResponse<T>(response);
}

export async function apiDelete<T>(path: string): Promise<T> {
  const response = await fetch(`${getApiUrl()}${path}`, {
    method: "DELETE",
    headers: buildHeaders(),
  });
  return handleResponse<T>(response);
}
