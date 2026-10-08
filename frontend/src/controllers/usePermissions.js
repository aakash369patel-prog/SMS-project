import { useMemo } from 'react';
import { useAuthController } from './AuthController.jsx';

export function usePermissions() {
  const { user } = useAuthController();

  return useMemo(() => {
    const permissions = user?.permissions ?? [];
    const isAdmin = permissions.includes('*');

    function can(ability) {
      if (!user || user.role === 'parent') {
        return false;
      }
      return isAdmin || permissions.includes(ability);
    }

    return {
      role: user?.role,
      permissions,
      can,
      isAdmin,
    };
  }, [user]);
}
