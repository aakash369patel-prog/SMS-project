import { useEffect, useState } from 'react';
import { FinanceModel } from '../../models/FinanceModel.js';
import { usePermissions } from '../../controllers/usePermissions.js';

const TABS = ['Expenses', 'Online payments'];

export function FinanceView() {
  const { can } = usePermissions();
  const [tab, setTab] = useState('Expenses');
  const [summary, setSummary] = useState(null);
  const [expenses, setExpenses] = useState([]);
  const [orders, setOrders] = useState([]);
  const [form, setForm] = useState({
    title: '',
    category: '',
    amount: '',
    expense_date: '',
    paid_to: '',
    payment_method: 'cash',
    notes: '',
  });

  function loadExpenses() {
    FinanceModel.expenseSummary().then((r) => setSummary(r.data));
    FinanceModel.listExpenses().then((p) => setExpenses(p.data ?? []));
  }
  function loadOrders() {
    FinanceModel.listPaymentOrders().then((p) => setOrders(p.data ?? []));
  }

  useEffect(() => {
    loadExpenses();
    loadOrders();
  }, []);

  if (!can('finance.expenses') && !can('finance.payments.view')) {
    return <p className="muted">You do not have access to finance modules.</p>;
  }

  return (
    <>
      <div className="page-header">
        <h2>Finance</h2>
        <div style={{ display: 'flex', gap: '0.5rem' }}>
          {TABS.map((t) => (
            <button key={t} type="button" className={tab === t ? 'btn' : 'btn secondary'} onClick={() => setTab(t)}>
              {t}
            </button>
          ))}
        </div>
      </div>

      {tab === 'Expenses' && can('finance.expenses') ? (
        <>
          {summary ? (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <strong>Total expenses:</strong> {summary.total_expenses}{' '}
              <span className="muted">| This month: {summary.current_month}</span>
            </div>
          ) : null}
          <div className="card" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Record expense</h3>
            <form
              onSubmit={async (e) => {
                e.preventDefault();
                await FinanceModel.createExpense({ ...form, amount: Number(form.amount) });
                setForm({ title: '', category: '', amount: '', expense_date: '', paid_to: '', payment_method: 'cash', notes: '' });
                loadExpenses();
              }}
              className="form-grid"
            >
              <label>Title<input value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required /></label>
              <label>Category<input value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} placeholder="Utilities, supplies…" /></label>
              <label>Amount<input type="number" min="0.01" step="0.01" value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} required /></label>
              <label>Date<input type="date" value={form.expense_date} onChange={(e) => setForm({ ...form, expense_date: e.target.value })} required /></label>
              <label>Paid to<input value={form.paid_to} onChange={(e) => setForm({ ...form, paid_to: e.target.value })} /></label>
              <div className="actions"><button type="submit" className="btn">Save</button></div>
            </form>
          </div>
          <div className="card table-wrap">
            <table>
              <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Amount</th><th>Paid to</th><th /></tr></thead>
              <tbody>
                {expenses.map((ex) => (
                  <tr key={ex.id}>
                    <td>{ex.expense_date}</td>
                    <td>{ex.title}</td>
                    <td>{ex.category}</td>
                    <td>{ex.amount}</td>
                    <td>{ex.paid_to}</td>
                    <td><button type="button" className="btn secondary" onClick={() => FinanceModel.deleteExpense(ex.id).then(loadExpenses)}>Delete</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      ) : null}

      {tab === 'Online payments' && can('finance.payments.view') ? (
        <div className="card table-wrap">
          <h3 style={{ marginTop: 0 }}>Gateway orders (Razorpay / Stripe / demo)</h3>
          <table>
            <thead><tr><th>ID</th><th>Student</th><th>Provider</th><th>Amount</th><th>Status</th><th>Paid</th></tr></thead>
            <tbody>
              {orders.map((o) => (
                <tr key={o.id}>
                  <td>{o.id}</td>
                  <td>{o.student_fee?.student?.full_name}</td>
                  <td>{o.provider}</td>
                  <td>{o.amount} {o.currency}</td>
                  <td>{o.status}</td>
                  <td>{o.paid_at ?? '—'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      ) : null}
    </>
  );
}
