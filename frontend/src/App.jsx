import { Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider, useAuthController } from './controllers/AuthController.jsx';
import { ProtectedRoute } from './views/components/ProtectedRoute.jsx';
import { AdminRoute } from './views/components/AdminRoute.jsx';
import { ParentRoute } from './views/components/ParentRoute.jsx';
import { AppLayout } from './views/layout/AppLayout.jsx';
import { ParentLayout } from './views/layout/ParentLayout.jsx';
import { LoginView } from './views/auth/LoginView.jsx';
import { RegisterView } from './views/auth/RegisterView.jsx';
import { DashboardView } from './views/dashboard/DashboardView.jsx';
import { StudentsView } from './views/students/StudentsView.jsx';
import { TeachersView } from './views/teachers/TeachersView.jsx';
import { ClassesView } from './views/classes/ClassesView.jsx';
import { SubjectsView } from './views/subjects/SubjectsView.jsx';
import { AttendanceView } from './views/attendance/AttendanceView.jsx';
import { ExamsView } from './views/exams/ExamsView.jsx';
import { TimetableView } from './views/timetable/TimetableView.jsx';
import { ParentsView } from './views/parents/ParentsView.jsx';
import { FeesView } from './views/fees/FeesView.jsx';
import { AnnouncementsView } from './views/announcements/AnnouncementsView.jsx';
import { ReportsView } from './views/reports/ReportsView.jsx';
import { LibraryView } from './views/library/LibraryView.jsx';
import { TransportView } from './views/transport/TransportView.jsx';
import { HrView } from './views/hr/HrView.jsx';
import { AcademicsView } from './views/academics/AcademicsView.jsx';
import { CommunicationView } from './views/communication/CommunicationView.jsx';
import { FinanceView } from './views/finance/FinanceView.jsx';
import { AuditView } from './views/audit/AuditView.jsx';
import { ParentHomeView } from './views/parent/ParentHomeView.jsx';
import { ParentFeesView } from './views/parent/ParentFeesView.jsx';
import { ParentAnnouncementsView } from './views/parent/ParentAnnouncementsView.jsx';
import { ParentMessagesView } from './views/parent/ParentMessagesView.jsx';
import { ParentCalendarView } from './views/parent/ParentCalendarView.jsx';

function HomeRedirect() {
  const { user } = useAuthController();
  return <Navigate to={user?.role === 'parent' ? '/parent' : '/dashboard'} replace />;
}

export default function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/login" element={<LoginView />} />
        <Route path="/register" element={<RegisterView />} />
        <Route element={<ProtectedRoute />}>
          <Route index element={<HomeRedirect />} />
          <Route element={<AdminRoute />}>
            <Route element={<AppLayout />}>
              <Route path="dashboard" element={<DashboardView />} />
              <Route path="students" element={<StudentsView />} />
              <Route path="teachers" element={<TeachersView />} />
              <Route path="classes" element={<ClassesView />} />
              <Route path="subjects" element={<SubjectsView />} />
              <Route path="attendance" element={<AttendanceView />} />
              <Route path="exams" element={<ExamsView />} />
              <Route path="timetable" element={<TimetableView />} />
              <Route path="parents" element={<ParentsView />} />
              <Route path="fees" element={<FeesView />} />
              <Route path="announcements" element={<AnnouncementsView />} />
            <Route path="reports" element={<ReportsView />} />
            <Route path="library" element={<LibraryView />} />
            <Route path="transport" element={<TransportView />} />
            <Route path="hr" element={<HrView />} />
              <Route path="academics" element={<AcademicsView />} />
              <Route path="communication" element={<CommunicationView />} />
              <Route path="finance" element={<FinanceView />} />
              <Route path="audit" element={<AuditView />} />
            </Route>
          </Route>
          <Route element={<ParentRoute />}>
            <Route element={<ParentLayout />}>
              <Route path="parent" element={<ParentHomeView />} />
              <Route path="parent/fees" element={<ParentFeesView />} />
              <Route path="parent/announcements" element={<ParentAnnouncementsView />} />
              <Route path="parent/messages" element={<ParentMessagesView />} />
              <Route path="parent/calendar" element={<ParentCalendarView />} />
            </Route>
          </Route>
        </Route>
        <Route path="*" element={<HomeRedirect />} />
      </Routes>
    </AuthProvider>
  );
}
