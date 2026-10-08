import { useEffect, useState } from 'react';
import { ParentPortalModel } from '../../models/ParentPortalModel.js';

export function ParentHomeView() {
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    ParentPortalModel.overview()
      .then(setData)
      .catch(() => setError('Could not load parent overview.'));
  }, []);

  if (error) return <div className="alert error">{error}</div>;
  if (!data) return <p>Loading…</p>;

  return (
    <>
      <div className="page-header">
        <h2>Welcome, {data.parent?.full_name}</h2>
      </div>
      <div className="grid-stats" style={{ marginBottom: '1.25rem' }}>
        <div className="stat-card">
          <span>Children</span>
          <strong>{data.children?.length ?? 0}</strong>
        </div>
        <div className="stat-card">
          <span>Fee balance</span>
          <strong>{data.fee_summary?.total_due ?? 0}</strong>
        </div>
        <div className="stat-card">
          <span>Open invoices</span>
          <strong>{data.fee_summary?.open_invoices ?? 0}</strong>
        </div>
      </div>
      <div className="card">
        <h3 style={{ marginTop: 0 }}>Your children</h3>
        <ul>
          {(data.children ?? []).map((child) => (
            <li key={child.id}>
              {child.full_name} — {child.school_class?.label ?? 'No class'} ({child.admission_number})
            </li>
          ))}
        </ul>
      </div>
    </>
  );
}
