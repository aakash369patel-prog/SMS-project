import { useEffect, useState } from 'react';
import { AuditModel } from '../../models/AuditModel.js';
import { ListFilters } from '../components/ListFilters.jsx';

export function AuditView() {
  const [rows, setRows] = useState([]);
  const [filters, setFilters] = useState({ action: '', auditable_type: '' });

  function load(page = 1) {
    AuditModel.list(page, filters).then((p) => setRows(p.data ?? []));
  }

  useEffect(() => {
    load();
  }, []);

  return (
    <>
      <div className="page-header"><h2>Audit log</h2></div>
      <ListFilters onApply={() => load(1)} onReset={() => { setFilters({ action: '', auditable_type: '' }); load(1); }}>
        <label>Action<input value={filters.action} onChange={(e) => setFilters({ ...filters, action: e.target.value })} placeholder="grade.updated" /></label>
        <label>Model<input value={filters.auditable_type} onChange={(e) => setFilters({ ...filters, auditable_type: e.target.value })} placeholder="ExamGrade" /></label>
      </ListFilters>
      <div className="card table-wrap">
        <table>
          <thead><tr><th>When</th><th>User</th><th>Action</th><th>Model</th><th>Changes</th></tr></thead>
          <tbody>
            {rows.map((r) => (
              <tr key={r.id}>
                <td>{r.created_at}</td>
                <td>{r.user?.name ?? '—'}</td>
                <td>{r.action}</td>
                <td>{r.auditable_type?.split('\\').pop()} #{r.auditable_id}</td>
                <td><code style={{ fontSize: '0.75rem' }}>{JSON.stringify(r.new_values)}</code></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}
