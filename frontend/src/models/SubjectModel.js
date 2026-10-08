import { apiRequest } from './ApiClient.js';

export const SubjectModel = {
  list(page = 1) {
    return apiRequest(`/subjects?page=${page}`);
  },

  create(data) {
    return apiRequest('/subjects', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  update(id, data) {
    return apiRequest(`/subjects/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  remove(id) {
    return apiRequest(`/subjects/${id}`, { method: 'DELETE' });
  },
};
