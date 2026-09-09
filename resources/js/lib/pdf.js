/**
 * Talking to the PDF routes.
 *
 * Draft data is posted and the bytes come straight back — nothing is written
 * to the server, which is what keeps guest documents unstored.
 */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function post(url, body, signal) {
    const response = await fetch(url, {
        method: 'POST',
        signal,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/pdf, application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });

    if (response.ok) {
        return response.blob();
    }

    throw await validationError(response);
}

/** Turn a 422 into something the editor can show against its fields. */
async function validationError(response) {
    if (response.status !== 422) {
        const error = new Error('That document could not be rendered.');
        error.errors = {};

        return error;
    }

    const payload = await response.json().catch(() => ({}));
    const errors = payload.errors ?? {};
    const first = Object.values(errors)[0]?.[0];

    const error = new Error(first ?? 'Some details are missing.');
    error.errors = errors;

    return error;
}

export function renderPreview(template, data, signal) {
    return post('/pdf/preview', { template, data }, signal);
}

export async function downloadPdf(template, data, filename) {
    const blob = await post('/pdf/download', { template, data });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();

    // Give the browser a moment to start the download before revoking.
    setTimeout(() => URL.revokeObjectURL(url), 10_000);
}
