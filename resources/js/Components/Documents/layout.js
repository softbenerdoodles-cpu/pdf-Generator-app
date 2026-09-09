/**
 * Splits a template's fields into named sections for the hand-arranged
 * editors. Any field a layout does not mention still gets rendered, in a
 * trailing section, so a schema change can never silently hide an input.
 */
export function sectioned(fields, layout) {
    const byName = new Map(fields.map((field) => [field.name, field]));
    const used = new Set();

    const sections = layout
        .map(({ title, description, names, columns }) => {
            const picked = names
                .map((name) => byName.get(name))
                .filter(Boolean);

            picked.forEach((field) => used.add(field.name));

            return { title, description, columns, fields: picked };
        })
        .filter((section) => section.fields.length > 0);

    const leftovers = fields.filter((field) => !used.has(field.name));

    return leftovers.length > 0
        ? [...sections, { title: 'More details', fields: leftovers }]
        : sections;
}
