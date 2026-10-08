import { useEffect, useState } from 'react';
import { ParentPortalModel } from '../../models/ParentPortalModel.js';

export function ParentAnnouncementsView() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    ParentPortalModel.announcements()
      .then((payload) => setItems(payload.data ?? []))
      .finally(() => setLoading(false));
  }, []);

  return (
    <>
      <div className="page-header"><h2>School notices</h2></div>
      {loading ? <p>Loading…</p> : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {items.map((item) => (
            <article key={item.id} className="card">
              <h3 style={{ marginTop: 0 }}>{item.title}</h3>
              <p style={{ margin: '0 0 0.5rem', color: 'var(--muted)', fontSize: '0.875rem' }}>
                {item.school_class?.label ? `Class: ${item.school_class.label}` : 'School-wide'}
              </p>
              <p style={{ whiteSpace: 'pre-wrap' }}>{item.body}</p>
            </article>
          ))}
          {items.length === 0 ? <p>No notices at this time.</p> : null}
        </div>
      )}
    </>
  );
}
