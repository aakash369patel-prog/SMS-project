import { useCallback, useState } from 'react';
import { ApiError } from '../models/ApiClient.js';
import { ExamModel } from '../models/ExamModel.js';

export function useExamGradesController() {
  const [exam, setExam] = useState(null);
  const [rows, setRows] = useState([]);
  const [summary, setSummary] = useState(null);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  const loadSheet = useCallback(async (examId) => {
    if (!examId) {
      setExam(null);
      setRows([]);
      setSummary(null);
      return;
    }

    setLoading(true);
    setError(null);
    setSuccess(null);
    try {
      const [sheet, summaryData] = await Promise.all([
        ExamModel.sheet(examId),
        ExamModel.summary(examId),
      ]);
      setExam(sheet.exam);
      setRows(sheet.rows ?? []);
      setSummary(summaryData);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load grade sheet.');
      setExam(null);
      setRows([]);
      setSummary(null);
    } finally {
      setLoading(false);
    }
  }, []);

  const saveBulk = useCallback(
    async (examId, marksByStudent) => {
      setSaving(true);
      setError(null);
      setSuccess(null);
      try {
        const records = Object.entries(marksByStudent).map(([studentId, marks_obtained]) => ({
          student_id: Number(studentId),
          marks_obtained: Number(marks_obtained),
        }));
        await ExamModel.bulkGrades(examId, records);
        setSuccess('Grades saved successfully.');
        await loadSheet(examId);
        return true;
      } catch (err) {
        setError(err instanceof ApiError ? err.message : 'Failed to save grades.');
        return false;
      } finally {
        setSaving(false);
      }
    },
    [loadSheet],
  );

  return {
    exam,
    rows,
    summary,
    loading,
    saving,
    error,
    success,
    loadSheet,
    saveBulk,
  };
}
