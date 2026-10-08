import { apiRequest } from './ApiClient.js';

export const HrModel = {
  listStaff(page = 1) {
    return apiRequest(`/hr/staff?page=${page}`);
  },
  createStaff(data) {
    return apiRequest('/hr/staff', { method: 'POST', body: JSON.stringify(data) });
  },
  listLeave(page = 1) {
    return apiRequest(`/hr/leave?page=${page}`);
  },
  createLeave(data) {
    return apiRequest('/hr/leave', { method: 'POST', body: JSON.stringify(data) });
  },
  updateLeaveStatus(id, status) {
    return apiRequest(`/hr/leave/${id}`, { method: 'PATCH', body: JSON.stringify({ status }) });
  },
  listPayroll(page = 1) {
    return apiRequest(`/hr/payroll?page=${page}`);
  },
  createPayroll(data) {
    return apiRequest('/hr/payroll', { method: 'POST', body: JSON.stringify(data) });
  },
};
