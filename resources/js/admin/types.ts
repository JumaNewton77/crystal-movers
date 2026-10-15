export interface Attachment {
    id: number;
    original_name: string;
    mime_type: string;
    size_bytes: number;
    is_image: boolean;
    url: string;
}

export interface HistoryEntry {
    from: string | null;
    to: string;
    by: string | null;
    note: string | null;
    created_at: string;
}

export interface Quote {
    id: number;
    reference: string;
    name: string;
    phone: string;
    email: string | null;
    service: { id: number; title: string; slug: string } | null;
    category: { id: number; name: string } | null;
    move_type: string | null;
    move_type_label: string;
    from_location: string | null;
    to_location: string | null;
    location: string | null;
    preferred_date: string | null;
    preferred_time: string | null;
    details: Record<string, string> | null;
    contact_consent: boolean;
    message: string | null;
    status: string;
    status_label: string;
    admin_notes: string | null;
    created_at: string;
    attachments_count?: number;
    attachments?: Attachment[];
    history?: HistoryEntry[];
}

export interface Message {
    id: number;
    name: string;
    phone: string | null;
    email: string;
    subject: string | null;
    message: string;
    status: 'new' | 'read' | 'replied' | 'archived';
    admin_notes: string | null;
    created_at: string;
}
