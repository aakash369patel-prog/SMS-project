import { apiRequest } from './ApiClient.js';

export const AcademicsModel = {
  listAssignments(page = 1, schoolClassId = '') {
    const q = schoolClassId ? `&school_class_id=${schoolClassId}` : '';
    return apiRequest(`/academics/assignments?page=${page}${q}`);
  },
  createAssignment(data) {
    return apiRequest('/academics/assignments', { method: 'POST', body: JSON.stringify(data) });
  },
  assignmentSubmissions(assignmentId) {
    return apiRequest(`/academics/assignments/${assignmentId}/submissions`);
  },
  updateSubmission(assignmentId, submissionId, data) {
    return apiRequest(`/academics/assignments/${assignmentId}/submissions/${submissionId}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    });
  },

  listQuizzes(page = 1) {
    return apiRequest(`/academics/quizzes?page=${page}`);
  },
  getQuiz(id) {
    return apiRequest(`/academics/quizzes/${id}`);
  },
  createQuiz(data) {
    return apiRequest('/academics/quizzes', { method: 'POST', body: JSON.stringify(data) });
  },
  quizAttempts(quizId) {
    return apiRequest(`/academics/quizzes/${quizId}/attempts`);
  },
  startQuiz(quizId, studentId) {
    return apiRequest(`/academics/quizzes/${quizId}/start`, {
      method: 'POST',
      body: JSON.stringify({ student_id: studentId }),
    });
  },
  submitQuiz(quizId, attemptId, answers) {
    return apiRequest(`/academics/quizzes/${quizId}/attempts/${attemptId}/submit`, {
      method: 'POST',
      body: JSON.stringify({ answers }),
    });
  },

  listSyllabi(page = 1) {
    return apiRequest(`/academics/syllabi?page=${page}`);
  },
  createSyllabus(data) {
    return apiRequest('/academics/syllabi', { method: 'POST', body: JSON.stringify(data) });
  },
  listLessonPlans(page = 1) {
    return apiRequest(`/academics/lesson-plans?page=${page}`);
  },
  createLessonPlan(data) {
    return apiRequest('/academics/lesson-plans', { method: 'POST', body: JSON.stringify(data) });
  },

  promotionHistory(page = 1) {
    return apiRequest(`/academics/promotions?page=${page}`);
  },
  bulkPromote(data) {
    return apiRequest('/academics/promotions/bulk', { method: 'POST', body: JSON.stringify(data) });
  },
};
