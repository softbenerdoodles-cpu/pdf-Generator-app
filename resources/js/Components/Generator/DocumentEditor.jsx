import { Plus, Trash2 } from 'lucide-react';
import FieldControl, { Label } from '../UI/Fields';
import { getIn } from '../../lib/objects';

/** Narrow inputs get a fixed column; prose gets the space that is left. */
const COLUMN_WIDTH = {
    number: '5.5rem',
    money: '8rem',
    date: '10rem',
    select: '10rem',
};

export function Section({ title, description, children, columns = 2 }) {
    return (
        <section className="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {title && (
                <header className="mb-4">
                    <h2 className="text-sm font-bold text-slate-900 dark:text-white">{title}</h2>
                    {description && (
                        <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{description}</p>
                    )}
                </header>
            )}

            <div className={`grid gap-4 ${columns === 2 ? 'sm:grid-cols-2' : ''}`}>{children}</div>
        </section>
    );
}

/** A fixed set of sub-fields — an address block, a named party. */
export function Group({ field, path, value, onChange, errors }) {
    return (
        <fieldset className="sm:col-span-2">
            <legend className="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                {field.label}
            </legend>
            <div className="grid gap-4 sm:grid-cols-2">
                {field.fields.map((child) => (
                    <div key={child.name} className={child.type === 'textarea' ? 'sm:col-span-2' : ''}>
                        <FieldControl
                            field={child}
                            path={[...path, child.name]}
                            value={getIn(value, [child.name])}
                            onChange={onChange}
                            errors={errors}
                        />
                    </div>
                ))}
            </div>
        </fieldset>
    );
}

/** A repeating list of rows: line items, clauses, sections. */
export function Repeater({ field, path, value, onChange, errors }) {
    const rows = Array.isArray(value) ? value : [];
    const min = field.min ?? 0;
    const stacked = field.fields.some((child) => child.type === 'textarea');

    const blankRow = () =>
        Object.fromEntries(field.fields.map((child) => [child.name, child.default ?? '']));

    const addRow = () => onChange(path, [...rows, blankRow()]);
    const removeRow = (index) => onChange(path, rows.filter((_, i) => i !== index));

    const columns = stacked
        ? null
        : field.fields.map((child) => COLUMN_WIDTH[child.type] ?? 'minmax(0, 1fr)').join(' ') + ' 2.5rem';

    const rowError = errors?.[['data', ...path].join('.')]?.[0];

    return (
        <div className="sm:col-span-2">
            <div className="mb-2 flex items-center justify-between">
                <span className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    {field.label}
                </span>
                <button
                    type="button"
                    onClick={addRow}
                    className="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10"
                >
                    <Plus className="h-3.5 w-3.5" />
                    Add
                </button>
            </div>

            {!stacked && (
                <div
                    className="mb-1.5 hidden gap-3 px-1 sm:grid"
                    style={{ gridTemplateColumns: columns }}
                    aria-hidden="true"
                >
                    {field.fields.map((child) => (
                        <span key={child.name} className="text-[11px] font-semibold text-slate-500 dark:text-slate-500">
                            {child.label}
                        </span>
                    ))}
                </div>
            )}

            <div className="space-y-3">
                {rows.map((row, index) => (
                    <div
                        key={index}
                        className={
                            stacked
                                ? 'rounded-xl border border-slate-200 p-4 dark:border-slate-700'
                                : 'grid items-start gap-3'
                        }
                        style={stacked ? undefined : { gridTemplateColumns: columns }}
                    >
                        {stacked ? (
                            <>
                                <div className="mb-3 flex items-center justify-between">
                                    <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        {index + 1}
                                    </span>
                                    {rows.length > min && (
                                        <button
                                            type="button"
                                            onClick={() => removeRow(index)}
                                            aria-label={`Remove ${field.label} ${index + 1}`}
                                            className="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>
                                <div className="space-y-3">
                                    {field.fields.map((child) => (
                                        <FieldControl
                                            key={child.name}
                                            field={child}
                                            path={[...path, index, child.name]}
                                            value={getIn(row, [child.name])}
                                            onChange={onChange}
                                            errors={errors}
                                        />
                                    ))}
                                </div>
                            </>
                        ) : (
                            <>
                                {field.fields.map((child) => (
                                    <FieldControl
                                        key={child.name}
                                        field={child}
                                        path={[...path, index, child.name]}
                                        value={getIn(row, [child.name])}
                                        onChange={onChange}
                                        errors={errors}
                                        hideLabel
                                    />
                                ))}
                                <button
                                    type="button"
                                    onClick={() => removeRow(index)}
                                    disabled={rows.length <= min}
                                    aria-label={`Remove row ${index + 1}`}
                                    className="mt-0.5 grid h-9 w-9 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-rose-500/10"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </>
                        )}
                    </div>
                ))}
            </div>

            {rowError && <p className="mt-2 text-xs text-rose-600 dark:text-rose-400">{rowError}</p>}
        </div>
    );
}

/** Renders any field, dispatching on its type. */
export function AnyField({ field, path, value, onChange, errors }) {
    if (field.type === 'group') {
        return <Group field={field} path={path} value={value} onChange={onChange} errors={errors} />;
    }

    if (field.type === 'repeater') {
        return <Repeater field={field} path={path} value={value} onChange={onChange} errors={errors} />;
    }

    return (
        <div className={field.type === 'textarea' ? 'sm:col-span-2' : ''}>
            <FieldControl field={field} path={path} value={value} onChange={onChange} errors={errors} />
        </div>
    );
}

/**
 * The default editor: every field in schema order.
 *
 * Types with a hand-arranged editor (invoice, quote, receipt) use theirs
 * instead; everything else is well served by this.
 */
export default function DocumentEditor({ fields, data, onChange, errors }) {
    return (
        <Section>
            {fields.map((field) => (
                <AnyField
                    key={field.name}
                    field={field}
                    path={[field.name]}
                    value={getIn(data, [field.name])}
                    onChange={onChange}
                    errors={errors}
                />
            ))}
        </Section>
    );
}
