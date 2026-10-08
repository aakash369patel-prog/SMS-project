const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

export class ApiError extends Error {
  constructor(message, status, errors = null) {
    super(message);
    this.status = status;
    this.errors = errors;
  }
}

export async function apiRequest(path, options = {}) {
  const token = localStorage.getItem('token');
  const headers = {
    Accept: 'application/json',
    ...options.headers,
  };

  if (!(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers,
  });

  const isJson = response.headers.get('content-type')?.includes('application/json');
  const payload = isJson ? await response.json() : null;

  if (!response.ok) {
    const message = payload?.message || `Request failed (${response.status})`;
    throw new ApiError(message, response.status, payload?.errors ?? null);
  }

  return payload;
}

export function unwrapPaginated(payload) {
  return {
    items: payload?.data ?? [],
    meta: payload?.meta ?? null,
    links: payload?.links ?? null,
  };
}

export async function downloadFile(path, filename) {
  const token = localStorage.getItem('token');
  const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
  const response = await fetch(`${API_BASE}${path}`, {
    headers: token ? { Authorization: `Bearer ${token}` } : {},
  });
  if (!response.ok) {
    throw new ApiError('Download failed.', response.status);
  }
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.click();
  window.URL.revokeObjectURL(url);
}
