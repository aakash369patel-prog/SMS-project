import { useEffect, useState } from 'react';
import { LibraryModel } from '../../models/LibraryModel.js';
import { StudentModel } from '../../models/StudentModel.js';
import { usePermissions } from '../../controllers/usePermissions.js';

export function LibraryView() {
  const { can } = usePermissions();
  const [books, setBooks] = useState([]);
  const [loans, setLoans] = useState([]);
  const [students, setStudents] = useState([]);
  const [bookForm, setBookForm] = useState({ title: '', isbn: '', author: '', category: '', total_copies: '1' });
  const [issueForm, setIssueForm] = useState({ book_id: '', student_id: '', due_date: '' });

  function load() {
    LibraryModel.listBooks().then((p) => setBooks(p.data ?? []));
    LibraryModel.listLoans().then((p) => setLoans(p.data ?? []));
  }

  useEffect(() => {
    load();
    StudentModel.list(1).then((p) => setStudents(p.data ?? []));
  }, []);

  async function addBook(e) {
    e.preventDefault();
    await LibraryModel.createBook({ ...bookForm, total_copies: Number(bookForm.total_copies) });
    setBookForm({ title: '', isbn: '', author: '', category: '', total_copies: '1' });
    load();
  }

  async function issueBook(e) {
    e.preventDefault();
    await LibraryModel.issue({
      book_id: Number(issueForm.book_id),
      student_id: Number(issueForm.student_id),
      due_date: issueForm.due_date,
    });
    setIssueForm({ book_id: '', student_id: '', due_date: '' });
    load();
  }

  return (
    <>
      <div className="page-header"><h2>Library</h2></div>
      {can('library.manage') ? (
        <>
          <div className="card" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Add book</h3>
            <form onSubmit={addBook} className="form-grid">
              <label>Title<input value={bookForm.title} onChange={(e) => setBookForm({ ...bookForm, title: e.target.value })} required /></label>
              <label>ISBN<input value={bookForm.isbn} onChange={(e) => setBookForm({ ...bookForm, isbn: e.target.value })} /></label>
              <label>Author<input value={bookForm.author} onChange={(e) => setBookForm({ ...bookForm, author: e.target.value })} /></label>
              <label>Copies<input type="number" min="1" value={bookForm.total_copies} onChange={(e) => setBookForm({ ...bookForm, total_copies: e.target.value })} required /></label>
              <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Add</button></div>
            </form>
          </div>
          <div className="card" style={{ marginBottom: '1rem' }}>
            <h3 style={{ marginTop: 0 }}>Issue book</h3>
            <form onSubmit={issueBook} className="form-grid">
              <label>Book<select value={issueForm.book_id} onChange={(e) => setIssueForm({ ...issueForm, book_id: e.target.value })} required><option value="">—</option>{books.map((b) => <option key={b.id} value={b.id}>{b.title} ({b.available_copies} free)</option>)}</select></label>
              <label>Student<select value={issueForm.student_id} onChange={(e) => setIssueForm({ ...issueForm, student_id: e.target.value })} required><option value="">—</option>{students.map((s) => <option key={s.id} value={s.id}>{s.full_name}</option>)}</select></label>
              <label>Due date<input type="date" value={issueForm.due_date} onChange={(e) => setIssueForm({ ...issueForm, due_date: e.target.value })} required /></label>
              <div className="actions" style={{ alignSelf: 'end' }}><button type="submit" className="btn">Issue</button></div>
            </form>
          </div>
        </>
      ) : null}
      <div className="card table-wrap" style={{ marginBottom: '1rem' }}>
        <h3 style={{ marginTop: 0 }}>Catalog</h3>
        <table><thead><tr><th>Title</th><th>Author</th><th>Available</th><th>Total</th></tr></thead><tbody>{books.map((b) => <tr key={b.id}><td>{b.title}</td><td>{b.author}</td><td>{b.available_copies}</td><td>{b.total_copies}</td></tr>)}</tbody></table>
      </div>
      <div className="card table-wrap">
        <h3 style={{ marginTop: 0 }}>Loans</h3>
        <table><thead><tr><th>Book</th><th>Student</th><th>Due</th><th>Status</th><th>Fine</th><th /></tr></thead><tbody>{loans.map((l) => <tr key={l.id}><td>{l.book?.title}</td><td>{l.student?.full_name}</td><td>{l.due_date}</td><td>{l.status}</td><td>{l.fine_amount}</td><td>{can('library.manage') && l.status !== 'returned' ? <button type="button" className="btn secondary" onClick={() => LibraryModel.returnLoan(l.id).then(load)}>Return</button> : null}</td></tr>)}</tbody></table>
      </div>
    </>
  );
}
