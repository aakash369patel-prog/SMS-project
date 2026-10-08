import { useEffect, useState } from 'react';
import { NavLink, Outlet, useLocation } from 'react-router-dom';
import { useAuthController } from '../../controllers/AuthController.jsx';
import { usePermissions } from '../../controllers/usePermissions.js';
import { useTheme } from '../../controllers/ThemeController.jsx';
import { STAFF_NAV_LINKS, staffNavLinkForPath } from '../../config/navigation.js';
import { NavIcon } from '../components/NavIcon.jsx';

export function AppLayout() {
  const { user, logout } = useAuthController();
  const { can } = usePermissions();
  const { theme, toggleTheme } = useTheme();
  const location = useLocation();
  const links = STAFF_NAV_LINKS.filter((link) => can(link.permission));

  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

  const currentPage = staffNavLinkForPath(location.pathname);

  useEffect(() => {
    setSidebarOpen(false);
  }, [location.pathname]);

  useEffect(() => {
    document.body.classList.toggle('adminlte-sidebar-open', sidebarOpen);
    return () => document.body.classList.remove('adminlte-sidebar-open');
  }, [sidebarOpen]);

  const wrapperClass = [
    'adminlte-wrapper',
    sidebarCollapsed ? 'sidebar-collapse' : '',
    sidebarOpen ? 'sidebar-open' : '',
  ]
    .filter(Boolean)
    .join(' ');

  return (
    <div className={wrapperClass}>
      <nav className="main-header navbar">
        <ul className="navbar-nav">
          <li className="nav-item">
            <button
              type="button"
              className="nav-link nav-icon-btn"
              onClick={() => setSidebarOpen((o) => !o)}
              aria-label="Toggle navigation menu"
            >
              <span className="bars-icon" aria-hidden="true" />
            </button>
          </li>
          <li className="nav-item d-none-mobile">
            <button
              type="button"
              className="nav-link nav-icon-btn"
              onClick={() => setSidebarCollapsed((c) => !c)}
              aria-label="Collapse sidebar"
            >
              <span className="bars-icon" aria-hidden="true" />
            </button>
          </li>
        </ul>
        <ul className="navbar-nav navbar-nav-end">
          <li className="nav-item">
            <button type="button" className="nav-link nav-text-btn" onClick={toggleTheme}>
              {theme === 'dark' ? 'Light' : 'Dark'}
            </button>
          </li>
          <li className="nav-item nav-user">
            <span className="nav-user-name">{user?.name}</span>
            <span className="nav-user-role">{user?.role}</span>
          </li>
          <li className="nav-item">
            <button type="button" className="btn btn-navbar-logout" onClick={() => logout()}>
              Logout
            </button>
          </li>
        </ul>
      </nav>

      <aside className="main-sidebar">
        <NavLink to="/dashboard" className="brand-link" onClick={() => setSidebarOpen(false)}>
          <span className="brand-image">SMS</span>
          <span className="brand-text">School Admin</span>
        </NavLink>
        <div className="sidebar">
          <nav className="sidebar-menu">
            {links.map((link) => (
              <NavLink
                key={link.to}
                to={link.to}
                end={link.to === '/dashboard'}
                className={({ isActive }) => `nav-item${isActive ? ' active' : ''}`}
                onClick={() => setSidebarOpen(false)}
              >
                <NavIcon name={link.icon} className="nav-icon" />
                <span className="nav-label">{link.label}</span>
              </NavLink>
            ))}
          </nav>
        </div>
      </aside>

      <button
        type="button"
        className="sidebar-overlay"
        aria-label="Close menu"
        onClick={() => setSidebarOpen(false)}
      />

      <div className="content-wrapper">
        <div className="content-header">
          <div className="content-header-row">
            <h1 className="content-title">{currentPage?.label ?? 'Admin'}</h1>
            <ol className="breadcrumb">
              <li className="breadcrumb-item">
                <NavLink to="/dashboard">Home</NavLink>
              </li>
              {currentPage && currentPage.to !== '/dashboard' ? (
                <li className="breadcrumb-item active">{currentPage.label}</li>
              ) : (
                <li className="breadcrumb-item active">Dashboard</li>
              )}
            </ol>
          </div>
        </div>
        <section className="content">
          <Outlet />
        </section>
      </div>

      <footer className="main-footer">
        <strong>School Management System</strong>
        <span className="float-right">Admin panel</span>
      </footer>
    </div>
  );
}
