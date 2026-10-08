import { apiRequest } from './ApiClient.js';

export const ParentModel = {
  list(page = 1) {
    return apiRequest(`/parents?page=${page}`);
  },

  create(data) {
    return apiRequest('/parents', { method: 'POST', body: JSON.stringify(data) });
  },

  update(id, data) {
    return apiRequest(`/parents/${id}`, { method: 'PUT', body: JSON.stringify(data) });
  },

  remove(id) {
    return apiRequest(`/parents/${id}`, { method: 'DELETE' });
  },
};
