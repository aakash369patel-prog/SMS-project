import { NavLink, Outlet } from 'react-router-dom';
import { useAuthController } from '../../controllers/AuthController.jsx';
import { useTheme } from '../../controllers/ThemeController.jsx';

const links = [
  { to: '/parent', label: 'Home', end: true },
  { to: '/parent/fees', label: 'Fees' },
  { to: '/parent/announcements', label: 'Notices' },
  { to: '/parent/messages', label: 'Messages' },
  { to: '/parent/calendar', label: 'Calendar' },
];

export function ParentLayout() {
  const { user, logout } = useAuthController();
  const { theme, toggleTheme } = useTheme();

  return (
    <div className="app-shell parent-shell">
      <aside className="sidebar">
        <h1>Parent Portal</h1>
        <nav>
          {links.map((link) => (
            <NavLink
              key={link.to}
              to={link.to}
              end={link.end}
              className={({ isActive }) => (isActive ? 'active' : undefined)}
            >
              {link.label}
            </NavLink>
          ))}
        </nav>
      </aside>
      <div className="main">
        <div className="topbar">
          <span>{user?.name}</span>
          <button type="button" className="btn secondary" onClick={toggleTheme} aria-label="Toggle theme">
            {theme === 'dark' ? 'Light mode' : 'Dark mode'}
          </button>
          <button type="button" className="btn secondary" onClick={() => logout()}>
            Logout
          </button>
        </div>
        <Outlet />
      </div>
    </div>
  );
}
