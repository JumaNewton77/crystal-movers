/** Link helpers. Company details themselves come from the admin settings (see siteData.tsx). */

/** wa.me link, or null when no real WhatsApp number is configured yet. */
export function whatsappLink(number: string | null | undefined, message: string): string | null {
    if (/x{3}/i.test(number ?? '')) return null;
    let digits = (number ?? '').replace(/\D/g, '');
    // Local Kenyan format 0712 345 678 -> 254712345678, so the link opens the chat with that number.
    if (digits.length === 10 && digits.startsWith('0')) digits = `254${digits.slice(1)}`;
    return digits.length >= 9 && digits.length <= 15 ? `https://wa.me/${digits}?text=${encodeURIComponent(message)}` : null;
}

/** tel: link, or null while the phone is still a placeholder. */
export function telLink(phone: string | null | undefined): string | null {
    const digits = (phone ?? '').replace(/[^\d+]/g, '');
    return digits.replace(/\D/g, '').length >= 9 && !/x{3}/i.test(phone ?? '') ? `tel:${digits}` : null;
}
