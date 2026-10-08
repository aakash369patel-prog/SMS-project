import { useEffect, useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { StudentModel } from '../../models/StudentModel.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { Pagination } from '../components/Pagination.jsx';
import { ListFilters } from '../components/ListFilters.jsx';
import { UploadModel } from '../../models/UploadModel.js';
import { usePermissions } from '../../controllers/usePermissions.js';

const emptyForm = {
  admission_number: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  school_class_id: '',
};

export function StudentsView() {
  const { can } = usePermissions();
  const controller = useResourceController(StudentModel);
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState(null);
  const [classes, setClasses] = useState([]);
  const [filters, setFilters] = useState({ q: '', school_class_id: '' });
  const [appliedFilters, setAppliedFilters] = useState({});

  useEffect(() => {
    SchoolClassModel.list(1).then((payload) => {
      setClasses(payload.data ?? []);
    });
  }, []);

  function startEdit(item) {
    setEditingId(item.id);
    setForm({
      admission_number: item.admission_number ?? '',
      first_name: item.first_name ?? '',
      last_name: item.last_name ?? '',
      email: item.email ?? '',
      phone: item.phone ?? '',
      date_of_birth: item.date_of_birth ?? '',
      gender: item.gender ?? '',
      school_class_id: item.school_class_id ?? '',
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
      gender: form.gender || null,
      phone: form.phone || null,
      date_of_birth: form.date_of_birth || null,
    };
    const ok = await controller.save(payload, editingId);
    if (ok) resetForm();
  }

  return (
    <>
      <div className="page-header">
        <h2>Students</h2>
      </div>
      {controller.error ? <div className="alert error">{controller.error}</div> : null}
      <ListFilters
        onApply={() => {
          setAppliedFilters({ ...filters });
          controller.load(1, filters);
        }}
        onReset={() => {
          setFilters({ q: '', school_class_id: '' });
          setAppliedFilters({});
          controller.load(1, {});
        }}
      >
        <label>
          Search
          <input
            value={filters.q}
            onChange={(e) => {
              const next = { ...filters, q: e.target.value };
              setFilters(next);
              setAppliedFilters(next);
            }}
            placeholder="Name, admission #, email"
          />
        </label>
        <label>
          Class
          <select
            value={filters.school_class_id}
            onChange={(e) => {
              const next = { ...filters, school_class_id: e.target.value };
              setFilters(next);
              setAppliedFilters(next);
            }}
          >
            <option value="">All classes</option>
            {classes.map((c) => (
              <option key={c.id} value={c.id}>
                {c.label ?? `${c.name} ${c.section ?? ''}`}
              </option>
            ))}
          </select>
        </label>
      </ListFilters>
      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>{editingId ? 'Edit student' : 'Add student'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              Admission #
              <input
                value={form.admission_number}
                onChange={(e) => setForm({ ...form, admission_number: e.target.value })}
                required
              />
              {controller.fieldErrors?.admission_number ? (
                <span className="field-error">{controller.fieldErrors.admission_number[0]}</span>
              ) : null}
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
              Date of birth
              <input type="date" value={form.date_of_birth} onChange={(e) => setForm({ ...form, date_of_birth: e.target.value })} />
            </label>
            <label>
              Gender
              <select value={form.gender} onChange={(e) => setForm({ ...form, gender: e.target.value })}>
                <option value="">—</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </label>
            <label>
              Class
              <select value={form.school_class_id} onChange={(e) => setForm({ ...form, school_class_id: e.target.value })}>
                <option value="">—</option>
                {classes.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.label ?? `${c.name} ${c.section ?? ''}`}
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
                  <th>Photo</th>
                  <th>Admission #</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Class</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                {controller.items.map((item) => (
                  <tr key={item.id}>
                    <td>
                      {item.photo_url ? <img src={item.photo_url} alt="" style={{ width: 36, height: 36, borderRadius: 6, objectFit: 'cover' }} /> : '—'}
                      {can('files.upload') ? (
                        <input
                          type="file"
                          accept="image/*"
                          style={{ display: 'block', fontSize: '0.7rem', marginTop: 4 }}
                          onChange={(e) => {
                            const file = e.target.files?.[0];
                            if (file) UploadModel.studentPhoto(item.id, file).then(() => controller.load(controller.page, appliedFilters));
                          }}
                        />
                      ) : null}
                    </td>
                    <td>{item.admission_number}</td>
                    <td>{item.full_name}</td>
                    <td>{item.email}</td>
                    <td>{item.school_class?.label ?? '—'}</td>
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
            <Pagination meta={controller.meta} onPageChange={(p) => controller.load(p, appliedFilters)} />
          </>
        )}
      </div>
    </>
  );
}
