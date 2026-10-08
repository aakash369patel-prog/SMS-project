export function Pagination({ meta, onPageChange }) {
  if (!meta || meta.last_page <= 1) {
    return null;
  }

  return (
    <div className="actions" style={{ marginTop: '1rem' }}>
      <button
        type="button"
        className="btn secondary"
        disabled={meta.current_page <= 1}
        onClick={() => onPageChange(meta.current_page - 1)}
      >
        Previous
      </button>
      <span style={{ alignSelf: 'center', color: 'var(--muted)', fontSize: '0.875rem' }}>
        Page {meta.current_page} of {meta.last_page}
      </span>
      <button
        type="button"
        className="btn secondary"
        disabled={meta.current_page >= meta.last_page}
        onClick={() => onPageChange(meta.current_page + 1)}
      >
        Next
      </button>
    </div>
  );
}
