import { useEffect, useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { TeacherModel } from '../../models/TeacherModel.js';
import { Pagination } from '../components/Pagination.jsx';

const emptyForm = {
  name: '',
  section: '',
  academic_year: '2025-2026',
  capacity: '',
  class_teacher_id: '',
};

export function ClassesView() {
  const controller = useResourceController(SchoolClassModel);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);
  const [teachers, setTeachers] = useState([]);

  useEffect(() => {
    TeacherModel.list(1).then((payload) => setTeachers(payload.data ?? []));
  }, []);

  function startEdit(item) {
    setEditingId(item.id);
    setForm({
      name: item.name ?? '',
      section: item.section ?? '',
      academic_year: item.academic_year ?? '',
      capacity: item.capacity ?? '',
      class_teacher_id: item.class_teacher_id ?? '',
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
      section: form.section || null,
      capacity: form.capacity ? Number(form.capacity) : null,
      class_teacher_id: form.class_teacher_id ? Number(form.class_teacher_id) : null,
    };
    const ok = await controller.save(payload, editingId);
    if (ok) resetForm();
  }

  return (
    <>
      <div className="page-header">
        <h2>Classes</h2>
      </div>
      {controller.error ? <div className="alert error">{controller.error}</div> : null}
      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>{editingId ? 'Edit class' : 'Add class'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              Name
              <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
            </label>
            <label>
              Section
              <input value={form.section} onChange={(e) => setForm({ ...form, section: e.target.value })} />
            </label>
            <label>
              Academic year
              <input value={form.academic_year} onChange={(e) => setForm({ ...form, academic_year: e.target.value })} required />
            </label>
            <label>
              Capacity
              <input type="number" min="1" value={form.capacity} onChange={(e) => setForm({ ...form, capacity: e.target.value })} />
            </label>
            <label>
              Class teacher
              <select value={form.class_teacher_id} onChange={(e) => setForm({ ...form, class_teacher_id: e.target.value })}>
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
                  <th>Class</th>
                  <th>Year</th>
                  <th>Teacher</th>
                  <th>Students</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                {controller.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.label}</td>
                    <td>{item.academic_year}</td>
                    <td>{item.class_teacher?.full_name ?? '—'}</td>
                    <td>{item.students_count ?? '—'}</td>
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
