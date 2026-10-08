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

export const TimetableModel = {
  grid(schoolClassId) {
    return apiRequest(`/timetable/grid${queryString({ school_class_id: schoolClassId })}`);
  },

  bulkSave(schoolClassId, slots) {
    return apiRequest('/timetable/bulk', {
      method: 'POST',
      body: JSON.stringify({
        school_class_id: Number(schoolClassId),
        slots,
      }),
    });
  },
};
