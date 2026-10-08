import { apiRequest } from './ApiClient.js';

export const AuthModel = {
  login(email, password) {
    return apiRequest('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
  },

  register(name, email, password, password_confirmation) {
    return apiRequest('/auth/register', {
      method: 'POST',
      body: JSON.stringify({ name, email, password, password_confirmation }),
    });
  },

  me() {
    return apiRequest('/auth/me');
  },

  logout() {
    return apiRequest('/auth/logout', { method: 'POST' });
  },
};
