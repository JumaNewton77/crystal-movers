import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from 'react';
import { api, UNAUTHORIZED_EVENT } from '@/shared/api';

export interface AdminUser {
    id: number;
    name: string;
    email: string;
}

interface AuthState {
    user: AdminUser | null;
    loading: boolean;
    login: (email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [user, setUser] = useState<AdminUser | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api<{ user: AdminUser | null }>('/auth/me')
            .then((r) => setUser(r.data.user))
            .catch(() => setUser(null))
            .finally(() => setLoading(false));

        // Any admin request answered with 401 (expired session) sends the person back to the login page.
        const onUnauthorized = () => setUser(null);
        window.addEventListener(UNAUTHORIZED_EVENT, onUnauthorized);
        return () => window.removeEventListener(UNAUTHORIZED_EVENT, onUnauthorized);
    }, []);

    const login = useCallback(async (email: string, password: string) => {
        const r = await api<{ user: AdminUser }>('/auth/login', { method: 'POST', body: { email, password } });
        setUser(r.data.user);
    }, []);

    const logout = useCallback(async () => {
        try {
            await api('/auth/logout', { method: 'POST' });
        } finally {
            setUser(null);
        }
    }, []);

    return <AuthContext.Provider value={{ user, loading, login, logout }}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthState {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth must be used inside AuthProvider');
    return ctx;
}
