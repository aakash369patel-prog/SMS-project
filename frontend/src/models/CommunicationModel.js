import { apiRequest } from './ApiClient.js';

function qs(params) {
  const s = new URLSearchParams();
  Object.entries(params).forEach(([k, v]) => {
    if (v != null && v !== '') s.set(k, String(v));
  });
  const str = s.toString();
  return str ? `?${str}` : '';
}

export const CommunicationModel = {
  listNotifications(page = 1) {
    return apiRequest(`/communication/notifications?page=${page}`);
  },
  sendFeeReminders(includeSms = false) {
    return apiRequest('/communication/notifications/fee-reminders', {
      method: 'POST',
      body: JSON.stringify({ include_sms: includeSms }),
    });
  },
  sendAbsenceAlerts(date, includeSms = false) {
    return apiRequest('/communication/notifications/absence-alerts', {
      method: 'POST',
      body: JSON.stringify({ date, include_sms: includeSms }),
    });
  },
  sendAnnouncementNotice(announcementId, includeSms = false) {
    return apiRequest('/communication/notifications/announcement', {
      method: 'POST',
      body: JSON.stringify({ announcement_id: announcementId, include_sms: includeSms }),
    });
  },

  listThreads(page = 1) {
    return apiRequest(`/communication/threads?page=${page}`);
  },
  getThread(id) {
    return apiRequest(`/communication/threads/${id}`);
  },
  createThread(data) {
    return apiRequest('/communication/threads', { method: 'POST', body: JSON.stringify(data) });
  },
  replyThread(threadId, body) {
    return apiRequest(`/communication/threads/${threadId}/reply`, {
      method: 'POST',
      body: JSON.stringify({ body }),
    });
  },
  moderateMessage(messageId, moderationStatus) {
    return apiRequest(`/communication/messages/${messageId}/moderate`, {
      method: 'PATCH',
      body: JSON.stringify({ moderation_status: moderationStatus }),
    });
  },

  calendar(from, to) {
    return apiRequest(`/communication/calendar${qs({ from, to })}`);
  },
  createEvent(data) {
    return apiRequest('/communication/calendar/events', { method: 'POST', body: JSON.stringify(data) });
  },
  deleteEvent(id) {
    return apiRequest(`/communication/calendar/events/${id}`, { method: 'DELETE' });
  },
};
