import { apiRequest } from './ApiClient.js';

function queryString(params) {
  const search = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== '' && value != null) search.set(key, String(value));
  });
  const qs = search.toString();
  return qs ? `&${qs}` : '';
}

export const StudentModel = {
  list(page = 1, filters = {}) {
    return apiRequest(`/students?page=${page}${queryString(filters)}`);
  },

  create(data) {
    return apiRequest('/students', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  update(id, data) {
    return apiRequest(`/students/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  remove(id) {
    return apiRequest(`/students/${id}`, { method: 'DELETE' });
  },
};
