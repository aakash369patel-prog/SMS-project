import { useEffect, useState } from 'react';
import { HrModel } from '../../models/HrModel.js';
import { usePermissions } from '../../controllers/usePermissions.js';

function rowsOf(payload) {
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  return [];
}

export function HrView() {
  const { can } = usePermissions();
  const [staff, setStaff] = useState([]);
  const [leave, setLeave] = useState([]);
  const [payroll, setPayroll] = useState([]);
  const [staffForm, setStaffForm] = useState({ employee_code: '', first_name: '', last_name: '', email: '', department: '', job_title: '', basic_salary: '' });
  const [leaveForm, setLeaveForm] = useState({ staff_member_id: '', leave_type: 'casual', start_date: '', end_date: '', reason: '' });
  const [payrollForm, setPayrollForm] = useState({ staff_member_id: '', month: String(new Date().getMonth() + 1), year: String(new Date().getFullYear()), allowances: '0', deductions: '0' });

  function load() {
    HrModel.listStaff().then((p) => setStaff(rowsOf(p)));
    HrModel.listLeave().then((p) => setLeave(rowsOf(p)));
    HrModel.listPayroll().then((p) => setPayroll(rowsOf(p)));
  }

  useEffect(() => { load(); }, []);

  return (
    <>
      <div className="page-header"><h2>Staff / HR</h2></div>
      {can('hr.manage') ? (
        <>
          <div className="card" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Add staff member</h3>
            <form onSubmit={async (e) => { e.preventDefault(); await HrModel.createStaff({ ...staffForm, basic_salary: Number(staffForm.basic_salary || 0) }); load(); }} className="form-grid">
              <label>Code<input value={staffForm.employee_code} onChange={(e) => setStaffForm({ ...staffForm, employee_code: e.target.value })} required /></label>
              <label>First name<input value={staffForm.first_name} onChange={(e) => setStaffForm({ ...staffForm, first_name: e.target.value })} required /></label>
              <label>Last name<input value={staffForm.last_name} onChange={(e) => setStaffForm({ ...staffForm, last_name: e.target.value })} required /></label>
              <label>Email<input type="email" value={staffForm.email} onChange={(e) => setStaffForm({ ...staffForm, email: e.target.value })} required /></label>
              <label>Department<input value={staffForm.department} onChange={(e) => setStaffForm({ ...staffForm, department: e.target.value })} /></label>
              <label>Job title<input value={staffForm.job_title} onChange={(e) => setStaffForm({ ...staffForm, job_title: e.target.value })} /></label>
              <label>Basic salary<input type="number" value={staffForm.basic_salary} onChange={(e) => setStaffForm({ ...staffForm, basic_salary: e.target.value })} /></label>
              <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Save</button></div>
            </form>
          </div>
          <div className="card" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Leave request</h3>
            <form onSubmit={async (e) => { e.preventDefault(); await HrModel.createLeave({ ...leaveForm, staff_member_id: Number(leaveForm.staff_member_id) }); load(); }} className="form-grid">
              <label>Staff<select value={leaveForm.staff_member_id} onChange={(e) => setLeaveForm({ ...leaveForm, staff_member_id: e.target.value })} required><option value="">—</option>{staff.map((s) => <option key={s.id} value={s.id}>{s.first_name} {s.last_name}</option>)}</select></label>
              <label>Type<select value={leaveForm.leave_type} onChange={(e) => setLeaveForm({ ...leaveForm, leave_type: e.target.value })}><option value="casual">Casual</option><option value="sick">Sick</option><option value="annual">Annual</option></select></label>
              <label>Start<input type="date" value={leaveForm.start_date} onChange={(e) => setLeaveForm({ ...leaveForm, start_date: e.target.value })} required /></label>
              <label>End<input type="date" value={leaveForm.end_date} onChange={(e) => setLeaveForm({ ...leaveForm, end_date: e.target.value })} required /></label>
              <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Submit</button></div>
            </form>
          </div>
          <div className="card" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Payroll entry</h3>
            <form onSubmit={async (e) => { e.preventDefault(); await HrModel.createPayroll({ ...payrollForm, staff_member_id: Number(payrollForm.staff_member_id), month: Number(payrollForm.month), year: Number(payrollForm.year), allowances: Number(payrollForm.allowances), deductions: Number(payrollForm.deductions) }); load(); }} className="form-grid">
              <label>Staff<select value={payrollForm.staff_member_id} onChange={(e) => setPayrollForm({ ...payrollForm, staff_member_id: e.target.value })} required><option value="">—</option>{staff.map((s) => <option key={s.id} value={s.id}>{s.first_name} {s.last_name}</option>)}</select></label>
              <label>Month<input type="number" min="1" max="12" value={payrollForm.month} onChange={(e) => setPayrollForm({ ...payrollForm, month: e.target.value })} /></label>
              <label>Year<input type="number" value={payrollForm.year} onChange={(e) => setPayrollForm({ ...payrollForm, year: e.target.value })} /></label>
              <label>Allowances<input type="number" value={payrollForm.allowances} onChange={(e) => setPayrollForm({ ...payrollForm, allowances: e.target.value })} /></label>
              <label>Deductions<input type="number" value={payrollForm.deductions} onChange={(e) => setPayrollForm({ ...payrollForm, deductions: e.target.value })} /></label>
              <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Generate</button></div>
            </form>
          </div>
        </>
      ) : null}
      <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
        <h3 style={{ marginTop: 0 }}>Staff</h3>
        <table><thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Salary</th></tr></thead><tbody>{staff.map((s) => <tr key={s.id}><td>{s.employee_code}</td><td>{s.first_name} {s.last_name}</td><td>{s.department}</td><td>{s.basic_salary}</td></tr>)}</tbody></table>
      </div>
      <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
        <h3 style={{ marginTop: 0 }}>Leave</h3>
        <table><thead><tr><th>Staff</th><th>Type</th><th>Dates</th><th>Status</th><th /></tr></thead><tbody>{leave.map((l) => <tr key={l.id}><td>{l.staff_member?.first_name} {l.staff_member?.last_name}</td><td>{l.leave_type}</td><td>{l.start_date} → {l.end_date}</td><td>{l.status}</td><td>{can('hr.manage') && l.status === 'pending' ? <><button type="button" className="btn secondary" onClick={() => HrModel.updateLeaveStatus(l.id, 'approved').then(load)}>Approve</button> <button type="button" className="btn danger" onClick={() => HrModel.updateLeaveStatus(l.id, 'rejected').then(load)}>Reject</button></> : null}</td></tr>)}</tbody></table>
      </div>
      <div className="card table-wrap">
        <h3 style={{ marginTop: 0 }}>Payroll</h3>
        <table><thead><tr><th>Staff</th><th>Period</th><th>Net pay</th><th>Status</th></tr></thead><tbody>{payroll.map((p) => <tr key={p.id}><td>{p.staff_member?.first_name} {p.staff_member?.last_name}</td><td>{p.month}/{p.year}</td><td>{p.net_pay}</td><td>{p.status}</td></tr>)}</tbody></table>
      </div>
    </>
  );
}
