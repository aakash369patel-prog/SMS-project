export function SimpleBarChart({ title, items, valueKey = 'value', labelKey = 'label', max }) {
  const peak = max ?? Math.max(1, ...items.map((i) => Number(i[valueKey]) || 0));

  return (
    <div className="chart-card">
      {title ? <h3 className="chart-title">{title}</h3> : null}
      <div className="bar-chart">
        {items.map((item) => {
          const val = Number(item[valueKey]) || 0;
          const pct = Math.round((val / peak) * 100);
          return (
            <div key={item[labelKey] ?? item.label} className="bar-row">
              <span className="bar-label">{item[labelKey] ?? item.label}</span>
              <div className="bar-track">
                <div className="bar-fill" style={{ width: `${pct}%` }} />
              </div>
              <span className="bar-value">{val}</span>
            </div>
          );
        })}
      </div>
    </div>
  );
}
