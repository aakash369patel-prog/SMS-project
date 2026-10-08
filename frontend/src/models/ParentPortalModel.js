import { apiRequest } from './ApiClient.js';

export const ParentPortalModel = {
  overview() {
    return apiRequest('/parent/overview');
  },

  fees(page = 1) {
    return apiRequest(`/parent/fees?page=${page}`);
  },

  announcements(page = 1) {
    return apiRequest(`/parent/announcements?page=${page}`);
  },

  messages() {
    return apiRequest('/parent/messages');
  },
  messageThread(id) {
    return apiRequest(`/parent/messages/${id}`);
  },
  replyMessage(threadId, body) {
    return apiRequest(`/parent/messages/${threadId}/reply`, {
      method: 'POST',
      body: JSON.stringify({ body }),
    });
  },
  calendar(from, to) {
    const q = new URLSearchParams({ from, to }).toString();
    return apiRequest(`/parent/calendar?${q}`);
  },
  notifications(page = 1) {
    return apiRequest(`/parent/notifications?page=${page}`);
  },

  createPaymentIntent(studentFeeId, provider) {
    return apiRequest(`/parent/fees/${studentFeeId}/payment-intent`, {
      method: 'POST',
      body: JSON.stringify({ provider }),
    });
  },
  confirmPayment(orderId, externalPaymentId = null) {
    return apiRequest(`/parent/payment-orders/${orderId}/confirm`, {
      method: 'POST',
      body: JSON.stringify({ external_payment_id: externalPaymentId }),
    });
  },
};
