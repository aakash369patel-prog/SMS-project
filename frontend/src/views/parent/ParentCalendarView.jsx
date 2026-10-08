import { useEffect, useState } from 'react';
import { ParentPortalModel } from '../../models/ParentPortalModel.js';

export function ParentCalendarView() {
  const [events, setEvents] = useState([]);

  useEffect(() => {
    const start = new Date();
    start.setDate(1);
    const end = new Date(start.getFullYear(), start.getMonth() + 1, 0);
    ParentPortalModel.calendar(start.toISOString().slice(0, 10), end.toISOString().slice(0, 10)).then((r) =>
      setEvents(r.data ?? [])
    );
  }, []);

  return (
    <>
      <div className="page-header"><h2>School calendar</h2></div>
      <div className="card table-wrap">
        <table>
          <thead><tr><th>Date</th><th>Title</th><th>Type</th></tr></thead>
          <tbody>
            {events.map((ev) => (
              <tr key={ev.id}>
                <td>{String(ev.starts_at).slice(0, 10)}</td>
                <td>{ev.title}</td>
                <td>{ev.event_type}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}
