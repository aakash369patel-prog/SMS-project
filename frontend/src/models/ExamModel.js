import { apiRequest } from './ApiClient.js';

function queryString(params) {
  const search = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== '' && value != null) {
      search.set(key, String(value));
    }
  });
  const qs = search.toString();
  return qs ? `?${qs}` : '';
}

export const ExamModel = {
  list(page = 1, filters = {}) {
    return apiRequest(`/exams?page=${page}${queryString(filters).replace('?', '&')}`);
  },

  create(data) {
    return apiRequest('/exams', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  update(id, data) {
    return apiRequest(`/exams/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  remove(id) {
    return apiRequest(`/exams/${id}`, { method: 'DELETE' });
  },

  sheet(examId) {
    return apiRequest(`/exams/${examId}/sheet`);
  },

  summary(examId) {
    return apiRequest(`/exams/${examId}/summary`);
  },

  bulkGrades(examId, records) {
    return apiRequest(`/exams/${examId}/grades/bulk`, {
      method: 'POST',
      body: JSON.stringify({ records }),
    });
  },
};
