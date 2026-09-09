import { AnyField, Section } from '../Generator/DocumentEditor';
import { getIn } from '../../lib/objects';
import { sectioned } from './layout';

/**
 * The editor behind invoices, quotes and estimates.
 *
 * They share a schema, so they share this layout; only the second date field
 * differs, and it is picked up by name from whichever the template declares.
 */
export default function BillingEditor({ fields, data, onChange, errors }) {
    const sections = sectioned(fields, [
        {
            title: 'Details',
            names: ['number', 'issue_date', 'due_date', 'valid_until', 'currency'],
        },
        {
            title: 'Who it is between',
            names: ['from', 'to'],
        },
        {
            title: 'Line items',
            description: 'Quantity times unit price. Totals are worked out for you.',
            names: ['items'],
        },
        {
            title: 'Adjustments and notes',
            names: ['tax_rate', 'discount', 'notes'],
        },
    ]);

    return (
        <div className="space-y-4">
            {sections.map((section) => (
                <Section key={section.title} title={section.title} description={section.description}>
                    {section.fields.map((field) => (
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
            ))}
        </div>
    );
}
