import { useEffect, useMemo, useState } from 'react';
import { usePermissions } from '../../controllers/usePermissions.js';
import { useTimetableController } from '../../controllers/useTimetableController.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { SubjectModel } from '../../models/SubjectModel.js';
import { TeacherModel } from '../../models/TeacherModel.js';

const DAY_LABELS = {
  monday: 'Monday',
  tuesday: 'Tuesday',
  wednesday: 'Wednesday',
  thursday: 'Thursday',
  friday: 'Friday',
  saturday: 'Saturday',
};

function cellKey(day, period) {
  return `${day}-${period}`;
}

function buildCellStateFromGrid(gridData) {
  const cells = {};
  gridData?.days?.forEach((dayBlock) => {
    dayBlock.periods.forEach((periodRow) => {
      const key = cellKey(dayBlock.day_of_week, periodRow.period_number);
      const slot = periodRow.slot;
      cells[key] = {
        subject_id: slot?.subject_id ? String(slot.subject_id) : '',
        teacher_id: slot?.teacher_id ? String(slot.teacher_id) : '',
        room: slot?.room ?? '',
      };
    });
  });
  return cells;
}

function buildSlotsPayload(gridData, cells) {
  const slots = [];
  gridData?.days?.forEach((dayBlock) => {
    dayBlock.periods.forEach((periodRow) => {
      const key = cellKey(dayBlock.day_of_week, periodRow.period_number);
      const cell = cells[key] ?? {};
      slots.push({
        day_of_week: dayBlock.day_of_week,
        period_number: periodRow.period_number,
        start_time: periodRow.default_start_time,
        end_time: periodRow.default_end_time,
        subject_id: cell.subject_id ? Number(cell.subject_id) : null,
        teacher_id: cell.teacher_id ? Number(cell.teacher_id) : null,
        room: cell.room?.trim() || null,
      });
    });
  });
  return slots;
}

