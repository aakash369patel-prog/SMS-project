import { useEffect, useMemo, useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { useExamGradesController } from '../../controllers/useExamGradesController.js';
import { usePermissions } from '../../controllers/usePermissions.js';
import { ExamModel } from '../../models/ExamModel.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { SubjectModel } from '../../models/SubjectModel.js';
import { Pagination } from '../components/Pagination.jsx';

const EXAM_TYPES = [
  { value: 'quiz', label: 'Quiz' },
  { value: 'unit_test', label: 'Unit test' },
  { value: 'midterm', label: 'Midterm' },
  { value: 'final', label: 'Final' },
  { value: 'practical', label: 'Practical' },
];

const emptyExamForm = {
  name: '',
  code: '',
  school_class_id: '',
  subject_id: '',
  exam_type: 'midterm',
  exam_date: '',
  max_marks: '100',
  academic_year: '2025-2026',
  description: '',
};

function letterGrade(percentage) {
  if (percentage >= 90) return 'A+';
  if (percentage >= 80) return 'A';
  if (percentage >= 70) return 'B';
  if (percentage >= 60) return 'C';
  if (percentage >= 50) return 'D';
  return 'F';
}

export function ExamsView() {
  const { can } = usePermissions();
  const examsController = useResourceController(ExamModel);
  const gradesController = useExamGradesController();

  const [examForm, setExamForm] = useState(emptyExamForm);
  const [editingExamId, setEditingExamId] = useState(null);
  const [classes, setClasses] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [selectedExamId, setSelectedExamId] = useState('');
  const [marksByStudent, setMarksByStudent] = useState({});

  useEffect(() => {
    SchoolClassModel.list(1).then((payload) => setClasses(payload.data ?? []));
    SubjectModel.list(1).then((payload) => setSubjects(payload.data ?? []));
  }, []);

  useEffect(() => {
    if (selectedExamId) {
      gradesController.loadSheet(selectedExamId);
    }
  }, [selectedExamId, gradesController.loadSheet]);

  useEffect(() => {
    const initial = {};
    gradesController.rows.forEach((row) => {
      const id = row.student?.id;
      if (!id) return;
      initial[id] = row.grade?.marks_obtained ?? '';
    });
    setMarksByStudent(initial);
  }, [gradesController.rows]);

  const filteredSubjects = useMemo(() => {
    if (!examForm.school_class_id) return subjects;
    return subjects.filter((s) => String(s.school_class_id) === String(examForm.school_class_id));
  }, [subjects, examForm.school_class_id]);

  const maxMarks = gradesController.exam?.max_marks ?? 100;

  function startEditExam(item) {
    setEditingExamId(item.id);
    setExamForm({
      name: item.name ?? '',
      code: item.code ?? '',
      school_class_id: item.school_class_id ?? '',
      subject_id: item.subject_id ?? '',
      exam_type: item.exam_type ?? 'midterm',
      exam_date: item.exam_date ?? '',
      max_marks: String(item.max_marks ?? 100),
      academic_year: item.academic_year ?? '2025-2026',
      description: item.description ?? '',
    });
  }

  function resetExamForm() {
    setEditingExamId(null);
    setExamForm(emptyExamForm);
  }

  async function submitExam(event) {
    event.preventDefault();
    const payload = {
      ...examForm,
      school_class_id: Number(examForm.school_class_id),
      subject_id: examForm.subject_id ? Number(examForm.subject_id) : null,
      max_marks: Number(examForm.max_marks),
      description: examForm.description || null,
    };
    const ok = await examsController.save(payload, editingExamId);
    if (ok) resetExamForm();
  }

  async function submitGrades(event) {
    event.preventDefault();
    if (!selectedExamId) return;
    await gradesController.saveBulk(selectedExamId, marksByStudent);
  }

  return (
    <>
      <div className="page-header">
        <h2>Exams &amp; grades</h2>
      </div>

      {examsController.error ? <div className="alert error">{examsController.error}</div> : null}
      {gradesController.error ? <div className="alert error">{gradesController.error}</div> : null}
      {gradesController.success ? (
        <div className="alert" style={{ background: '#ecfdf5', border: '1px solid #a7f3d0', color: '#065f46' }}>
          {gradesController.success}
        </div>
      ) : null}

      {can('exams.manage') ? (
        <div className="card" style={{ marginBottom: '1.25rem' }}>
          <h3 style={{ marginTop: 0 }}>{editingExamId ? 'Edit exam' : 'Create exam'}</h3>
          <form onSubmit={submitExam}>
            <div className="form-grid">
              <label>
                Name
                <input value={examForm.name} onChange={(e) => setExamForm({ ...examForm, name: e.target.value })} required />
              </label>
              <label>
                Code
                <input value={examForm.code} onChange={(e) => setExamForm({ ...examForm, code: e.target.value })} required />
              </label>
              <label>
                Class
                <select
                  value={examForm.school_class_id}
                  onChange={(e) => setExamForm({ ...examForm, school_class_id: e.target.value, subject_id: '' })}
                  required
                >
                  <option value="">Select class</option>
                  {classes.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.label}
                    </option>
                  ))}
                </select>
              </label>
              <label>
                Subject
                <select value={examForm.subject_id} onChange={(e) => setExamForm({ ...examForm, subject_id: e.target.value })}>
                  <option value="">—</option>
                  {filteredSubjects.map((s) => (
                    <option key={s.id} value={s.id}>
                      {s.name} ({s.code})
                    </option>
                  ))}
                </select>
              </label>
              <label>
                Type
                <select value={examForm.exam_type} onChange={(e) => setExamForm({ ...examForm, exam_type: e.target.value })}>
                  {EXAM_TYPES.map((t) => (
                    <option key={t.value} value={t.value}>
                      {t.label}
                    </option>
                  ))}
                </select>
              </label>
              <label>
                Exam date
                <input type="date" value={examForm.exam_date} onChange={(e) => setExamForm({ ...examForm, exam_date: e.target.value })} required />
              </label>
              <label>
                Max marks
                <input type="number" min="1" value={examForm.max_marks} onChange={(e) => setExamForm({ ...examForm, max_marks: e.target.value })} required />
              </label>
              <label>
                Academic year
                <input value={examForm.academic_year} onChange={(e) => setExamForm({ ...examForm, academic_year: e.target.value })} required />
              </label>
            </div>
            <div className="actions">
              <button type="submit" className="btn">
                {editingExamId ? 'Update exam' : 'Create exam'}
              </button>
              {editingExamId ? (
                <button type="button" className="btn secondary" onClick={resetExamForm}>
                  Cancel
                </button>
              ) : null}
            </div>
          </form>
        </div>
      ) : null}

      <div className="card table-wrap" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>Exams</h3>
        {examsController.loading ? (
          <p>Loading exams…</p>
        ) : (
          <>
            <table>
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Name</th>
                  <th>Class</th>
                  <th>Subject</th>
                  <th>Date</th>
                  <th>Graded</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                {examsController.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.code}</td>
                    <td>{item.name}</td>
                    <td>{item.school_class?.label ?? '—'}</td>
                    <td>{item.subject?.name ?? '—'}</td>
                    <td>{item.exam_date}</td>
                    <td>{item.grades_count ?? 0}</td>
                    <td>
                      <div className="actions">
                        {can('grades.manage') ? (
                          <button type="button" className="btn secondary" onClick={() => setSelectedExamId(String(item.id))}>
                            Grade
                          </button>
                        ) : null}
                        {can('exams.manage') ? (
                          <>
                            <button type="button" className="btn secondary" onClick={() => startEditExam(item)}>
                              Edit
                            </button>
                            <button type="button" className="btn danger" onClick={() => examsController.remove(item.id)}>
                              Delete
                            </button>
                          </>
                        ) : null}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <Pagination meta={examsController.meta} onPageChange={(p) => examsController.load(p)} />
          </>
        )}
      </div>

      {can('grades.manage') ? (
        <div className="card">
          <h3 style={{ marginTop: 0 }}>Enter grades</h3>
          <div className="form-grid" style={{ marginBottom: '1rem' }}>
            <label>
              Exam
              <select value={selectedExamId} onChange={(e) => setSelectedExamId(e.target.value)}>
                <option value="">Select exam</option>
                {examsController.items.map((item) => (
                  <option key={item.id} value={item.id}>
                    {item.code} — {item.name}
                  </option>
                ))}
              </select>
            </label>
          </div>

          {!selectedExamId ? (
            <p style={{ color: 'var(--muted)' }}>Select an exam to enter marks.</p>
          ) : gradesController.loading ? (
            <p>Loading grade sheet…</p>
          ) : (
            <>
              {gradesController.summary ? (
                <div className="grid-stats" style={{ marginBottom: '1rem' }}>
                  <div className="stat-card">
                    <span>Average %</span>
                    <strong>{gradesController.summary.average_percentage}</strong>
                  </div>
                  <div className="stat-card">
                    <span>Highest %</span>
                    <strong>{gradesController.summary.highest_percentage}</strong>
                  </div>
                  <div className="stat-card">
                    <span>Passed</span>
                    <strong>{gradesController.summary.passed}</strong>
                  </div>
                  <div className="stat-card">
                    <span>Graded</span>
                    <strong>
                      {gradesController.summary.graded}/{gradesController.summary.students_in_class}
                    </strong>
                  </div>
                </div>
              ) : null}

              <form onSubmit={submitGrades}>
                <div className="table-wrap">
                  <table>
                    <thead>
                      <tr>
                        <th>Student</th>
                        <th>Marks ( / {maxMarks})</th>
                        <th>%</th>
                        <th>Grade</th>
                      </tr>
                    </thead>
                    <tbody>
                      {gradesController.rows.map((row) => {
                        const student = row.student;
                        if (!student) return null;
                        const marks = Number(marksByStudent[student.id] ?? 0);
                        const pct = maxMarks > 0 ? Math.round((marks / maxMarks) * 10000) / 100 : 0;
                        return (
                          <tr key={student.id}>
                            <td>{student.full_name}</td>
                            <td>
                              <input
                                type="number"
                                min="0"
                                max={maxMarks}
                                step="0.01"
                                value={marksByStudent[student.id] ?? ''}
                                onChange={(e) =>
                                  setMarksByStudent((prev) => ({ ...prev, [student.id]: e.target.value }))
                                }
                                required
                              />
                            </td>
                            <td>{Number.isFinite(pct) ? pct : '—'}</td>
                            <td>{Number.isFinite(pct) ? letterGrade(pct) : '—'}</td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
                <div className="actions" style={{ marginTop: '1rem' }}>
                  <button type="submit" className="btn" disabled={gradesController.saving}>
                    {gradesController.saving ? 'Saving…' : 'Save grades'}
                  </button>
                </div>
              </form>
            </>
          )}
        </div>
      ) : null}
    </>
  );
}
