import { apiRequest } from './ApiClient.js';

export const UploadModel = {
  studentPhoto(studentId, file) {
    const body = new FormData();
    body.append('photo', file);
    return apiRequest(`/students/${studentId}/photo`, { method: 'POST', body });
  },
  listDocuments(studentId) {
    return apiRequest(`/students/${studentId}/documents`);
  },
  uploadDocument(studentId, title, file) {
    const body = new FormData();
    body.append('title', title);
    body.append('document', file);
    return apiRequest(`/students/${studentId}/documents`, { method: 'POST', body });
  },
  announcementAttachment(announcementId, file) {
    const body = new FormData();
    body.append('attachment', file);
    return apiRequest(`/announcements/${announcementId}/attachment`, { method: 'POST', body });
  },
};
