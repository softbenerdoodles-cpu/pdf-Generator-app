import { Head, Link, useForm } from '@inertiajs/react';
import { Card, Input } from '../../Components/UI/Form';
import Button from '../../Components/UI/Button';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Sign in" />

            <div className="mx-auto max-w-md px-4 py-16 sm:px-6">
                <h1 className="text-center text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Welcome back
                </h1>
                <p className="mt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                    Sign in to reach your saved documents. Creating one never needs an account.
                </p>

                <form onSubmit={submit} className="mt-8">
                    <Card
                        footer={
                            <Button type="submit" disabled={processing} className="w-full">
                                Sign in
                            </Button>
                        }
                    >
                        <div className="space-y-4">
                            <Input
                                id="email"
                                type="email"
                                label="Email"
                                autoComplete="username"
                                value={data.email}
                                error={errors.email}
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            <Input
                                id="password"
                                type="password"
                                label="Password"
                                autoComplete="current-password"
                                value={data.password}
                                error={errors.password}
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            <label className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                Keep me signed in
                            </label>
                        </div>
                    </Card>
                </form>

                <p className="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    No account?{' '}
                    <Link href="/register" className="font-semibold text-indigo-600 dark:text-indigo-400">
                        Create one
                    </Link>
                </p>
            </div>
        </>
    );
}
