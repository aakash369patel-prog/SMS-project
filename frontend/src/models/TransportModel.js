import { apiRequest } from './ApiClient.js';

export const TransportModel = {
  listRoutes(page = 1) {
    return apiRequest(`/transport/routes?page=${page}`);
  },
  createRoute(data) {
    return apiRequest('/transport/routes', { method: 'POST', body: JSON.stringify(data) });
  },
  listBuses(page = 1) {
    return apiRequest(`/transport/buses?page=${page}`);
  },
  createBus(data) {
    return apiRequest('/transport/buses', { method: 'POST', body: JSON.stringify(data) });
  },
  listAssignments() {
    return apiRequest('/transport/assignments');
  },
  assign(data) {
    return apiRequest('/transport/assignments', { method: 'POST', body: JSON.stringify(data) });
  },
};
