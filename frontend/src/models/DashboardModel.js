import { apiRequest } from './ApiClient.js';

export const DashboardModel = {
  getStats() {
    return apiRequest('/dashboard');
  },
};
