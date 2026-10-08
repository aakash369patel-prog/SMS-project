import { Navigate, Outlet } from 'react-router-dom';
import { useAuthController } from '../../controllers/AuthController.jsx';

/** Staff app shell: admin, teacher, and office staff (not parents). */
export function AdminRoute() {
  const { user } = useAuthController();
  if (user?.role === 'parent') {
    return <Navigate to="/parent" replace />;
  }
  if (user && !['admin', 'teacher', 'staff'].includes(user.role)) {
    return <Navigate to="/login" replace />;
  }
  return <Outlet />;
}
