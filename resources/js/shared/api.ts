/**
 * Fetch wrapper for the Laravel API (same origin, Sanctum cookie authentication).
 * - Fetches the CSRF cookie on first write, then sends it as X-XSRF-TOKEN.
 * - Always resolves to the { success, message, data } envelope or throws ApiError.
 * - Turns network failures into a friendly ApiError (status 0).
 */
export interface ApiOk<T> {
    success: true;
    message: string;
    data: T;
}

export class ApiError extends Error {
    constructor(
        public status: number,
        message: string,
        public errors: Record<string, string[]> = {},
    ) {
        super(message);
    }

    /** First validation message for a field (e.g. "email" or "attachments.0"). */
    field(name: string): string | undefined {
        const direct = this.errors[name];
        if (direct?.length) return direct[0];
        const nested = Object.keys(this.errors).find((key) => key.startsWith(`${name}.`));
        return nested ? this.errors[nested][0] : undefined;
    }
}

export const UNAUTHORIZED_EVENT = 'sm:unauthorized';

function xsrfToken(): string | null {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : null;
}

async function ensureCsrfCookie(): Promise<void> {
    if (xsrfToken()) return;
    try {
        await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
    } catch {
        // The real request below will surface a network error.
    }
}

export async function api<T = unknown>(
    path: string,
    options: { method?: string; body?: unknown } = {},
): Promise<ApiOk<T>> {
    const method = (options.method ?? 'GET').toUpperCase();
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    if (method !== 'GET') {
        await ensureCsrfCookie();
        const token = xsrfToken();
        if (token) headers['X-XSRF-TOKEN'] = token;
    }

    let body: BodyInit | undefined;
    if (options.body instanceof FormData) {
        body = options.body; // the browser sets the multipart boundary
    } else if (options.body !== undefined) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(options.body);
    }

    let response: Response;
    try {
        response = await fetch(`/api${path}`, { method, headers, body, credentials: 'same-origin' });
    } catch {
        throw new ApiError(0, 'We could not reach the server. Please check your internet connection and try again.');
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        if (response.status === 401 && path.startsWith('/admin')) {
            window.dispatchEvent(new Event(UNAUTHORIZED_EVENT));
        }
        const fallback =
            response.status >= 500
                ? 'Something went wrong on our side. Please try again shortly.'
                : response.status === 413
                  ? 'The upload is too large.'
                  : 'The request could not be completed.';
        throw new ApiError(response.status, data?.message ?? fallback, data?.errors ?? {});
    }

    return data as ApiOk<T>;
}

/** Message safe to show to a person for any thrown value. */
export function errorMessage(error: unknown): string {
    return error instanceof ApiError ? error.message : 'Something went wrong. Please try again.';
}
