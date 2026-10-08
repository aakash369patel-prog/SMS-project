import { apiRequest } from './ApiClient.js';

export const SchoolClassModel = {
  list(page = 1) {
    return apiRequest(`/classes?page=${page}`);
  },

  create(data) {
    return apiRequest('/classes', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  update(id, data) {
    return apiRequest(`/classes/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  remove(id) {
    return apiRequest(`/classes/${id}`, { method: 'DELETE' });
  },
};
