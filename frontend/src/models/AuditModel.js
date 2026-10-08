import { apiRequest } from './ApiClient.js';

function qs(params) {
  const s = new URLSearchParams();
  Object.entries(params).forEach(([k, v]) => {
    if (v != null && v !== '') s.set(k, String(v));
  });
  const str = s.toString();
  return str ? `?${str}` : '';
}

export const AuditModel = {
  list(page = 1, filters = {}) {
    return apiRequest(`/audit-logs?page=${page}${qs(filters).replace('?', '&')}`);
  },
};
