import { useEffect, useState } from 'react';
import { ParentPortalModel } from '../../models/ParentPortalModel.js';

export function ParentFeesView() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [payMsg, setPayMsg] = useState('');

  function load() {
    setLoading(true);
    ParentPortalModel.fees()
      .then((payload) => setItems(payload.data ?? []))
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    load();
  }, []);

  async function payOnline(row, provider) {
    setPayMsg('');
    try {
      const intent = await ParentPortalModel.createPaymentIntent(row.id, provider);
      const order = intent.data;
      await ParentPortalModel.confirmPayment(order.id);
      setPayMsg(`Payment successful via ${provider} (demo: ${order.external_order_id}).`);
      load();
    } catch {
      setPayMsg('Payment could not be completed.');
    }
  }

  return (
    <>
      <div className="page-header"><h2>Fee statements</h2></div>
      {payMsg ? <div className="alert">{payMsg}</div> : null}
      <div className="card table-wrap">
        {loading ? <p>Loading…</p> : (
          <table>
            <thead><tr><th>Student</th><th>Fee</th><th>Amount</th><th>Discount</th><th>Net</th><th>Paid</th><th>Balance</th><th>Status</th><th>Due</th><th>Pay online</th></tr></thead>
            <tbody>
              {items.map((row) => (
                <tr key={row.id}>
                  <td>{row.student?.full_name}</td>
                  <td>{row.title}</td>
                  <td>{row.amount}</td>
                  <td>{row.discount_amount ?? 0}</td>
                  <td>{row.effective_amount ?? row.amount}</td>
                  <td>{row.amount_paid}</td>
                  <td>{row.balance}</td>
                  <td>{row.status}</td>
                  <td>{row.due_date}</td>
                  <td>
                    {row.balance > 0 && row.status !== 'cancelled' ? (
                      <span style={{ display: 'flex', flexWrap: 'wrap', gap: '0.25rem' }}>
                        <button type="button" className="btn secondary" onClick={() => payOnline(row, 'razorpay')}>Razorpay</button>
                        <button type="button" className="btn secondary" onClick={() => payOnline(row, 'stripe')}>Stripe</button>
                      </span>
                    ) : '—'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
        <p className="muted" style={{ marginTop: '0.75rem' }}>Without live gateway keys, Razorpay/Stripe run in demo mode and complete after confirm.</p>
      </div>
    </>
  );
}
