import { apiRequest } from './ApiClient.js';

function queryString(params) {
  const search = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== '' && value != null) search.set(key, String(value));
  });
  const qs = search.toString();
  return qs ? `?${qs}` : '';
}

export const StudentFeeModel = {
  list(page = 1, filters = {}) {
    const extra = queryString(filters).replace('?', '&');
    return apiRequest(`/student-fees?page=${page}${extra}`);
  },

  create(data) {
    return apiRequest('/student-fees', { method: 'POST', body: JSON.stringify(data) });
  },

  bulkAssignClass(data) {
    return apiRequest('/student-fees/bulk-class', { method: 'POST', body: JSON.stringify(data) });
  },

  recordPayment(studentFeeId, data) {
    return apiRequest(`/student-fees/${studentFeeId}/payments`, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  remove(id) {
    return apiRequest(`/student-fees/${id}`, { method: 'DELETE' });
  },

  applyDiscount(id, data) {
    return apiRequest(`/student-fees/${id}/discount`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    });
  },
};
