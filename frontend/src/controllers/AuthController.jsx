import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { AuthModel } from '../models/AuthModel.js';

const AuthContext = createContext(null);

const TOKEN_KEY = 'token';
const USER_KEY = 'user';

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    const stored = localStorage.getItem(USER_KEY);
    return stored ? JSON.parse(stored) : null;
  });
  const [loading, setLoading] = useState(!!localStorage.getItem(TOKEN_KEY));

  const persistSession = useCallback((session) => {
    localStorage.setItem(TOKEN_KEY, session.token);
    localStorage.setItem(USER_KEY, JSON.stringify(session.user));
    setUser(session.user);
  }, []);

  const clearSession = useCallback(() => {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    setUser(null);
  }, []);

  useEffect(() => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (!token) {
      setLoading(false);
      return;
    }

    AuthModel.me()
      .then((res) => {
        const nextUser = res.user ?? res;
        setUser(nextUser);
        localStorage.setItem(USER_KEY, JSON.stringify(nextUser));
      })
      .catch(() => clearSession())
      .finally(() => setLoading(false));
  }, [clearSession]);

  const login = useCallback(
    async (email, password) => {
      const session = await AuthModel.login(email, password);
      persistSession(session);
      return session;
    },
    [persistSession],
  );

  const register = useCallback(
    async (name, email, password, passwordConfirmation) => {
      const session = await AuthModel.register(name, email, password, passwordConfirmation);
      persistSession(session);
      return session;
    },
    [persistSession],
  );

  const logout = useCallback(async () => {
    try {
      await AuthModel.logout();
    } finally {
      clearSession();
    }
  }, [clearSession]);

  const value = useMemo(
    () => ({
      user,
      loading,
      isAuthenticated: !!user,
      login,
      register,
      logout,
    }),
    [user, loading, login, register, logout],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuthController() {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuthController must be used within AuthProvider');
  }
  return ctx;
}
