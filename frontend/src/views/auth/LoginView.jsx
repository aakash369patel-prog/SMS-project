import { useState } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { ApiError } from '../../models/ApiClient.js';
import { useAuthController } from '../../controllers/AuthController.jsx';

export function LoginView() {
  const { login, isAuthenticated, user } = useAuthController();
  const navigate = useNavigate();
  const [email, setEmail] = useState('admin@school.local');
  const [password, setPassword] = useState('password');
  const [error, setError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  if (isAuthenticated) {
    return <Navigate to={user?.role === 'parent' ? '/parent' : '/dashboard'} replace />;
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      const session = await login(email, password);
      navigate(session.user?.role === 'parent' ? '/parent' : '/dashboard');
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Login failed.');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="auth-page">
      <div className="card auth-card">
        <h2>Sign in</h2>
        <p style={{ color: 'var(--muted)', marginTop: 0 }}>School Management System</p>
        {error ? <div className="alert error">{error}</div> : null}
        <form onSubmit={handleSubmit}>
          <div className="form-grid">
            <label>
              Email
              <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
            </label>
            <label>
              Password
              <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
            </label>
          </div>
          <button type="submit" className="btn" disabled={submitting}>
            {submitting ? 'Signing in…' : 'Sign in'}
          </button>
        </form>
        <p style={{ marginTop: '1rem', fontSize: '0.875rem' }}>
          Demo: admin / teacher / staff / parent — <code>@school.local</code>, password <code>password</code>
        </p>
        <p style={{ fontSize: '0.875rem' }}>
          No account? <Link to="/register">Register</Link>
        </p>
      </div>
    </div>
  );
}
