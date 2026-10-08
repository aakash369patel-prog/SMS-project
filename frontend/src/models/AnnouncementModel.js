import { apiRequest } from './ApiClient.js';

function queryString(params) {
  const search = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== '' && value != null) search.set(key, String(value));
  });
  const qs = search.toString();
  return qs ? `?${qs}` : '';
}

export const AnnouncementModel = {
  list(page = 1, filters = {}) {
    const extra = queryString(filters).replace('?', '&');
    return apiRequest(`/announcements?page=${page}${extra}`);
  },

  feed(page = 1) {
    return apiRequest(`/announcements/feed?page=${page}`);
  },

  create(data) {
    return apiRequest('/announcements', { method: 'POST', body: JSON.stringify(data) });
  },

  update(id, data) {
    return apiRequest(`/announcements/${id}`, { method: 'PUT', body: JSON.stringify(data) });
  },

  remove(id) {
    return apiRequest(`/announcements/${id}`, { method: 'DELETE' });
  },
};
