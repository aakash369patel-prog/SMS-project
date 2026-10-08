import { useCallback, useState } from 'react';
import { ApiError } from '../models/ApiClient.js';
import { TimetableModel } from '../models/TimetableModel.js';

export function useTimetableController() {
  const [grid, setGrid] = useState(null);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  const loadGrid = useCallback(async (schoolClassId) => {
    if (!schoolClassId) {
      setGrid(null);
      return null;
    }

    setLoading(true);
    setError(null);
    setSuccess(null);
    try {
      const data = await TimetableModel.grid(schoolClassId);
      setGrid(data);
      return data;
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load timetable.');
      setGrid(null);
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const saveBulk = useCallback(async (schoolClassId, slots) => {
    setSaving(true);
    setError(null);
    setSuccess(null);
    try {
      await TimetableModel.bulkSave(schoolClassId, slots);
      setSuccess('Timetable saved successfully.');
      await loadGrid(schoolClassId);
      return true;
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to save timetable.');
      return false;
    } finally {
      setSaving(false);
    }
  }, [loadGrid]);

  return {
    grid,
    loading,
    saving,
    error,
    success,
    loadGrid,
    saveBulk,
  };
}
