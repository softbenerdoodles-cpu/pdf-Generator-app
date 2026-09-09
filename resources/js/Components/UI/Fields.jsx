import { errorKey } from '../../lib/objects';

const CONTROL =
    'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm transition ' +
    'placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 ' +
    'dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-600';

const CONTROL_ERROR = 'border-rose-400 focus:border-rose-400 focus:ring-rose-500/20 dark:border-rose-500/60';

export function Label({ field, htmlFor }) {
    return (
        <label htmlFor={htmlFor} className="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-slate-400">
            {field.label}
            {field.required && <span className="ml-0.5 text-rose-500">*</span>}
        </label>
    );
}

/**
 * One scalar input, chosen by the field's declared type.
 *
 * The schema comes from the server, so a field can never be rendered under
 * different terms than it will be validated by.
 */
export default function FieldControl({ field, path, value, onChange, errors, hideLabel = false }) {
    const id = errorKey(path).replace(/\./g, '-');
    const error = errors?.[errorKey(path)]?.[0];
    const classes = `${CONTROL} ${error ? CONTROL_ERROR : ''}`;
    const set = (next) => onChange(path, next);
    const common = {
        id,
        value: value ?? '',
        onChange: (event) => set(event.target.value),
        placeholder: field.placeholder,
        'aria-invalid': error ? 'true' : undefined,
        className: classes,
    };

    return (
        <div>
            {!hideLabel && <Label field={field} htmlFor={id} />}

            {field.type === 'textarea' && <textarea {...common} rows={field.name === 'body' ? 8 : 3} />}

            {field.type === 'select' && (
                <select {...common}>
                    {Object.entries(field.options ?? {}).map(([key, label]) => (
                        <option key={key} value={key}>
                            {label}
                        </option>
                    ))}
                </select>
            )}

            {field.type === 'date' && <input type="date" {...common} />}

            {field.type === 'email' && <input type="email" {...common} />}

            {(field.type === 'number' || field.type === 'money') && (
                <input
                    type="number"
                    step={field.type === 'money' ? '0.01' : 'any'}
                    min={field.type === 'money' ? '0' : undefined}
                    inputMode="decimal"
                    {...common}
                />
            )}

            {field.type === 'text' && <input type="text" {...common} />}

            {error ? (
                <p className="mt-1 text-xs text-rose-600 dark:text-rose-400">{error}</p>
            ) : (
                field.help && <p className="mt-1 text-xs text-slate-400 dark:text-slate-500">{field.help}</p>
            )}
        </div>
    );
}
