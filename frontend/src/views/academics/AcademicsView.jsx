import { useEffect, useState } from 'react';
import { AcademicsModel } from '../../models/AcademicsModel.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { SubjectModel } from '../../models/SubjectModel.js';
import { StudentModel } from '../../models/StudentModel.js';
import { usePermissions } from '../../controllers/usePermissions.js';

const TABS = ['Homework', 'Quizzes', 'Promotion', 'Syllabus'];

export function AcademicsView() {
  const { can } = usePermissions();
  const [tab, setTab] = useState('Homework');
  const [classes, setClasses] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [students, setStudents] = useState([]);
  const [assignments, setAssignments] = useState([]);
  const [selectedAssignment, setSelectedAssignment] = useState(null);
  const [submissions, setSubmissions] = useState([]);
  const [quizzes, setQuizzes] = useState([]);
  const [quizDetail, setQuizDetail] = useState(null);
  const [attempts, setAttempts] = useState([]);
  const [syllabi, setSyllabi] = useState([]);
  const [lessonPlans, setLessonPlans] = useState([]);
  const [promotions, setPromotions] = useState([]);

  const [hwForm, setHwForm] = useState({
    title: '',
    description: '',
    school_class_id: '',
    subject_id: '',
    due_date: '',
    publish: true,
  });
  const [quizForm, setQuizForm] = useState({
    title: '',
    school_class_id: '',
    subject_id: '',
    duration_minutes: '15',
    q1: 'Sample MCQ?',
    q1a: 'A',
    q1b: 'B',
    q1correct: 'A',
  });
  const [quizTake, setQuizTake] = useState({ quizId: '', studentId: '', attemptId: '', answers: {} });
  const [syllabusForm, setSyllabusForm] = useState({
    school_class_id: '',
    subject_id: '',
    academic_year: '2025-2026',
    title: '',
    overview: '',
  });
  const [lessonForm, setLessonForm] = useState({
    school_class_id: '',
    subject_id: '',
    title: '',
    planned_date: '',
    objectives: '',
    content: '',
  });
  const [promoteForm, setPromoteForm] = useState({
    from_class_id: '',
    to_class_id: '',
    academic_year: '2025-2026',
  });

  function loadHomework() {
    AcademicsModel.listAssignments().then((p) => setAssignments(p.data ?? []));
  }
  function loadQuizzes() {
    AcademicsModel.listQuizzes().then((p) => setQuizzes(p.data ?? []));
  }
  function loadSyllabus() {
    AcademicsModel.listSyllabi().then((p) => setSyllabi(p.data ?? []));
    AcademicsModel.listLessonPlans().then((p) => setLessonPlans(p.data ?? []));
  }
  function loadPromotions() {
    AcademicsModel.promotionHistory().then((p) => setPromotions(p.data ?? []));
  }

  useEffect(() => {
    SchoolClassModel.list(1).then((p) => setClasses(p.data ?? []));
    SubjectModel.list(1).then((p) => setSubjects(p.data ?? []));
    StudentModel.list(1).then((p) => setStudents(p.data ?? []));
    loadHomework();
    loadQuizzes();
    loadSyllabus();
    loadPromotions();
  }, []);

  async function createHomework(e) {
    e.preventDefault();
    await AcademicsModel.createAssignment({
      ...hwForm,
      school_class_id: Number(hwForm.school_class_id),
      subject_id: Number(hwForm.subject_id),
      publish: hwForm.publish,
    });
    setHwForm({ title: '', description: '', school_class_id: '', subject_id: '', due_date: '', publish: true });
    loadHomework();
  }

  async function openSubmissions(a) {
    setSelectedAssignment(a);
    const res = await AcademicsModel.assignmentSubmissions(a.id);
    setSubmissions(res.data ?? []);
  }

  async function markSubmitted(sub, status) {
    await AcademicsModel.updateSubmission(selectedAssignment.id, sub.id, {
      status,
      content: sub.content || 'Submitted in class',
    });
    openSubmissions(selectedAssignment);
  }

  async function createQuiz(e) {
    e.preventDefault();
    await AcademicsModel.createQuiz({
      title: quizForm.title,
      school_class_id: Number(quizForm.school_class_id),
      subject_id: Number(quizForm.subject_id),
      duration_minutes: Number(quizForm.duration_minutes),
      is_published: true,
      starts_at: new Date().toISOString(),
      ends_at: new Date(Date.now() + 7 * 86400000).toISOString(),
      questions: [
        {
          question_text: quizForm.q1,
          question_type: 'mcq',
          options: [quizForm.q1a, quizForm.q1b],
          correct_answer: quizForm.q1correct,
          marks: 1,
        },
      ],
    });
    setQuizForm({
      title: '',
      school_class_id: '',
      subject_id: '',
      duration_minutes: '15',
      q1: 'Sample MCQ?',
      q1a: 'A',
      q1b: 'B',
      q1correct: 'A',
    });
    loadQuizzes();
  }

  async function openQuiz(q) {
    const res = await AcademicsModel.getQuiz(q.id);
    setQuizDetail(res.data);
    const att = await AcademicsModel.quizAttempts(q.id);
    setAttempts(att.data ?? []);
    setQuizTake({ quizId: q.id, studentId: '', attemptId: '', answers: {} });
  }

  async function startQuizAttempt() {
    const res = await AcademicsModel.startQuiz(Number(quizTake.quizId), Number(quizTake.studentId));
    setQuizTake((t) => ({ ...t, attemptId: res.data.attempt.id }));
    setQuizDetail(res.data.quiz);
  }

  async function submitQuizAttempt() {
    const answers = Object.entries(quizTake.answers).map(([question_id, answer]) => ({
      question_id: Number(question_id),
      answer,
    }));
    await AcademicsModel.submitQuiz(Number(quizTake.quizId), Number(quizTake.attemptId), answers);
    openQuiz({ id: quizTake.quizId });
    loadQuizzes();
  }

  async function createSyllabus(e) {
    e.preventDefault();
    await AcademicsModel.createSyllabus({
      ...syllabusForm,
      school_class_id: Number(syllabusForm.school_class_id),
      subject_id: Number(syllabusForm.subject_id),
    });
    setSyllabusForm({ school_class_id: '', subject_id: '', academic_year: '2025-2026', title: '', overview: '' });
    loadSyllabus();
  }

  async function createLesson(e) {
    e.preventDefault();
    await AcademicsModel.createLessonPlan({
      ...lessonForm,
      school_class_id: Number(lessonForm.school_class_id),
      subject_id: Number(lessonForm.subject_id),
      status: 'published',
    });
    setLessonForm({ school_class_id: '', subject_id: '', title: '', planned_date: '', objectives: '', content: '' });
    loadSyllabus();
  }

  async function bulkPromote(e) {
    e.preventDefault();
    await AcademicsModel.bulkPromote({
      from_class_id: Number(promoteForm.from_class_id),
      to_class_id: Number(promoteForm.to_class_id),
      academic_year: promoteForm.academic_year,
    });
    loadPromotions();
    StudentModel.list(1).then((p) => setStudents(p.data ?? []));
  }

  const manage = can('academics.manage');
  const promote = can('promotion.manage');

  return (
    <>
      <div className="page-header">
        <h2>Academics</h2>
        <div className="tab-row" style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
          {TABS.map((t) => (
            <button
              key={t}
              type="button"
              className={tab === t ? 'btn' : 'btn secondary'}
              onClick={() => setTab(t)}
            >
              {t}
            </button>
          ))}
        </div>
      </div>

      {tab === 'Homework' ? (
        <>
          {manage ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <h3 style={{ marginTop: 0 }}>Publish homework</h3>
              <form onSubmit={createHomework} className="form-grid">
                <label>Title<input value={hwForm.title} onChange={(e) => setHwForm({ ...hwForm, title: e.target.value })} required /></label>
                <label>Class<select value={hwForm.school_class_id} onChange={(e) => setHwForm({ ...hwForm, school_class_id: e.target.value })} required><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.name} {c.section}</option>)}</select></label>
                <label>Subject<select value={hwForm.subject_id} onChange={(e) => setHwForm({ ...hwForm, subject_id: e.target.value })} required><option value="">—</option>{subjects.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}</select></label>
                <label>Due date<input type="date" value={hwForm.due_date} onChange={(e) => setHwForm({ ...hwForm, due_date: e.target.value })} required /></label>
                <label style={{ gridColumn: '1 / -1' }}>Description<textarea value={hwForm.description} onChange={(e) => setHwForm({ ...hwForm, description: e.target.value })} rows={2} /></label>
                <div className="actions"><button type="submit" className="btn">Publish</button></div>
              </form>
            </div>
          ) : null}
          <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Assignments</h3>
            <table>
              <thead><tr><th>Title</th><th>Class</th><th>Due</th><th>Submissions</th><th /></tr></thead>
              <tbody>
                {assignments.map((a) => (
                  <tr key={a.id}>
                    <td>{a.title}</td>
                    <td>{a.school_class?.name}</td>
                    <td>{a.due_date}</td>
                    <td>{a.submissions?.length ?? 0}</td>
                    <td><button type="button" className="btn secondary" onClick={() => openSubmissions(a)}>Status</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {selectedAssignment ? (
            <div className="card table-wrap">
              <h3 style={{ marginTop: 0 }}>Submissions — {selectedAssignment.title}</h3>
              <table>
                <thead><tr><th>Student</th><th>Status</th><th>Submitted</th>{manage ? <th /> : null}</tr></thead>
                <tbody>
                  {submissions.map((s) => (
                    <tr key={s.id}>
                      <td>{s.student?.full_name ?? s.student_id}</td>
                      <td>{s.status}</td>
                      <td>{s.submitted_at ?? '—'}</td>
                      {manage ? (
                        <td>
                          {s.status === 'pending' ? (
                            <button type="button" className="btn secondary" onClick={() => markSubmitted(s, 'submitted')}>Mark submitted</button>
                          ) : null}
                        </td>
                      ) : null}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : null}
        </>
      ) : null}

      {tab === 'Quizzes' ? (
        <>
          {manage ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <h3 style={{ marginTop: 0 }}>Create timed quiz (MCQ auto-mark)</h3>
              <form onSubmit={createQuiz} className="form-grid">
                <label>Title<input value={quizForm.title} onChange={(e) => setQuizForm({ ...quizForm, title: e.target.value })} required /></label>
                <label>Class<select value={quizForm.school_class_id} onChange={(e) => setQuizForm({ ...quizForm, school_class_id: e.target.value })} required><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}</select></label>
                <label>Subject<select value={quizForm.subject_id} onChange={(e) => setQuizForm({ ...quizForm, subject_id: e.target.value })} required><option value="">—</option>{subjects.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}</select></label>
                <label>Minutes<input type="number" min="5" value={quizForm.duration_minutes} onChange={(e) => setQuizForm({ ...quizForm, duration_minutes: e.target.value })} /></label>
                <label style={{ gridColumn: '1 / -1' }}>Question<input value={quizForm.q1} onChange={(e) => setQuizForm({ ...quizForm, q1: e.target.value })} /></label>
                <label>Option A<input value={quizForm.q1a} onChange={(e) => setQuizForm({ ...quizForm, q1a: e.target.value })} /></label>
                <label>Option B<input value={quizForm.q1b} onChange={(e) => setQuizForm({ ...quizForm, q1b: e.target.value })} /></label>
                <label>Correct answer<input value={quizForm.q1correct} onChange={(e) => setQuizForm({ ...quizForm, q1correct: e.target.value })} /></label>
                <div className="actions"><button type="submit" className="btn">Create & publish</button></div>
              </form>
            </div>
          ) : null}
          <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Quizzes</h3>
            <table>
              <thead><tr><th>Title</th><th>Class</th><th>Duration</th><th>Max score</th><th /></tr></thead>
              <tbody>
                {quizzes.map((q) => (
                  <tr key={q.id}>
                    <td>{q.title}</td>
                    <td>{q.school_class?.name}</td>
                    <td>{q.duration_minutes} min</td>
                    <td>{q.max_score}</td>
                    <td><button type="button" className="btn secondary" onClick={() => openQuiz(q)}>Open</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {quizDetail && manage ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <h3 style={{ marginTop: 0 }}>Take quiz (demo — select student)</h3>
              <div className="form-grid">
                <label>Student<select value={quizTake.studentId} onChange={(e) => setQuizTake({ ...quizTake, studentId: e.target.value })}><option value="">—</option>{students.map((s) => <option key={s.id} value={s.id}>{s.full_name}</option>)}</select></label>
                <div className="actions" style={{ alignSelf: 'end' }}><button type="button" className="btn secondary" onClick={startQuizAttempt} disabled={!quizTake.studentId}>Start</button></div>
              </div>
              {quizTake.attemptId && quizDetail.questions ? (
                <>
                  {quizDetail.questions.map((qu) => (
                    <div key={qu.id} style={{ marginTop: '0.75rem' }}>
                      <strong>{qu.question_text}</strong>
                      <div>
                        {(qu.options ?? []).map((opt) => (
                          <label key={opt} style={{ display: 'block' }}>
                            <input
                              type="radio"
                              name={`q-${qu.id}`}
                              checked={quizTake.answers[qu.id] === opt}
                              onChange={() => setQuizTake((t) => ({ ...t, answers: { ...t.answers, [qu.id]: opt } }))}
                            />
                            {' '}{opt}
                          </label>
                        ))}
                      </div>
                    </div>
                  ))}
                  <button type="button" className="btn" style={{ marginTop: '1rem' }} onClick={submitQuizAttempt}>Submit (auto-mark MCQ)</button>
                </>
              ) : null}
              <h4>Attempts</h4>
              <ul>{attempts.map((a) => <li key={a.id}>{a.student?.full_name}: {a.status} — score {a.score ?? '—'}</li>)}</ul>
            </div>
          ) : null}
        </>
      ) : null}

      {tab === 'Promotion' ? (
        <>
          {promote ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <h3 style={{ marginTop: 0 }}>Bulk promotion</h3>
              <form onSubmit={bulkPromote} className="form-grid">
                <label>From class<select value={promoteForm.from_class_id} onChange={(e) => setPromoteForm({ ...promoteForm, from_class_id: e.target.value })} required><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.name} {c.section}</option>)}</select></label>
                <label>To class<select value={promoteForm.to_class_id} onChange={(e) => setPromoteForm({ ...promoteForm, to_class_id: e.target.value })} required><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.name} {c.section}</option>)}</select></label>
                <label>Academic year<input value={promoteForm.academic_year} onChange={(e) => setPromoteForm({ ...promoteForm, academic_year: e.target.value })} required /></label>
                <div className="actions"><button type="submit" className="btn">Promote all in class</button></div>
              </form>
            </div>
          ) : (
            <p className="muted">Promotion requires office staff or admin permissions.</p>
          )}
          <div className="card table-wrap">
            <h3 style={{ marginTop: 0 }}>Promotion history</h3>
            <table>
              <thead><tr><th>Student</th><th>From</th><th>To</th><th>Year</th><th>Date</th></tr></thead>
              <tbody>
                {promotions.map((p) => (
                  <tr key={p.id}>
                    <td>{p.student?.full_name}</td>
                    <td>{p.from_class?.name}</td>
                    <td>{p.to_class?.name}</td>
                    <td>{p.academic_year}</td>
                    <td>{p.promoted_at}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      ) : null}

      {tab === 'Syllabus' ? (
        <>
          {manage ? (
            <>
              <div className="card" style={{ marginBottom: '1rem' }}>
                <h3 style={{ marginTop: 0 }}>Syllabus</h3>
                <form onSubmit={createSyllabus} className="form-grid">
                  <label>Class<select value={syllabusForm.school_class_id} onChange={(e) => setSyllabusForm({ ...syllabusForm, school_class_id: e.target.value })} required><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}</select></label>
                  <label>Subject<select value={syllabusForm.subject_id} onChange={(e) => setSyllabusForm({ ...syllabusForm, subject_id: e.target.value })} required><option value="">—</option>{subjects.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}</select></label>
                  <label>Title<input value={syllabusForm.title} onChange={(e) => setSyllabusForm({ ...syllabusForm, title: e.target.value })} required /></label>
                  <label style={{ gridColumn: '1 / -1' }}>Overview<textarea value={syllabusForm.overview} onChange={(e) => setSyllabusForm({ ...syllabusForm, overview: e.target.value })} rows={2} /></label>
                  <div className="actions"><button type="submit" className="btn">Save syllabus</button></div>
                </form>
              </div>
              <div className="card" style={{ marginBottom: '1rem' }}>
                <h3 style={{ marginTop: 0 }}>Lesson plan</h3>
                <form onSubmit={createLesson} className="form-grid">
                  <label>Class<select value={lessonForm.school_class_id} onChange={(e) => setLessonForm({ ...lessonForm, school_class_id: e.target.value })} required><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}</select></label>
                  <label>Subject<select value={lessonForm.subject_id} onChange={(e) => setLessonForm({ ...lessonForm, subject_id: e.target.value })} required><option value="">—</option>{subjects.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}</select></label>
                  <label>Title<input value={lessonForm.title} onChange={(e) => setLessonForm({ ...lessonForm, title: e.target.value })} required /></label>
                  <label>Date<input type="date" value={lessonForm.planned_date} onChange={(e) => setLessonForm({ ...lessonForm, planned_date: e.target.value })} /></label>
                  <label style={{ gridColumn: '1 / -1' }}>Objectives<textarea value={lessonForm.objectives} onChange={(e) => setLessonForm({ ...lessonForm, objectives: e.target.value })} rows={2} /></label>
                  <div className="actions"><button type="submit" className="btn">Add lesson plan</button></div>
                </form>
              </div>
            </>
          ) : null}
          <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Syllabi</h3>
            <table>
              <thead><tr><th>Title</th><th>Class</th><th>Subject</th><th>Year</th></tr></thead>
              <tbody>{syllabi.map((s) => <tr key={s.id}><td>{s.title}</td><td>{s.school_class?.name}</td><td>{s.subject?.name}</td><td>{s.academic_year}</td></tr>)}</tbody>
            </table>
          </div>
          <div className="card table-wrap">
            <h3 style={{ marginTop: 0 }}>Lesson plans</h3>
            <table>
              <thead><tr><th>Title</th><th>Class</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>{lessonPlans.map((l) => <tr key={l.id}><td>{l.title}</td><td>{l.school_class?.name}</td><td>{l.planned_date}</td><td>{l.status}</td></tr>)}</tbody>
            </table>
          </div>
        </>
      ) : null}
    </>
  );
}
