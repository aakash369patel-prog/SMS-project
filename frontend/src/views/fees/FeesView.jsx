import { useEffect, useState } from 'react';
import { useResourceController } from '../../controllers/useResourceController.js';
import { FeeTypeModel } from '../../models/FeeTypeModel.js';
import { StudentFeeModel } from '../../models/StudentFeeModel.js';
import { StudentModel } from '../../models/StudentModel.js';
import { SchoolClassModel } from '../../models/SchoolClassModel.js';
import { Pagination } from '../components/Pagination.jsx';
import { usePermissions } from '../../controllers/usePermissions.js';
import { ListFilters } from '../components/ListFilters.jsx';

const emptyFeeType = { name: '', code: '', amount: '', academic_year: '2025-2026', description: '' };
const emptyInvoice = { student_id: '', fee_type_id: '', due_date: '' };

export function FeesView() {
  const { can } = usePermissions();
  const feeTypes = useResourceController(FeeTypeModel);
  const [invoices, setInvoices] = useState([]);
  const [invoiceMeta, setInvoiceMeta] = useState(null);
  const [invoiceLoading, setInvoiceLoading] = useState(false);
  const [invoiceError, setInvoiceError] = useState(null);
  const [feeTypeForm, setFeeTypeForm] = useState(emptyFeeType);
  const [editingFeeTypeId, setEditingFeeTypeId] = useState(null);
  const [invoiceForm, setInvoiceForm] = useState(emptyInvoice);
  const [bulkClassId, setBulkClassId] = useState('');
  const [bulkFeeTypeId, setBulkFeeTypeId] = useState('');
  const [bulkDueDate, setBulkDueDate] = useState('');
  const [students, setStudents] = useState([]);
  const [classes, setClasses] = useState([]);
  const [paymentModal, setPaymentModal] = useState(null);
  const [paymentForm, setPaymentForm] = useState({ amount: '', payment_date: '', payment_method: 'cash', reference: '' });
  const [discountModal, setDiscountModal] = useState(null);
  const [discountForm, setDiscountForm] = useState({ discount_amount: '', discount_type: 'scholarship', discount_note: '' });

  const [feeFilters, setFeeFilters] = useState({ q: '', status: '', school_class_id: '' });

  function loadInvoices(page = 1, filters = feeFilters) {
    setInvoiceLoading(true);
    StudentFeeModel.list(page, filters)
      .then((payload) => {
        setInvoices(payload.data ?? []);
        setInvoiceMeta(payload.meta ?? null);
      })
      .catch(() => setInvoiceError('Failed to load student fees.'))
      .finally(() => setInvoiceLoading(false));
  }

  useEffect(() => {
    loadInvoices();
    StudentModel.list(1).then((p) => setStudents(p.data ?? []));
    SchoolClassModel.list(1).then((p) => setClasses(p.data ?? []));
  }, []);

  async function submitFeeType(e) {
    e.preventDefault();
    const payload = { ...feeTypeForm, amount: Number(feeTypeForm.amount) };
    const ok = await feeTypes.save(payload, editingFeeTypeId);
    if (ok) {
      setFeeTypeForm(emptyFeeType);
      setEditingFeeTypeId(null);
    }
  }

  async function submitInvoice(e) {
    e.preventDefault();
    setInvoiceError(null);
    try {
      await StudentFeeModel.create({
        student_id: Number(invoiceForm.student_id),
        fee_type_id: Number(invoiceForm.fee_type_id),
        due_date: invoiceForm.due_date,
      });
      setInvoiceForm(emptyInvoice);
      loadInvoices(invoiceMeta?.current_page ?? 1);
    } catch {
      setInvoiceError('Failed to create invoice.');
    }
  }

  async function bulkAssign(e) {
    e.preventDefault();
    await StudentFeeModel.bulkAssignClass({
      school_class_id: Number(bulkClassId),
      fee_type_id: Number(bulkFeeTypeId),
      due_date: bulkDueDate,
    });
    loadInvoices();
  }

  async function submitPayment(e) {
    e.preventDefault();
    if (!paymentModal) return;
    await StudentFeeModel.recordPayment(paymentModal.id, {
      ...paymentForm,
      amount: Number(paymentForm.amount),
    });
    setPaymentModal(null);
    loadInvoices(invoiceMeta?.current_page ?? 1);
  }

  return (
    <>
      <div className="page-header">
        <h2>Fees</h2>
      </div>
      {feeTypes.error ? <div className="alert error">{feeTypes.error}</div> : null}
      {invoiceError ? <div className="alert error">{invoiceError}</div> : null}

      <ListFilters
        onApply={() => loadInvoices(1, feeFilters)}
        onReset={() => {
          const empty = { q: '', status: '', school_class_id: '' };
          setFeeFilters(empty);
          loadInvoices(1, empty);
        }}
      >
        <label>Search<input value={feeFilters.q} onChange={(e) => setFeeFilters({ ...feeFilters, q: e.target.value })} placeholder="Student or fee title" /></label>
        <label>Status<select value={feeFilters.status} onChange={(e) => setFeeFilters({ ...feeFilters, status: e.target.value })}><option value="">All</option><option value="pending">Pending</option><option value="partial">Partial</option><option value="paid">Paid</option></select></label>
        <label>Class<select value={feeFilters.school_class_id} onChange={(e) => setFeeFilters({ ...feeFilters, school_class_id: e.target.value })}><option value="">All</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.label}</option>)}</select></label>
      </ListFilters>

      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>Fee types</h3>
        <form onSubmit={submitFeeType}>
          <div className="form-grid">
            <label>Name<input value={feeTypeForm.name} onChange={(e) => setFeeTypeForm({ ...feeTypeForm, name: e.target.value })} required /></label>
            <label>Code<input value={feeTypeForm.code} onChange={(e) => setFeeTypeForm({ ...feeTypeForm, code: e.target.value })} required /></label>
            <label>Amount<input type="number" min="0" step="0.01" value={feeTypeForm.amount} onChange={(e) => setFeeTypeForm({ ...feeTypeForm, amount: e.target.value })} required /></label>
            <label>Year<input value={feeTypeForm.academic_year} onChange={(e) => setFeeTypeForm({ ...feeTypeForm, academic_year: e.target.value })} required /></label>
          </div>
          <button type="submit" className="btn">{editingFeeTypeId ? 'Update type' : 'Add fee type'}</button>
        </form>
        <table style={{ marginTop: '1rem' }}>
          <thead><tr><th>Code</th><th>Name</th><th>Amount</th><th /></tr></thead>
          <tbody>
            {feeTypes.items.map((t) => (
              <tr key={t.id}>
                <td>{t.code}</td><td>{t.name}</td><td>{t.amount}</td>
                <td><button type="button" className="btn danger" onClick={() => feeTypes.remove(t.id)}>Delete</button></td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>Assign fee to class</h3>
        <form onSubmit={bulkAssign} className="form-grid">
          <label>Class<select value={bulkClassId} onChange={(e) => setBulkClassId(e.target.value)} required><option value="">—</option>{classes.map((c) => <option key={c.id} value={c.id}>{c.label}</option>)}</select></label>
          <label>Fee type<select value={bulkFeeTypeId} onChange={(e) => setBulkFeeTypeId(e.target.value)} required><option value="">—</option>{feeTypes.items.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}</select></label>
          <label>Due date<input type="date" value={bulkDueDate} onChange={(e) => setBulkDueDate(e.target.value)} required /></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Assign to class</button></div>
        </form>
      </div>

      <div className="card" style={{ marginBottom: '1.25rem' }}>
        <h3 style={{ marginTop: 0 }}>Invoice for one student</h3>
        <form onSubmit={submitInvoice} className="form-grid">
          <label>Student<select value={invoiceForm.student_id} onChange={(e) => setInvoiceForm({ ...invoiceForm, student_id: e.target.value })} required><option value="">—</option>{students.map((s) => <option key={s.id} value={s.id}>{s.full_name}</option>)}</select></label>
          <label>Fee type<select value={invoiceForm.fee_type_id} onChange={(e) => setInvoiceForm({ ...invoiceForm, fee_type_id: e.target.value })} required><option value="">—</option>{feeTypes.items.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}</select></label>
          <label>Due date<input type="date" value={invoiceForm.due_date} onChange={(e) => setInvoiceForm({ ...invoiceForm, due_date: e.target.value })} required /></label>
          <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Create invoice</button></div>
        </form>
      </div>

      <div className="card table-wrap">
        <h3 style={{ marginTop: 0 }}>Student fee records</h3>
        {invoiceLoading ? <p>Loading…</p> : (
          <table>
            <thead><tr><th>Student</th><th>Title</th><th>Amount</th><th>Discount</th><th>Net</th><th>Paid</th><th>Balance</th><th>Status</th><th>Due</th><th /></tr></thead>
            <tbody>
              {invoices.map((row) => (
                <tr key={row.id}>
                  <td>{row.student?.full_name}</td>
                  <td>{row.title}</td>
                  <td>{row.amount}</td>
                  <td>{row.discount_amount ?? 0}{row.discount_type ? ` (${row.discount_type})` : ''}</td>
                  <td>{row.effective_amount ?? row.amount}</td>
                  <td>{row.amount_paid}</td>
                  <td>{row.balance}</td>
                  <td>{row.status}</td>
                  <td>{row.due_date}</td>
                  <td>
                    {can('fees.manage') && row.status !== 'cancelled' ? (
                      <button type="button" className="btn secondary" style={{ marginRight: '0.35rem' }} onClick={() => { setDiscountModal(row); setDiscountForm({ discount_amount: String(row.discount_amount ?? 0), discount_type: row.discount_type || 'scholarship', discount_note: row.discount_note || '' }); }}>Waiver</button>
                    ) : null}
                    {row.status !== 'paid' && row.status !== 'cancelled' ? (
                      <button type="button" className="btn secondary" onClick={() => { setPaymentModal(row); setPaymentForm({ amount: String(row.balance), payment_date: new Date().toISOString().slice(0, 10), payment_method: 'cash', reference: '' }); }}>Record payment</button>
                    ) : null}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {discountModal ? (
        <div className="card" style={{ marginTop: '1rem' }}>
          <h3>Discount / scholarship — {discountModal.student?.full_name}</h3>
          <form
            onSubmit={async (e) => {
              e.preventDefault();
              await StudentFeeModel.applyDiscount(discountModal.id, {
                ...discountForm,
                discount_amount: Number(discountForm.discount_amount),
              });
              setDiscountModal(null);
              loadInvoices(invoiceMeta?.current_page ?? 1);
            }}
            className="form-grid"
          >
            <label>Amount<input type="number" min="0" step="0.01" value={discountForm.discount_amount} onChange={(e) => setDiscountForm({ ...discountForm, discount_amount: e.target.value })} required /></label>
            <label>Type<select value={discountForm.discount_type} onChange={(e) => setDiscountForm({ ...discountForm, discount_type: e.target.value })}><option value="scholarship">Scholarship</option><option value="waiver">Waiver</option><option value="partial">Partial</option><option value="other">Other</option></select></label>
            <label>Note<input value={discountForm.discount_note} onChange={(e) => setDiscountForm({ ...discountForm, discount_note: e.target.value })} /></label>
            <div className="actions"><button type="submit" className="btn">Apply</button><button type="button" className="btn secondary" onClick={() => setDiscountModal(null)}>Cancel</button></div>
          </form>
        </div>
      ) : null}

      {paymentModal ? (
        <div className="card" style={{ marginTop: '1rem' }}>
          <h3>Payment — {paymentModal.student?.full_name}</h3>
          <form onSubmit={submitPayment} className="form-grid">
            <label>Amount<input type="number" step="0.01" value={paymentForm.amount} onChange={(e) => setPaymentForm({ ...paymentForm, amount: e.target.value })} required /></label>
            <label>Date<input type="date" value={paymentForm.payment_date} onChange={(e) => setPaymentForm({ ...paymentForm, payment_date: e.target.value })} required /></label>
            <label>Method<select value={paymentForm.payment_method} onChange={(e) => setPaymentForm({ ...paymentForm, payment_method: e.target.value })}><option value="cash">Cash</option><option value="bank">Bank</option><option value="online">Online</option><option value="cheque">Cheque</option></select></label>
            <label>Reference<input value={paymentForm.reference} onChange={(e) => setPaymentForm({ ...paymentForm, reference: e.target.value })} /></label>
            <div className="actions"><button type="submit" className="btn">Save payment</button><button type="button" className="btn secondary" onClick={() => setPaymentModal(null)}>Cancel</button></div>
          </form>
        </div>
      ) : null}
    </>
  );
}
