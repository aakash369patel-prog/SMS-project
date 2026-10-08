import { useEffect, useState } from 'react';
import { ReportModel } from '../../models/ReportModel.js';
import { StudentModel } from '../../models/StudentModel.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { StudentFeeModel } from '../../models/StudentFeeModel.js';
import { usePermissions } from '../../controllers/usePermissions.js';

export function ReportsView() {
  const { can } = usePermissions();
  const [students, setStudents] = useState([]);
  const [classes, setClasses] = useState([]);
  const [fees, setFees] = useState([]);
  const [studentId, setStudentId] = useState('');
  const [classId, setClassId] = useState('');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [feeId, setFeeId] = useState('');
  const [error, setError] = useState(null);

  useEffect(() => {
    StudentModel.list(1).then((p) => setStudents(p.data ?? []));
    SchoolClassModel.list(1).then((p) => setClasses(p.data ?? []));
    StudentFeeModel.list(1).then((p) => setFees(p.data ?? []));
  }, []);

  async function run(action) {
    setError(null);
    try {
      await action();
    } catch {
      setError('Download failed. Check permissions and selections.');
    }
  }

  if (!can('reports.view')) {
    return <div className="alert error">You do not have access to reports.</div>;
  }

  return (
    <>
      <div className="page-header"><h2>Reports &amp; exports</h2></div>
      {error ? <div className="alert error">{error}</div> : null}

      <div className="card" style={{ marginBottom: '1rem' }}>
        <h3 style={{ marginTop: 0 }}>PDF reports</h3>
        <div className="form-grid">
          <label>Report card — student<select value={studentId} onChange={(e) => setStudentId(e.target.value)}><option value="">—</option>{students.map((s) => <option key={s.id} value={s.id}>{s.full_name}</option>)}</select></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="button" className="btn" disabled={!studentId} onClick={() => run(() => ReportModel.downloadReportCard(studentId))}>Download PDF</button></div>
        </div>
        <div className="form-grid" style={{ marginTop: '1rem' }}>
          <label>Class<select value={classId} onChange={(e) => setClassId(e.target.value)}><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.label}</option>)}</select></label>
          <label>From<input type="date" value={from} onChange={(e) => setFrom(e.target.value)} /></label>
          <label>To<input type="date" value={to} onChange={(e) => setTo(e.target.value)} /></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="button" className="btn" disabled={!classId || !from || !to} onClick={() => run(() => ReportModel.downloadAttendanceSheet(classId, from, to))}>Attendance PDF</button></div>
        </div>
        <div className="form-grid" style={{ marginTop: '1rem' }}>
          <label>Fee receipt<select value={feeId} onChange={(e) => setFeeId(e.target.value)}><option value="">—</option>{fees.map((f) => <option key={f.id} value={f.id}>{f.title} — {f.student?.full_name}</option>)}</select></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="button" className="btn" disabled={!feeId} onClick={() => run(() => ReportModel.downloadFeeReceipt(feeId))}>Receipt PDF</button></div>
        </div>
      </div>

      {can('exports.download') ? (
        <div className="card">
          <h3 style={{ marginTop: 0 }}>CSV exports</h3>
          <div className="actions">
            <button type="button" className="btn secondary" onClick={() => run(() => ReportModel.exportStudents())}>Students CSV</button>
            <button type="button" className="btn secondary" onClick={() => run(() => ReportModel.exportFees())}>Fees CSV</button>
            <button type="button" className="btn secondary" onClick={() => run(() => ReportModel.exportAttendance({ school_class_id: classId || undefined, from: from || undefined, to: to || undefined }))}>Attendance CSV</button>
          </div>
        </div>
      ) : null}
    </>
  );
}
