/** Immutable reads and writes against a path like ['items', 0, 'description']. */

export function getIn(source, path) {
    return path.reduce((value, key) => (value == null ? undefined : value[key]), source);
}

export function setIn(source, path, value) {
    if (path.length === 0) {
        return value;
    }

    const [key, ...rest] = path;

    if (Array.isArray(source)) {
        const copy = source.slice();
        copy[key] = setIn(source[key], rest, value);

        return copy;
    }

    return {
        ...source,
        [key]: setIn(source?.[key] ?? (typeof rest[0] === 'number' ? [] : {}), rest, value),
    };
}

/** "data.items.0.description" — how Laravel keys its validation errors. */
export function errorKey(path) {
    return ['data', ...path].join('.');
}
