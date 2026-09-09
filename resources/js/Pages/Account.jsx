import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Plus, Trash2, UserRound } from 'lucide-react';
import Button from '../Components/UI/Button';
import { Card, Input } from '../Components/UI/Form';

function BusinessForm({ business }) {
    const { data, setData, put, processing, errors } = useForm({
        name: business?.name ?? '',
        email: business?.email ?? '',
        phone: business?.phone ?? '',
        address: business?.address ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        put('/account/business', { preserveScroll: true });
    };

    return (
        <form onSubmit={submit}>
            <Card
                title="Your business"
                description="This fills the From block on every new document, so you only type it once."
                footer={
                    <Button type="submit" disabled={processing}>
                        Save details
                    </Button>
                }
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <Input
                        id="business-name"
                        label="Business name"
                        value={data.name}
                        error={errors.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    <Input
                        id="business-email"
                        type="email"
                        label="Email"
                        value={data.email}
                        error={errors.email}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <Input
                        id="business-phone"
                        label="Phone"
                        value={data.phone}
                        error={errors.phone}
                        onChange={(e) => setData('phone', e.target.value)}
                    />
                    <div className="sm:col-span-2">
                        <Input
                            id="business-address"
                            as="textarea"
                            rows={3}
                            label="Address"
                            value={data.address}
                            error={errors.address}
                            onChange={(e) => setData('address', e.target.value)}
                        />
                    </div>
                </div>
            </Card>
        </form>
    );
}

function CustomerForm() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        address: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/account/customers', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
            <Input
                id="customer-name"
                label="Name"
                value={data.name}
                error={errors.name}
                onChange={(e) => setData('name', e.target.value)}
            />
            <Input
                id="customer-email"
                type="email"
                label="Email"
                value={data.email}
                error={errors.email}
                onChange={(e) => setData('email', e.target.value)}
            />
            <div className="sm:col-span-2">
                <Input
                    id="customer-address"
                    as="textarea"
                    rows={2}
                    label="Address"
                    value={data.address}
                    error={errors.address}
                    onChange={(e) => setData('address', e.target.value)}
                />
            </div>
            <div className="sm:col-span-2">
                <Button type="submit" variant="secondary" disabled={processing}>
                    <Plus className="h-4 w-4" />
                    Add customer
                </Button>
            </div>
        </form>
    );
}

export default function Account({ business, customers }) {
    const { flash } = usePage().props;

    const removeCustomer = (id) => {
        router.delete(`/account/customers/${id}`, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Account" />

            <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
                <h1 className="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Account</h1>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Details worth typing once.
                </p>

                {flash?.status && (
                    <p className="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                        {flash.status}
                    </p>
                )}

                <div className="mt-6 space-y-5">
                    <BusinessForm business={business} />

                    <Card
                        title="Customers"
                        description="Saved here, they can be dropped into a document in one click."
                    >
                        {customers.length > 0 && (
                            <ul className="mb-6 space-y-2">
                                {customers.map((customer) => (
                                    <li
                                        key={customer.id}
                                        className="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 dark:border-slate-700"
                                    >
                                        <UserRound className="h-4 w-4 shrink-0 text-slate-400" />
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                                                {customer.name}
                                            </div>
                                            {customer.email && (
                                                <div className="truncate text-xs text-slate-500 dark:text-slate-400">
                                                    {customer.email}
                                                </div>
                                            )}
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => removeCustomer(customer.id)}
                                            aria-label={`Remove ${customer.name}`}
                                            className="grid h-8 w-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}

                        <CustomerForm />
                    </Card>
                </div>
            </div>
        </>
    );
}