export function TimetableView() {
  const { can } = usePermissions();
  const canEdit = can('timetable.manage');
  const controller = useTimetableController();
  const [classes, setClasses] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [teachers, setTeachers] = useState([]);
  const [schoolClassId, setSchoolClassId] = useState('');
  const [cells, setCells] = useState({});
  const [viewMode, setViewMode] = useState(canEdit ? 'edit' : 'view');

  useEffect(() => {
    SchoolClassModel.list(1).then((payload) => setClasses(payload.data ?? []));
    SubjectModel.list(1).then((payload) => setSubjects(payload.data ?? []));
    TeacherModel.list(1).then((payload) => setTeachers(payload.data ?? []));
  }, []);

  useEffect(() => {
    if (schoolClassId) {
      controller.loadGrid(schoolClassId).then((data) => {
        if (data) setCells(buildCellStateFromGrid(data));
      });
    }
  }, [schoolClassId, controller.loadGrid]);

  const classSubjects = useMemo(() => {
    if (!schoolClassId) return [];
    return subjects.filter((s) => String(s.school_class_id) === String(schoolClassId));
  }, [subjects, schoolClassId]);

  const dayColumns = controller.grid?.days ?? [];

  const periodRows = useMemo(() => {
    if (dayColumns.length === 0) return [];
    return dayColumns[0].periods.map((p) => p.period_number);
  }, [dayColumns]);

  function updateCell(day, period, patch) {
    const key = cellKey(day, period);
    setCells((prev) => {
      const next = { ...prev, [key]: { ...prev[key], ...patch } };
      if (patch.subject_id !== undefined && patch.subject_id) {
        const subject = classSubjects.find((s) => String(s.id) === String(patch.subject_id));
        if (subject?.teacher_id && !next[key].teacher_id) {
          next[key].teacher_id = String(subject.teacher_id);
        }
      }
      return next;
    });
  }

  async function handleSave(event) {
    event.preventDefault();
    if (!schoolClassId || !controller.grid) return;
    const slots = buildSlotsPayload(controller.grid, cells);
    await controller.saveBulk(schoolClassId, slots);
  }

  function renderCellDisplay(day, period) {
    const key = cellKey(day, period);
    const cell = cells[key];
    if (!cell?.subject_id) {
      return <span className="timetable-empty">—</span>;
    }
    const subject = classSubjects.find((s) => String(s.id) === cell.subject_id);
    const teacher = teachers.find((t) => String(t.id) === cell.teacher_id);
    return (
      <div className="timetable-cell-display">
        <strong>{subject?.name ?? 'Subject'}</strong>
        <span>{teacher?.full_name ?? 'No teacher'}</span>
        {cell.room ? <span>{cell.room}</span> : null}
      </div>
    );
  }

  function renderCellEditor(day, period) {
    const key = cellKey(day, period);
    const cell = cells[key] ?? { subject_id: '', teacher_id: '', room: '' };
    return (
      <div className="timetable-cell-editor">
        <select value={cell.subject_id} onChange={(e) => updateCell(day, period, { subject_id: e.target.value })}>
          <option value="">Free</option>
          {classSubjects.map((s) => (
            <option key={s.id} value={s.id}>
              {s.name}
            </option>
          ))}
        </select>
        <select value={cell.teacher_id} onChange={(e) => updateCell(day, period, { teacher_id: e.target.value })}>
          <option value="">Teacher</option>
          {teachers.map((t) => (
            <option key={t.id} value={t.id}>
              {t.full_name}
            </option>
          ))}
        </select>
        <input
          type="text"
          placeholder="Room"
          value={cell.room}
          onChange={(e) => updateCell(day, period, { room: e.target.value })}
        />
      </div>
    );
  }

  return (
    <>
      <div className="page-header">
        <h2>Timetable</h2>
        {canEdit ? (
          <div className="actions">
            <button type="button" className={`btn ${viewMode === 'view' ? '' : 'secondary'}`} onClick={() => setViewMode('view')}>
              View
            </button>
            <button type="button" className={`btn ${viewMode === 'edit' ? '' : 'secondary'}`} onClick={() => setViewMode('edit')}>
              Edit
            </button>
          </div>
        ) : null}
      </div>

      {controller.error ? <div className="alert error">{controller.error}</div> : null}
      {controller.success ? (
        <div className="alert" style={{ background: '#ecfdf5', border: '1px solid #a7f3d0', color: '#065f46' }}>
          {controller.success}
        </div>
      ) : null}

      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <label>
          Class
          <select value={schoolClassId} onChange={(e) => setSchoolClassId(e.target.value)}>
            <option value="">Select class</option>
            {classes.map((c) => (
              <option key={c.id} value={c.id}>
                {c.label}
              </option>
            ))}
          </select>
        </label>
      </div>

      {!schoolClassId ? (
        <p style={{ color: 'var(--muted)' }}>Select a class to view or edit its weekly timetable.</p>
      ) : controller.loading ? (
        <p>Loading timetable…</p>
      ) : (
        <form onSubmit={handleSave}>
          <div className="card table-wrap timetable-wrap">
            <table className="timetable-grid">
              <thead>
                <tr>
                  <th>Period</th>
                  {dayColumns.map((day) => (
                    <th key={day.day_of_week}>{DAY_LABELS[day.day_of_week] ?? day.day_of_week}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {periodRows.map((periodNumber) => {
                  const meta = dayColumns[0]?.periods.find((p) => p.period_number === periodNumber);
                  return (
                    <tr key={periodNumber}>
                      <td className="timetable-period">
                        <strong>P{periodNumber}</strong>
                        <span>
                          {meta?.default_start_time}–{meta?.default_end_time}
                        </span>
                      </td>
                      {dayColumns.map((day) => (
                        <td key={`${day.day_of_week}-${periodNumber}`}>
                          {viewMode === 'view'
                            ? renderCellDisplay(day.day_of_week, periodNumber)
                            : renderCellEditor(day.day_of_week, periodNumber)}
                        </td>
                      ))}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          {viewMode === 'edit' ? (
            <div className="actions" style={{ marginTop: '1rem' }}>
              <button type="submit" className="btn" disabled={controller.saving}>
                {controller.saving ? 'Saving…' : 'Save timetable'}
              </button>
            </div>
          ) : null}
        </form>
      )}
    </>
  );
}
