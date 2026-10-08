import { apiRequest } from './ApiClient.js';

export const FeeTypeModel = {
  list(page = 1) {
    return apiRequest(`/fee-types?page=${page}`);
  },

  create(data) {
    return apiRequest('/fee-types', { method: 'POST', body: JSON.stringify(data) });
  },

  update(id, data) {
    return apiRequest(`/fee-types/${id}`, { method: 'PUT', body: JSON.stringify(data) });
  },

  remove(id) {
    return apiRequest(`/fee-types/${id}`, { method: 'DELETE' });
  },
};
