import { useEffect, useState } from 'react';
import { TransportModel } from '../../models/TransportModel.js';
import { StudentModel } from '../../models/StudentModel.js';

function rowsOf(payload) {
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  return [];
}

export function TransportView() {
  const [routes, setRoutes] = useState([]);
  const [buses, setBuses] = useState([]);
  const [assignments, setAssignments] = useState([]);
  const [students, setStudents] = useState([]);
  const [routeForm, setRouteForm] = useState({ name: '', code: '', start_point: '', end_point: '' });
  const [busForm, setBusForm] = useState({ registration_number: '', capacity: '40', transport_route_id: '', driver_name: '' });
  const [assignForm, setAssignForm] = useState({ student_id: '', transport_route_id: '', bus_id: '', pickup_point: '', drop_point: '', trip_type: 'both' });

  function load() {
    TransportModel.listRoutes().then((p) => setRoutes(rowsOf(p)));
    TransportModel.listBuses().then((p) => setBuses(rowsOf(p)));
    TransportModel.listAssignments().then((p) => setAssignments(rowsOf(p)));
  }

  useEffect(() => {
    load();
    StudentModel.list(1).then((p) => setStudents(rowsOf(p)));
  }, []);

  return (
    <>
      <div className="page-header"><h2>Transport</h2></div>
      <div className="card" style={{ marginBottom: '1rem' }}>
        <h3 style={{ marginTop: 0 }}>Add route</h3>
        <form onSubmit={async (e) => { e.preventDefault(); await TransportModel.createRoute(routeForm); setRouteForm({ name: '', code: '', start_point: '', end_point: '' }); load(); }} className="form-grid">
          <label>Name<input value={routeForm.name} onChange={(e) => setRouteForm({ ...routeForm, name: e.target.value })} required /></label>
          <label>Code<input value={routeForm.code} onChange={(e) => setRouteForm({ ...routeForm, code: e.target.value })} required /></label>
          <label>Start<input value={routeForm.start_point} onChange={(e) => setRouteForm({ ...routeForm, start_point: e.target.value })} /></label>
          <label>End<input value={routeForm.end_point} onChange={(e) => setRouteForm({ ...routeForm, end_point: e.target.value })} /></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Save route</button></div>
        </form>
      </div>
      <div className="card" style={{ marginBottom: '1rem' }}>
        <h3 style={{ marginTop: 0 }}>Add bus</h3>
        <form onSubmit={async (e) => { e.preventDefault(); await TransportModel.createBus({ ...busForm, capacity: Number(busForm.capacity), transport_route_id: busForm.transport_route_id ? Number(busForm.transport_route_id) : null }); load(); }} className="form-grid">
          <label>Registration<input value={busForm.registration_number} onChange={(e) => setBusForm({ ...busForm, registration_number: e.target.value })} required /></label>
          <label>Route<select value={busForm.transport_route_id} onChange={(e) => setBusForm({ ...busForm, transport_route_id: e.target.value })}><option value="">—</option>{routes.map((r) => <option key={r.id} value={r.id}>{r.name}</option>)}</select></label>
          <label>Capacity<input type="number" value={busForm.capacity} onChange={(e) => setBusForm({ ...busForm, capacity: e.target.value })} /></label>
          <label>Driver<input value={busForm.driver_name} onChange={(e) => setBusForm({ ...busForm, driver_name: e.target.value })} /></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Save bus</button></div>
        </form>
      </div>
      <div className="card" style={{ marginBottom: '1rem' }}>
        <h3 style={{ marginTop: 0 }}>Assign student</h3>
        <form onSubmit={async (e) => { e.preventDefault(); await TransportModel.assign({ ...assignForm, student_id: Number(assignForm.student_id), transport_route_id: Number(assignForm.transport_route_id), bus_id: assignForm.bus_id ? Number(assignForm.bus_id) : null }); load(); }} className="form-grid">
          <label>Student<select value={assignForm.student_id} onChange={(e) => setAssignForm({ ...assignForm, student_id: e.target.value })} required><option value="">—</option>{students.map((s) => <option key={s.id} value={s.id}>{s.full_name}</option>)}</select></label>
          <label>Route<select value={assignForm.transport_route_id} onChange={(e) => setAssignForm({ ...assignForm, transport_route_id: e.target.value })} required><option value="">—</option>{routes.map((r) => <option key={r.id} value={r.id}>{r.name}</option>)}</select></label>
          <label>Bus<select value={assignForm.bus_id} onChange={(e) => setAssignForm({ ...assignForm, bus_id: e.target.value })}><option value="">—</option>{buses.map((b) => <option key={b.id} value={b.id}>{b.registration_number}</option>)}</select></label>
          <label>Pickup<input value={assignForm.pickup_point} onChange={(e) => setAssignForm({ ...assignForm, pickup_point: e.target.value })} /></label>
          <label>Drop<input value={assignForm.drop_point} onChange={(e) => setAssignForm({ ...assignForm, drop_point: e.target.value })} /></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Assign</button></div>
        </form>
      </div>
      <div className="card table-wrap">
        <h3 style={{ marginTop: 0 }}>Student assignments</h3>
        <table><thead><tr><th>Student</th><th>Route</th><th>Bus</th><th>Pickup</th><th>Drop</th></tr></thead><tbody>{assignments.map((a) => <tr key={a.id}><td>{a.student?.full_name}</td><td>{a.route?.name}</td><td>{a.bus?.registration_number}</td><td>{a.pickup_point}</td><td>{a.drop_point}</td></tr>)}</tbody></table>
      </div>
    </>
  );
}
