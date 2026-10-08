import { useEffect, useState } from 'react';
import { ParentPortalModel } from '../../models/ParentPortalModel.js';

export function ParentMessagesView() {
  const [threads, setThreads] = useState([]);
  const [active, setActive] = useState(null);
  const [detail, setDetail] = useState(null);
  const [reply, setReply] = useState('');

  function load() {
    ParentPortalModel.messages().then((r) => setThreads(r.data ?? []));
  }

  useEffect(() => {
    load();
  }, []);

  async function openThread(t) {
    setActive(t);
    const r = await ParentPortalModel.messageThread(t.id);
    setDetail(r.data);
  }

  return (
    <>
      <div className="page-header"><h2>Messages</h2></div>
      <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
        <table>
          <thead><tr><th>Subject</th><th /></tr></thead>
          <tbody>
            {threads.map((t) => (
              <tr key={t.id}>
                <td>{t.subject}</td>
                <td><button type="button" className="btn secondary" onClick={() => openThread(t)}>Read</button></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {detail ? (
        <div className="card">
          <h3 style={{ marginTop: 0 }}>{detail.thread?.subject}</h3>
          <ul style={{ listStyle: 'none', padding: 0 }}>
            {(detail.messages ?? []).map((m) => (
              <li key={m.id} style={{ marginBottom: '0.5rem' }}>
                <strong>{m.sender?.name}</strong>
                <div>{m.body}</div>
              </li>
            ))}
          </ul>
          <form
            onSubmit={async (e) => {
              e.preventDefault();
              await ParentPortalModel.replyMessage(active.id, reply);
              setReply('');
              openThread(active);
            }}
          >
            <textarea value={reply} onChange={(e) => setReply(e.target.value)} rows={2} placeholder="Reply to school…" required />
            <button type="submit" className="btn" style={{ marginTop: '0.5rem' }}>Send reply</button>
          </form>
        </div>
      ) : null}
    </>
  );
}
