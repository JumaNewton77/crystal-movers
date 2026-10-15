import en, { type Messages } from './en';

type Dictionary = Messages | Record<string, unknown>;

// Register more languages here later, e.g. `sw: () => import('./sw')`.
const dictionaries: Record<string, Dictionary> = { en };

let locale = 'en';

export function setLocale(next: string): void {
    if (next in dictionaries) {
        locale = next;
    }
}

export function getLocale(): string {
    return locale;
}

/** Translate a dotted key such as `nav.home`. Falls back to English, then the key. */
export function t(key: string, params: Record<string, string | number> = {}): string {
    const lookup = (dict: Dictionary): string | undefined => {
        let node: unknown = dict;
        for (const part of key.split('.')) {
            if (typeof node !== 'object' || node === null) return undefined;
            node = (node as Record<string, unknown>)[part];
        }
        return typeof node === 'string' ? node : undefined;
    };

    const template = lookup(dictionaries[locale]) ?? lookup(en) ?? key;
    return template.replace(/\{(\w+)\}/g, (_, name: string) => String(params[name] ?? `{${name}}`));
}
