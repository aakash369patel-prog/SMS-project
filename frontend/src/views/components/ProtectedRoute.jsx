import { Navigate, Outlet } from 'react-router-dom';
import { useAuthController } from '../../controllers/AuthController.jsx';

export function ProtectedRoute() {
  const { isAuthenticated, loading } = useAuthController();

  if (loading) {
    return (
      <div className="auth-page">
        <p>Loading session…</p>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
}
