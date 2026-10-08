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

export const AttendanceModel = {
  sheet(schoolClassId, date) {
    return apiRequest(`/attendance/sheet${queryString({ school_class_id: schoolClassId, date })}`);
  },

  summary(schoolClassId, date) {
    return apiRequest(`/attendance/summary${queryString({ school_class_id: schoolClassId, date })}`);
  },

  bulkSave(schoolClassId, date, records) {
    return apiRequest('/attendance/bulk', {
      method: 'POST',
      body: JSON.stringify({
        school_class_id: Number(schoolClassId),
        date,
        records,
      }),
    });
  },

  list(filters = {}) {
    const { page, ...rest } = filters;
    const qs = queryString({ ...rest, page: page ?? undefined });
    return apiRequest(`/attendance${qs}`);
  },
};
