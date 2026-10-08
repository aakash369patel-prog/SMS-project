import { useDashboardController } from '../../controllers/useDashboardController.js';
import { SimpleBarChart } from '../components/SimpleBarChart.jsx';

export function DashboardView() {
  const { stats, charts, loading, error } = useDashboardController();

  const attendancePresent = (charts?.attendance_trends ?? []).map((d) => ({
    label: d.label,
    value: d.present,
  }));
  const feeBars = charts?.fee_collection ?? [];
  const passBars = charts?.pass_rates ?? [];

  return (
    <>
      {error ? <div className="alert error">{error}</div> : null}
      {loading ? (
        <p>Loading stats…</p>
      ) : (
        <>
          <div className="grid-stats adminlte-small-boxes">
            <div className="stat-card small-box bg-info">
              <span>Students</span>
              <strong>{stats?.students ?? 0}</strong>
            </div>
            <div className="stat-card small-box bg-success">
              <span>Teachers</span>
              <strong>{stats?.teachers ?? 0}</strong>
            </div>
            <div className="stat-card small-box bg-warning">
              <span>Classes</span>
              <strong>{stats?.classes ?? 0}</strong>
            </div>
            <div className="stat-card small-box bg-danger">
              <span>Open fees</span>
              <strong>{stats?.open_fees ?? 0}</strong>
            </div>
            <div className="stat-card small-box bg-primary">
              <span>Attendance today</span>
              <strong>{stats?.attendance_marked_today ?? 0}</strong>
            </div>
            <div className="stat-card small-box bg-secondary">
              <span>Grades recorded</span>
              <strong>{stats?.grades_recorded ?? 0}</strong>
            </div>
          </div>

          <div className="chart-grid" style={{ marginTop: '1.25rem' }}>
            <div className="card">
              <SimpleBarChart title="Attendance trend (present, 7 days)" items={attendancePresent} />
            </div>
            <div className="card">
              <SimpleBarChart title="Fee collection (6 months)" items={feeBars} />
            </div>
            <div className="card">
              <SimpleBarChart title="Pass rate % (recent exams, ≥50%)" items={passBars} />
            </div>
          </div>
        </>
      )}
    </>
  );
}
