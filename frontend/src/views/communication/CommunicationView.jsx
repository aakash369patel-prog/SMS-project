import { useEffect, useState } from 'react';
import { CommunicationModel } from '../../models/CommunicationModel.js';
import { AnnouncementModel } from '../../models/AnnouncementModel.js';
import { ParentModel } from '../../models/ParentModel.js';
import { usePermissions } from '../../controllers/usePermissions.js';

const TABS = ['Notifications', 'Messaging', 'Calendar'];

export function CommunicationView() {
  const { can } = usePermissions();
  const [tab, setTab] = useState('Notifications');
  const [logs, setLogs] = useState([]);
  const [announcements, setAnnouncements] = useState([]);
  const [absenceDate, setAbsenceDate] = useState(new Date().toISOString().slice(0, 10));
  const [includeSms, setIncludeSms] = useState(false);
  const [noticeId, setNoticeId] = useState('');
  const [threads, setThreads] = useState([]);
  const [activeThread, setActiveThread] = useState(null);
  const [threadDetail, setThreadDetail] = useState(null);
  const [parents, setParents] = useState([]);
  const [msgForm, setMsgForm] = useState({ parent_id: '', subject: '', body: '' });
  const [replyBody, setReplyBody] = useState('');
  const [events, setEvents] = useState([]);
  const [eventForm, setEventForm] = useState({
    title: '',
    event_type: 'holiday',
    starts_at: '',
    ends_at: '',
    description: '',
  });

  const monthStart = new Date();
  monthStart.setDate(1);
  const monthEnd = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 0);

  function loadLogs() {
    CommunicationModel.listNotifications().then((p) => setLogs(p.data ?? []));
  }
  function loadThreads() {
    CommunicationModel.listThreads().then((p) => setThreads(p.data ?? []));
  }
  function loadCalendar() {
    CommunicationModel.calendar(monthStart.toISOString().slice(0, 10), monthEnd.toISOString().slice(0, 10)).then(
      (r) => setEvents(r.data ?? [])
    );
  }

  useEffect(() => {
    loadLogs();
    loadThreads();
    loadCalendar();
    AnnouncementModel.list(1).then((p) => setAnnouncements(p.data ?? []));
    ParentModel.list(1).then((p) => setParents(p.data ?? []));
  }, []);

  async function openThread(t) {
    setActiveThread(t);
    const res = await CommunicationModel.getThread(t.id);
    setThreadDetail(res.data);
  }

  return (
    <>
      <div className="page-header">
        <h2>Communication</h2>
        <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
          {TABS.map((t) => (
            <button key={t} type="button" className={tab === t ? 'btn' : 'btn secondary'} onClick={() => setTab(t)}>
              {t}
            </button>
          ))}
        </div>
      </div>

      {tab === 'Notifications' ? (
        <>
          {can('communication.notify') ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <h3 style={{ marginTop: 0 }}>Send alerts</h3>
              <label style={{ display: 'block', marginBottom: '0.75rem' }}>
                <input type="checkbox" checked={includeSms} onChange={(e) => setIncludeSms(e.target.checked)} /> Also log SMS (simulated)
              </label>
              <div className="form-grid">
                <div className="actions">
                  <button type="button" className="btn" onClick={() => CommunicationModel.sendFeeReminders(includeSms).then(loadLogs)}>
                    Fee reminders
                  </button>
                </div>
                <label>
                  Absence date
                  <input type="date" value={absenceDate} onChange={(e) => setAbsenceDate(e.target.value)} />
                </label>
                <div className="actions" style={{ alignSelf: 'end' }}>
                  <button
                    type="button"
                    className="btn secondary"
                    onClick={() => CommunicationModel.sendAbsenceAlerts(absenceDate, includeSms).then(loadLogs)}
                  >
                    Absence alerts
                  </button>
                </div>
                <label>
                  Notice to email
                  <select value={noticeId} onChange={(e) => setNoticeId(e.target.value)}>
                    <option value="">— announcement —</option>
                    {announcements.map((a) => (
                      <option key={a.id} value={a.id}>{a.title}</option>
                    ))}
                  </select>
                </label>
                <div className="actions" style={{ alignSelf: 'end' }}>
                  <button
                    type="button"
                    className="btn secondary"
                    disabled={!noticeId}
                    onClick={() => CommunicationModel.sendAnnouncementNotice(Number(noticeId), includeSms).then(loadLogs)}
                  >
                    Email notice
                  </button>
                </div>
              </div>
              <p className="muted" style={{ marginBottom: 0 }}>Emails use your Laravel mail config (log driver writes to storage/logs).</p>
            </div>
          ) : null}
          <div className="card table-wrap">
            <h3 style={{ marginTop: 0 }}>Notification log</h3>
            <table>
              <thead><tr><th>Channel</th><th>Type</th><th>To</th><th>Subject</th><th>Status</th><th>Sent</th></tr></thead>
              <tbody>
                {logs.map((l) => (
                  <tr key={l.id}>
                    <td>{l.channel}</td>
                    <td>{l.notification_type}</td>
                    <td>{l.recipient_email || l.recipient_phone}</td>
                    <td>{l.subject || '—'}</td>
                    <td>{l.status}</td>
                    <td>{l.sent_at ?? '—'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      ) : null}

      {tab === 'Messaging' ? (
        <>
          {can('communication.messages') ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <h3 style={{ marginTop: 0 }}>New thread (teacher messages need moderation)</h3>
              <form
                onSubmit={async (e) => {
                  e.preventDefault();
                  await CommunicationModel.createThread({
                    parent_id: Number(msgForm.parent_id),
                    subject: msgForm.subject,
                    body: msgForm.body,
                  });
                  setMsgForm({ parent_id: '', subject: '', body: '' });
                  loadThreads();
                }}
                className="form-grid"
              >
                <label>Parent<select value={msgForm.parent_id} onChange={(e) => setMsgForm({ ...msgForm, parent_id: e.target.value })} required><option value="">—</option>{parents.map((p) => <option key={p.id} value={p.id}>{p.full_name}</option>)}</select></label>
                <label>Subject<input value={msgForm.subject} onChange={(e) => setMsgForm({ ...msgForm, subject: e.target.value })} required /></label>
                <label style={{ gridColumn: '1 / -1' }}>Message<textarea value={msgForm.body} onChange={(e) => setMsgForm({ ...msgForm, body: e.target.value })} rows={3} required /></label>
                <div className="actions"><button type="submit" className="btn">Send</button></div>
              </form>
            </div>
          ) : null}
          <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Threads</h3>
            <table>
              <thead><tr><th>Subject</th><th>Parent</th><th /></tr></thead>
              <tbody>
                {threads.map((t) => (
                  <tr key={t.id}>
                    <td>{t.subject}</td>
                    <td>{t.parent_guardian?.full_name ?? t.parent_id}</td>
                    <td><button type="button" className="btn secondary" onClick={() => openThread(t)}>Open</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {threadDetail ? (
            <div className="card">
              <h3 style={{ marginTop: 0 }}>{threadDetail.subject}</h3>
              <ul style={{ listStyle: 'none', padding: 0 }}>
                {(threadDetail.messages ?? []).map((m) => (
                  <li key={m.id} style={{ marginBottom: '0.75rem', padding: '0.5rem', background: '#f4f6f8', borderRadius: 6 }}>
                    <strong>{m.sender?.name}</strong> — {m.moderation_status}
                    <div>{m.body}</div>
                    {can('communication.moderate') && m.moderation_status === 'pending' ? (
                      <div style={{ marginTop: '0.35rem' }}>
                        <button type="button" className="btn" onClick={() => CommunicationModel.moderateMessage(m.id, 'approved').then(() => openThread(activeThread))}>Approve</button>
                        {' '}
                        <button type="button" className="btn secondary" onClick={() => CommunicationModel.moderateMessage(m.id, 'rejected').then(() => openThread(activeThread))}>Reject</button>
                      </div>
                    ) : null}
                  </li>
                ))}
              </ul>
              {can('communication.messages') ? (
                <form
                  onSubmit={async (e) => {
                    e.preventDefault();
                    await CommunicationModel.replyThread(threadDetail.id, replyBody);
                    setReplyBody('');
                    openThread(activeThread);
                  }}
                >
                  <textarea value={replyBody} onChange={(e) => setReplyBody(e.target.value)} rows={2} placeholder="Reply…" required />
                  <button type="submit" className="btn" style={{ marginTop: '0.5rem' }}>Reply</button>
                </form>
              ) : null}
            </div>
          ) : null}
        </>
      ) : null}

      {tab === 'Calendar' ? (
        <>
          {can('communication.calendar') ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <h3 style={{ marginTop: 0 }}>Add event / holiday</h3>
              <form
                onSubmit={async (e) => {
                  e.preventDefault();
                  await CommunicationModel.createEvent({ ...eventForm, all_day: true });
                  setEventForm({ title: '', event_type: 'holiday', starts_at: '', ends_at: '', description: '' });
                  loadCalendar();
                }}
                className="form-grid"
              >
                <label>Title<input value={eventForm.title} onChange={(e) => setEventForm({ ...eventForm, title: e.target.value })} required /></label>
                <label>Type<select value={eventForm.event_type} onChange={(e) => setEventForm({ ...eventForm, event_type: e.target.value })}><option value="holiday">Holiday</option><option value="event">Event</option><option value="exam">Exam (manual)</option></select></label>
                <label>Starts<input type="date" value={eventForm.starts_at} onChange={(e) => setEventForm({ ...eventForm, starts_at: e.target.value })} required /></label>
                <label>Ends<input type="date" value={eventForm.ends_at} onChange={(e) => setEventForm({ ...eventForm, ends_at: e.target.value })} /></label>
                <div className="actions"><button type="submit" className="btn">Save</button></div>
              </form>
            </div>
          ) : null}
          <div className="card table-wrap">
            <h3 style={{ marginTop: 0 }}>This month (includes exam dates from Exams module)</h3>
            <table>
              <thead><tr><th>Date</th><th>Title</th><th>Type</th><th>Source</th></tr></thead>
              <tbody>
                {events.map((ev) => (
                  <tr key={ev.id}>
                    <td>{String(ev.starts_at).slice(0, 10)}</td>
                    <td>{ev.title}</td>
                    <td>{ev.event_type}</td>
                    <td>{ev.source ?? 'calendar'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      ) : null}
    </>
  );
}
