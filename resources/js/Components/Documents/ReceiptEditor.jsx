import { AnyField, Section } from '../Generator/DocumentEditor';
import { getIn } from '../../lib/objects';
import { sectioned } from './layout';

export default function ReceiptEditor({ fields, data, onChange, errors }) {
    const sections = sectioned(fields, [
        {
            title: 'Details',
            names: ['number', 'paid_on', 'currency'],
        },
        {
            title: 'Who paid whom',
            names: ['from', 'payer'],
        },
        {
            title: 'What was paid for',
            names: ['items'],
        },
        {
            title: 'How it was paid',
            names: ['method', 'reference', 'notes'],
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
