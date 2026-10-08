import { useState } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { ApiError } from '../../models/ApiClient.js';
import { useAuthController } from '../../controllers/AuthController.jsx';

export function RegisterView() {
  const { register, isAuthenticated } = useAuthController();
  const navigate = useNavigate();
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [error, setError] = useState(null);
  const [fieldErrors, setFieldErrors] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />;
  }

  function updateField(key, value) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setError(null);
    setFieldErrors(null);
    setSubmitting(true);
    try {
      await register(form.name, form.email, form.password, form.password_confirmation);
      navigate('/dashboard');
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
        setFieldErrors(err.errors);
      } else {
        setError('Registration failed.');
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="auth-page">
      <div className="card auth-card">
        <h2>Create account</h2>
        {error ? <div className="alert error">{error}</div> : null}
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              Name
              <input value={form.name} onChange={(e) => updateField('name', e.target.value)} required />
              {fieldErrors?.name ? <span className="field-error">{fieldErrors.name[0]}</span> : null}
            </label>
            <label>
              Email
              <input
                type="email"
                value={form.email}
                onChange={(e) => updateField('email', e.target.value)}
                required
              />
              {fieldErrors?.email ? <span className="field-error">{fieldErrors.email[0]}</span> : null}
            </label>
            <label>
              Password
              <input
                type="password"
                value={form.password}
                onChange={(e) => updateField('password', e.target.value)}
                required
              />
            </label>
            <label>
              Confirm password
              <input
                type="password"
                value={form.password_confirmation}
                onChange={(e) => updateField('password_confirmation', e.target.value)}
                required
              />
            </label>
          </div>
          <button type="submit" className="btn" disabled={submitting}>
            {submitting ? 'Creating…' : 'Register'}
          </button>
        </form>
        <p style={{ marginTop: '1rem', fontSize: '0.875rem' }}>
          Already have an account? <Link to="/login">Sign in</Link>
        </p>
      </div>
    </div>
  );
}
