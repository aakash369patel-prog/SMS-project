export function FormField({ label, name, error, children }) {
  return (
    <label htmlFor={name}>
      {label}
      {children}
      {error ? <span className="field-error">{error[0]}</span> : null}
    </label>
  );
}
