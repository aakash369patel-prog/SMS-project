import { useEffect, useMemo, useState } from 'react';
import { useAttendanceController } from '../../controllers/useAttendanceController.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { AttendanceModel } from '../../models/AttendanceModel.js';
import { ListFilters } from '../components/ListFilters.jsx';
import { Pagination } from '../components/Pagination.jsx';
import { unwrapPaginated } from '../../models/ApiClient.js';

const STATUS_OPTIONS = [
  { value: 'present', label: 'Present' },
  { value: 'absent', label: 'Absent' },
  { value: 'late', label: 'Late' },
  { value: 'excused', label: 'Excused' },
];

function todayIso() {
  return new Date().toISOString().slice(0, 10);
}

export function AttendanceView() {
  const controller = useAttendanceController();
  const [classes, setClasses] = useState([]);
  const [schoolClassId, setSchoolClassId] = useState('');
  const [date, setDate] = useState(todayIso());
  const [statusByStudent, setStatusByStudent] = useState({});
  const [historyFilters, setHistoryFilters] = useState({ q: '', school_class_id: '', status: '', from: '', to: '' });
  const [historyRows, setHistoryRows] = useState([]);
  const [historyMeta, setHistoryMeta] = useState(null);
  const [historyLoading, setHistoryLoading] = useState(false);

  function loadHistory(page = 1) {
    setHistoryLoading(true);
    AttendanceModel.list({ page, ...historyFilters })
      .then((payload) => {
        const { items, meta } = unwrapPaginated(payload);
        setHistoryRows(items);
        setHistoryMeta(meta);
      })
      .finally(() => setHistoryLoading(false));
  }

  useEffect(() => {
    SchoolClassModel.list(1).then((payload) => setClasses(payload.data ?? []));
    loadHistory(1);
  }, []);

  useEffect(() => {
    if (schoolClassId) {
      controller.loadSheet(schoolClassId, date);
    }
  }, [schoolClassId, date, controller.loadSheet]);

  useEffect(() => {
    const initial = {};
    controller.rows.forEach((row) => {
      const id = row.student?.id;
      if (!id) return;
      initial[id] = row.attendance?.status ?? 'present';
    });
    setStatusByStudent(initial);
  }, [controller.rows]);

  const unmarkedCount = useMemo(() => {
    if (!controller.summary) return 0;
    return Math.max(0, (controller.summary.students_in_class ?? 0) - (controller.summary.marked ?? 0));
  }, [controller.summary]);

  function setAll(status) {
    const next = {};
    controller.rows.forEach((row) => {
      if (row.student?.id) {
        next[row.student.id] = status;
      }
    });
    setStatusByStudent(next);
  }

  async function handleSave(event) {
    event.preventDefault();
    if (!schoolClassId) return;
    await controller.saveBulk(schoolClassId, date, statusByStudent);
  }

  return (
    <>
      <div className="page-header">
        <h2>Attendance</h2>
      </div>

      {controller.error ? <div className="alert error">{controller.error}</div> : null}
      {controller.success ? (
        <div className="alert" style={{ background: '#ecfdf5', border: '1px solid #a7f3d0', color: '#065f46' }}>
          {controller.success}
        </div>
      ) : null}

      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <div className="form-grid">
          <label>
            Class
            <select value={schoolClassId} onChange={(e) => setSchoolClassId(e.target.value)}>
              <option value="">Select class</option>
              {classes.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.label ?? `${c.name} ${c.section ?? ''}`}
                </option>
              ))}
            </select>
          </label>
          <label>
            Date
            <input type="date" value={date} onChange={(e) => setDate(e.target.value)} />
          </label>
        </div>

        {controller.summary && schoolClassId ? (
          <div className="grid-stats" style={{ marginTop: '1rem' }}>
            <div className="stat-card">
              <span>Present</span>
              <strong>{controller.summary.by_status?.present ?? 0}</strong>
            </div>
            <div className="stat-card">
              <span>Absent</span>
              <strong>{controller.summary.by_status?.absent ?? 0}</strong>
            </div>
            <div className="stat-card">
              <span>Late</span>
              <strong>{controller.summary.by_status?.late ?? 0}</strong>
            </div>
            <div className="stat-card">
              <span>Not marked</span>
              <strong>{unmarkedCount}</strong>
            </div>
          </div>
        ) : null}
      </div>

      {!schoolClassId ? (
        <p style={{ color: 'var(--muted)' }}>Select a class and date to mark attendance.</p>
      ) : controller.loading ? (
        <p>Loading roster…</p>
      ) : controller.rows.length === 0 ? (
        <div className="card">
          <p>No students in {controller.schoolClass?.label ?? 'this class'}.</p>
        </div>
      ) : (
        <form onSubmit={handleSave}>
          <div className="card table-wrap">
            <div className="actions" style={{ marginBottom: '1rem' }}>
              <button type="button" className="btn secondary" onClick={() => setAll('present')}>
                Mark all present
              </button>
              <button type="button" className="btn secondary" onClick={() => setAll('absent')}>
                Mark all absent
              </button>
            </div>
            <table>
              <thead>
                <tr>
                  <th>Admission #</th>
                  <th>Student</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {controller.rows.map((row) => {
                  const student = row.student;
                  if (!student) return null;
                  return (
                    <tr key={student.id}>
                      <td>{student.admission_number}</td>
                      <td>{student.full_name}</td>
                      <td>
                        <select
                          value={statusByStudent[student.id] ?? 'present'}
                          onChange={(e) =>
                            setStatusByStudent((prev) => ({ ...prev, [student.id]: e.target.value }))
                          }
                        >
                          {STATUS_OPTIONS.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                              {opt.label}
                            </option>
                          ))}
                        </select>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          <div className="actions" style={{ marginTop: '1rem' }}>
            <button type="submit" className="btn" disabled={controller.saving}>
              {controller.saving ? 'Saving…' : 'Save attendance'}
            </button>
          </div>
        </form>
      )}

      <div className="page-header" style={{ marginTop: '2rem' }}>
        <h2 style={{ fontSize: '1.25rem', margin: 0 }}>Attendance history</h2>
      </div>
      <ListFilters
        onApply={() => loadHistory(1)}
        onReset={() => {
          setHistoryFilters({ q: '', school_class_id: '', status: '', from: '', to: '' });
          loadHistory(1);
        }}
      >
        <label>Search student<input value={historyFilters.q} onChange={(e) => setHistoryFilters({ ...historyFilters, q: e.target.value })} /></label>
        <label>Class<select value={historyFilters.school_class_id} onChange={(e) => setHistoryFilters({ ...historyFilters, school_class_id: e.target.value })}><option value="">All</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.label ?? c.name}</option>)}</select></label>
        <label>Status<select value={historyFilters.status} onChange={(e) => setHistoryFilters({ ...historyFilters, status: e.target.value })}><option value="">All</option>{STATUS_OPTIONS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}</select></label>
        <label>From<input type="date" value={historyFilters.from} onChange={(e) => setHistoryFilters({ ...historyFilters, from: e.target.value })} /></label>
        <label>To<input type="date" value={historyFilters.to} onChange={(e) => setHistoryFilters({ ...historyFilters, to: e.target.value })} /></label>
      </ListFilters>
      <div className="card table-wrap">
        {historyLoading ? <p>Loading history…</p> : (
          <>
            <table>
              <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Status</th></tr></thead>
              <tbody>
                {historyRows.map((row) => (
                  <tr key={row.id}>
                    <td>{row.attendance_date}</td>
                    <td>{row.student?.full_name}</td>
                    <td>{row.school_class?.label ?? row.school_class?.name}</td>
                    <td>{row.status}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            <Pagination meta={historyMeta} onPageChange={(p) => loadHistory(p)} />
          </>
        )}
      </div>
    </>
  );
}
