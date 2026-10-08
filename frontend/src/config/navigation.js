export const STAFF_NAV_LINKS = [
  { to: '/dashboard', label: 'Dashboard', permission: 'dashboard.view', icon: 'dashboard' },
  { to: '/students', label: 'Students', permission: 'students.view', icon: 'students' },
  { to: '/teachers', label: 'Teachers', permission: 'teachers.view', icon: 'teachers' },
  { to: '/classes', label: 'Classes', permission: 'classes.view', icon: 'classes' },
  { to: '/subjects', label: 'Subjects', permission: 'subjects.view', icon: 'subjects' },
  { to: '/attendance', label: 'Attendance', permission: 'attendance.view', icon: 'attendance' },
  { to: '/exams', label: 'Exams & grades', permission: 'exams.view', icon: 'exams' },
  { to: '/academics', label: 'Academics', permission: 'academics.view', icon: 'academics' },
  { to: '/timetable', label: 'Timetable', permission: 'timetable.view', icon: 'timetable' },
  { to: '/parents', label: 'Parents', permission: 'parents.view', icon: 'parents' },
  { to: '/fees', label: 'Fees', permission: 'fees.view', icon: 'fees' },
  { to: '/finance', label: 'Finance', permission: 'finance.expenses', icon: 'finance' },
  { to: '/audit', label: 'Audit log', permission: 'audit.view', icon: 'audit' },
  { to: '/announcements', label: 'Notices', permission: 'announcements.view', icon: 'announcements' },
  { to: '/communication', label: 'Communication', permission: 'communication.view', icon: 'communication' },
  { to: '/reports', label: 'Reports', permission: 'reports.view', icon: 'reports' },
  { to: '/library', label: 'Library', permission: 'library.view', icon: 'library' },
  { to: '/transport', label: 'Transport', permission: 'transport.manage', icon: 'transport' },
  { to: '/hr', label: 'Staff / HR', permission: 'hr.view', icon: 'hr' },
];

export function staffNavLinkForPath(pathname) {
  return STAFF_NAV_LINKS.find((link) =>
    link.to === '/dashboard' ? pathname === '/dashboard' : pathname.startsWith(link.to),
  );
}
