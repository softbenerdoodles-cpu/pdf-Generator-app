import { Head, Link, useForm } from '@inertiajs/react';
import { Card, Input } from '../../Components/UI/Form';
import Button from '../../Components/UI/Button';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/register');
    };

    return (
        <>
            <Head title="Create an account" />

            <div className="mx-auto max-w-md px-4 py-16 sm:px-6">
                <h1 className="text-center text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Create an account
                </h1>
                <p className="mt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                    Keep your documents, save your business details, and stop retyping customers.
                </p>

                <form onSubmit={submit} className="mt-8">
                    <Card
                        footer={
                            <Button type="submit" disabled={processing} className="w-full">
                                Create account
                            </Button>
                        }
                    >
                        <div className="space-y-4">
                            <Input
                                id="name"
                                label="Your name"
                                autoComplete="name"
                                value={data.name}
                                error={errors.name}
                                onChange={(e) => setData('name', e.target.value)}
                            />
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
                                autoComplete="new-password"
                                value={data.password}
                                error={errors.password}
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            <Input
                                id="password_confirmation"
                                type="password"
                                label="Confirm password"
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                            />
                        </div>
                    </Card>
                </form>

                <p className="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    Already have one?{' '}
                    <Link href="/login" className="font-semibold text-indigo-600 dark:text-indigo-400">
                        Sign in
                    </Link>
                </p>
            </div>
        </>
    );
}
