import { useCallback, useState } from 'react';
import { ApiError } from '../models/ApiClient.js';
import { AttendanceModel } from '../models/AttendanceModel.js';

export function useAttendanceController() {
  const [rows, setRows] = useState([]);
  const [summary, setSummary] = useState(null);
  const [schoolClass, setSchoolClass] = useState(null);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [fieldErrors, setFieldErrors] = useState(null);
  const [success, setSuccess] = useState(null);

  const loadSheet = useCallback(async (schoolClassId, date) => {
    if (!schoolClassId || !date) {
      setRows([]);
      setSummary(null);
      setSchoolClass(null);
      return;
    }

    setLoading(true);
    setError(null);
    setSuccess(null);
    try {
      const [sheet, summaryData] = await Promise.all([
        AttendanceModel.sheet(schoolClassId, date),
        AttendanceModel.summary(schoolClassId, date),
      ]);
      setSchoolClass(sheet.school_class);
      setRows(sheet.rows ?? []);
      setSummary(summaryData);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load attendance sheet.');
      setRows([]);
      setSummary(null);
    } finally {
      setLoading(false);
    }
  }, []);

  const saveBulk = useCallback(async (schoolClassId, date, statusByStudent) => {
    setSaving(true);
    setError(null);
    setFieldErrors(null);
    setSuccess(null);
    try {
      const records = Object.entries(statusByStudent).map(([studentId, status]) => ({
        student_id: Number(studentId),
        status,
      }));
      await AttendanceModel.bulkSave(schoolClassId, date, records);
      setSuccess('Attendance saved successfully.');
      await loadSheet(schoolClassId, date);
      return true;
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
        setFieldErrors(err.errors);
      } else {
        setError('Failed to save attendance.');
      }
      return false;
    } finally {
      setSaving(false);
    }
  }, [loadSheet]);

  return {
    rows,
    summary,
    schoolClass,
    loading,
    saving,
    error,
    fieldErrors,
    success,
    loadSheet,
    saveBulk,
  };
}
