import { useEffect, useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { AnnouncementModel } from '../../models/AnnouncementModel.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { Pagination } from '../components/Pagination.jsx';
import { UploadModel } from '../../models/UploadModel.js';

const emptyForm = {
  title: '',
  body: '',
  audience: 'all',
  school_class_id: '',
  is_published: true,
};

const AUDIENCES = [
  { value: 'all', label: 'Everyone' },
  { value: 'parents', label: 'Parents' },
  { value: 'students', label: 'Students' },
  { value: 'teachers', label: 'Teachers' },
  { value: 'staff', label: 'Staff' },
];

export function AnnouncementsView() {
  const controller = useResourceController(AnnouncementModel);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);
  const [classes, setClasses] = useState([]);

  useEffect(() => {
    SchoolClassModel.list(1).then((payload) => setClasses(payload.data ?? []));
  }, []);

  function startEdit(item) {
    setEditingId(item.id);
    setForm({
      title: item.title ?? '',
      body: item.body ?? '',
      audience: item.audience ?? 'all',
      school_class_id: item.school_class_id ? String(item.school_class_id) : '',
      is_published: !!item.is_published,
    });
  }

  function resetForm() {
    setEditingId(null);
    setForm(emptyForm);
  }

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      school_class_id: form.school_class_id ? Number(form.school_class_id) : null,
      is_published: !!form.is_published,
    };
    const ok = await controller.save(payload, editingId);
    if (ok) resetForm();
  }

  return (
    <>
      <div className="page-header">
        <h2>Announcements &amp; notices</h2>
      </div>
      {controller.error ? <div className="alert error">{controller.error}</div> : null}

      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>{editingId ? 'Edit notice' : 'Publish notice'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              Title
              <input value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
            </label>
            <label>
              Audience
              <select value={form.audience} onChange={(e) => setForm({ ...form, audience: e.target.value })}>
                {AUDIENCES.map((a) => (
                  <option key={a.value} value={a.value}>{a.label}</option>
                ))}
              </select>
            </label>
            <label>
              Class (optional)
              <select value={form.school_class_id} onChange={(e) => setForm({ ...form, school_class_id: e.target.value })}>
                <option value="">All classes</option>
                {classes.map((c) => (
                  <option key={c.id} value={c.id}>{c.label}</option>
                ))}
              </select>
            </label>
          </div>
          <label style={{ display: 'block', marginBottom: '1rem' }}>
            Message
            <textarea rows={4} value={form.body} onChange={(e) => setForm({ ...form, body: e.target.value })} required style={{ width: '100%', marginTop: '0.35rem' }} />
          </label>
          <label style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '1rem' }}>
            <input type="checkbox" checked={form.is_published} onChange={(e) => setForm({ ...form, is_published: e.target.checked })} />
            Published
          </label>
          <div className="actions">
            <button type="submit" className="btn">{editingId ? 'Update' : 'Publish'}</button>
            {editingId ? <button type="button" className="btn secondary" onClick={resetForm}>Cancel</button> : null}
          </div>
        </form>
      </div>

      <div className="card table-wrap">
        {controller.loading ? <p>Loading…</p> : (
          <>
            <table>
              <thead>
                <tr><th>Title</th><th>Audience</th><th>Attachment</th><th>Class</th><th>Status</th><th /></tr>
              </thead>
              <tbody>
                {controller.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.title}</td>
                    <td>{item.audience}</td>
                    <td>
                      {item.attachment_url ? <a href={item.attachment_url} target="_blank" rel="noreferrer">{item.attachment_name ?? 'File'}</a> : '—'}
                      <input type="file" style={{ display: 'block', fontSize: '0.7rem', marginTop: 4 }} onChange={(e) => {
                        const f = e.target.files?.[0];
                        if (f) UploadModel.announcementAttachment(item.id, f).then(() => controller.load(controller.page));
                      }} />
                    </td>
                    <td>{item.school_class?.label ?? 'All'}</td>
                    <td>{item.is_published ? 'Published' : 'Draft'}</td>
                    <td>
                      <div className="actions">
                        <button type="button" className="btn secondary" onClick={() => startEdit(item)}>Edit</button>
                        <button type="button" className="btn danger" onClick={() => controller.remove(item.id)}>Delete</button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <Pagination meta={controller.meta} onPageChange={(p) => controller.load(p)} />
          </>
        )}
      </div>
    </>
  );
}
