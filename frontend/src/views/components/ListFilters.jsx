export function ListFilters({ children, onApply, onReset }) {
  return (
    <div className="card list-filters" style={{ marginBottom: '1rem' }}>
      <div className="form-grid">{children}</div>
      <div className="actions">
        <button type="button" className="btn" onClick={onApply}>
          Apply filters
        </button>
        {onReset ? (
          <button type="button" className="btn secondary" onClick={onReset}>
            Reset
          </button>
        ) : null}
      </div>
    </div>
  );
}
