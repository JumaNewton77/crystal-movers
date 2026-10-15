import { useState, type FormEvent } from 'react';
import { Navigate } from 'react-router-dom';
import { ApiError, errorMessage } from '@/shared/api';
import { useAuth } from '../auth';
import { Alert, Button, Field, Input } from '../components/ui';

export default function LoginPage() {
    const { user, login } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');
    const [fieldError, setFieldError] = useState('');

    if (user) return <Navigate to="/" replace />;

    async function submit(e: FormEvent) {
        e.preventDefault();
        setBusy(true);
        setError('');
        setFieldError('');
        try {
            await login(email, password);
        } catch (err) {
            if (err instanceof ApiError && err.status === 422) setFieldError(err.field('email') ?? err.message);
            else setError(err instanceof ApiError && err.status === 429 ? 'Too many attempts. Please wait a minute and try again.' : errorMessage(err));
        } finally {
            setBusy(false);
        }
    }

    return (
        <div className="flex min-h-screen items-center justify-center bg-ink px-4">
            <form onSubmit={submit} className="w-full max-w-sm space-y-4 rounded-lg bg-white p-6">
                <h1 className="rounded-md bg-ink px-3 py-2 text-lg font-bold text-white">Crystal Movers Admin</h1>
                <Alert>{error}</Alert>
                <Field label="Email" error={fieldError}>
                    <Input type="email" autoComplete="username" required value={email} onChange={(e) => setEmail(e.target.value)} />
                </Field>
                <Field label="Password">
                    <Input type="password" autoComplete="current-password" required value={password} onChange={(e) => setPassword(e.target.value)} />
                </Field>
                <Button type="submit" disabled={busy} className="w-full">
                    {busy ? 'Signing in…' : 'Sign in'}
                </Button>
            </form>
        </div>
    );
}
