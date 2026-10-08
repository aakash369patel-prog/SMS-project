import { useEffect, useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { ParentModel } from '../../models/ParentModel.js';
import { StudentModel } from '../../models/StudentModel.js';
import { Pagination } from '../components/Pagination.jsx';

const emptyForm = {
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  password: '',
  student_id: '',
  relation: 'guardian',
};

export function ParentsView() {
  const controller = useResourceController(ParentModel);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);
  const [students, setStudents] = useState([]);

  useEffect(() => {
    StudentModel.list(1).then((payload) => setStudents(payload.data ?? []));
  }, []);

  function startEdit(item) {
    setEditingId(item.id);
    const linked = item.students?.[0];
    setForm({
      first_name: item.first_name ?? '',
      last_name: item.last_name ?? '',
      email: item.email ?? '',
      phone: item.phone ?? '',
      address: item.address ?? '',
      password: '',
      student_id: linked ? String(linked.id) : '',
      relation: linked?.pivot?.relation ?? 'guardian',
    });
  }

  function resetForm() {
    setEditingId(null);
    setForm(emptyForm);
  }

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email,
      phone: form.phone || null,
      address: form.address || null,
      password: form.password || undefined,
      students: form.student_id
        ? [{ student_id: Number(form.student_id), relation: form.relation }]
        : [],
    };
    const ok = await controller.save(payload, editingId);
    if (ok) resetForm();
  }

  return (
    <>
      <div className="page-header">
        <h2>Parents</h2>
      </div>
      {controller.error ? <div className="alert error">{controller.error}</div> : null}

      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>{editingId ? 'Edit parent' : 'Add parent'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              First name
              <input value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} required />
            </label>
            <label>
              Last name
              <input value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} required />
            </label>
            <label>
              Email
              <input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
            </label>
            <label>
              Phone
              <input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
            </label>
            <label>
              Linked student
              <select value={form.student_id} onChange={(e) => setForm({ ...form, student_id: e.target.value })}>
                <option value="">—</option>
                {students.map((s) => (
                  <option key={s.id} value={s.id}>
                    {s.full_name} ({s.admission_number})
                  </option>
                ))}
              </select>
            </label>
            <label>
              Relation
              <select value={form.relation} onChange={(e) => setForm({ ...form, relation: e.target.value })}>
                <option value="father">Father</option>
                <option value="mother">Mother</option>
                <option value="guardian">Guardian</option>
                <option value="other">Other</option>
              </select>
            </label>
            <label>
              Portal password {editingId ? '(leave blank to keep)' : '(optional login)'}
              <input type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
            </label>
          </div>
          <div className="actions">
            <button type="submit" className="btn">{editingId ? 'Update' : 'Create'}</button>
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
                  <th>Name</th>
                  <th>Email</th>
                  <th>Children</th>
                  <th>Login</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                {controller.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.full_name}</td>
                    <td>{item.email}</td>
                    <td>{item.students?.map((s) => s.full_name).join(', ') || '—'}</td>
                    <td>{item.user_id ? 'Yes' : 'No'}</td>
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
