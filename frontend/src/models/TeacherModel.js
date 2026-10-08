import { apiRequest } from './ApiClient.js';

export const TeacherModel = {
  list(page = 1) {
    return apiRequest(`/teachers?page=${page}`);
  },

  create(data) {
    return apiRequest('/teachers', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  update(id, data) {
    return apiRequest(`/teachers/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  remove(id) {
    return apiRequest(`/teachers/${id}`, { method: 'DELETE' });
  },
};
