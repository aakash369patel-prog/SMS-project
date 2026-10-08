import { useEffect, useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { SubjectModel } from '../../models/SubjectModel.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { TeacherModel } from '../../models/TeacherModel.js';
import { Pagination } from '../components/Pagination.jsx';

const emptyForm = {
  name: '',
  code: '',
  school_class_id: '',
  teacher_id: '',
};

export function SubjectsView() {
  const controller = useResourceController(SubjectModel);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);
  const [classes, setClasses] = useState([]);
  const [teachers, setTeachers] = useState([]);

  useEffect(() => {
    SchoolClassModel.list(1).then((payload) => setClasses(payload.data ?? []));
    TeacherModel.list(1).then((payload) => setTeachers(payload.data ?? []));
  }, []);

  function startEdit(item) {
    setEditingId(item.id);
    setForm({
      name: item.name ?? '',
      code: item.code ?? '',
      school_class_id: item.school_class_id ?? '',
      teacher_id: item.teacher_id ?? '',
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
      teacher_id: form.teacher_id ? Number(form.teacher_id) : null,
    };
    const ok = await controller.save(payload, editingId);
    if (ok) resetForm();
  }

  return (
    <>
      <div className="page-header">
        <h2>Subjects</h2>
      </div>
      {controller.error ? <div className="alert error">{controller.error}</div> : null}
      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>{editingId ? 'Edit subject' : 'Add subject'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              Name
              <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
            </label>
            <label>
              Code
              <input value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} required />
            </label>
            <label>
              Class
              <select value={form.school_class_id} onChange={(e) => setForm({ ...form, school_class_id: e.target.value })}>
                <option value="">—</option>
                {classes.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.label}
                  </option>
                ))}
              </select>
            </label>
            <label>
              Teacher
              <select value={form.teacher_id} onChange={(e) => setForm({ ...form, teacher_id: e.target.value })}>
                <option value="">—</option>
                {teachers.map((t) => (
                  <option key={t.id} value={t.id}>
                    {t.full_name}
                  </option>
                ))}
              </select>
            </label>
          </div>
          <div className="actions">
            <button type="submit" className="btn">
              {editingId ? 'Update' : 'Create'}
            </button>
            {editingId ? (
              <button type="button" className="btn secondary" onClick={resetForm}>
                Cancel
              </button>
            ) : null}
          </div>
        </form>
      </div>
      <div className="card table-wrap">
        {controller.loading ? (
          <p>Loading…</p>
        ) : (
          <>
            <table>
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Name</th>
                  <th>Class</th>
                  <th>Teacher</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                {controller.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.code}</td>
                    <td>{item.name}</td>
                    <td>{item.school_class?.label ?? '—'}</td>
                    <td>{item.teacher?.full_name ?? '—'}</td>
                    <td>
                      <div className="actions">
                        <button type="button" className="btn secondary" onClick={() => startEdit(item)}>
                          Edit
                        </button>
                        <button type="button" className="btn danger" onClick={() => controller.remove(item.id)}>
                          Delete
                        </button>
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
