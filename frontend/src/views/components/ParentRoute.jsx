import { Navigate, Outlet } from 'react-router-dom';
import { useAuthController } from '../../controllers/AuthController.jsx';

export function ParentRoute() {
  const { user } = useAuthController();
  if (user?.role !== 'parent') {
    return <Navigate to="/dashboard" replace />;
  }
  return <Outlet />;
}
