import { useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { TeacherModel } from '../../models/TeacherModel.js';
import { Pagination } from '../components/Pagination.jsx';

const emptyForm = {
  employee_code: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  department: '',
  hire_date: '',
};

export function TeachersView() {
  const controller = useResourceController(TeacherModel);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);

  function startEdit(item) {
    setEditingId(item.id);
    setForm({
      employee_code: item.employee_code ?? '',
      first_name: item.first_name ?? '',
      last_name: item.last_name ?? '',
      email: item.email ?? '',
      phone: item.phone ?? '',
      department: item.department ?? '',
      hire_date: item.hire_date ?? '',
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
      phone: form.phone || null,
      department: form.department || null,
      hire_date: form.hire_date || null,
    };
    const ok = await controller.save(payload, editingId);
    if (ok) resetForm();
  }

  return (
    <>
      <div className="page-header">
        <h2>Teachers</h2>
      </div>
      {controller.error ? <div className="alert error">{controller.error}</div> : null}
      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>{editingId ? 'Edit teacher' : 'Add teacher'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              Employee code
              <input value={form.employee_code} onChange={(e) => setForm({ ...form, employee_code: e.target.value })} required />
            </label>
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
              Department
              <input value={form.department} onChange={(e) => setForm({ ...form, department: e.target.value })} />
            </label>
            <label>
              Hire date
              <input type="date" value={form.hire_date} onChange={(e) => setForm({ ...form, hire_date: e.target.value })} />
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
                  <th>Email</th>
                  <th>Department</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                {controller.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.employee_code}</td>
                    <td>{item.full_name}</td>
                    <td>{item.email}</td>
                    <td>{item.department ?? '—'}</td>
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
