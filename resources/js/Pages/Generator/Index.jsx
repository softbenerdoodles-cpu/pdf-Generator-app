import { Head } from '@inertiajs/react';
import DocumentTypeSelector from '../../Components/Generator/DocumentTypeSelector';
import PromptBox from '../../Components/Generator/PromptBox';

export default function Index({ types }) {
    return (
        <>
            <Head title="Templates" />

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h1 className="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        Every document type
                    </h1>
                    <p className="mt-3 text-slate-600 dark:text-slate-400">
                        Pick a template to start from, or describe what you need and we will draft it for you.
                    </p>
                </div>

                <div className="mt-8">
                    <PromptBox />
                </div>

                <div className="mt-12">
                    <DocumentTypeSelector types={types} />
                </div>

                <dl className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {types.map((type) => (
                        <div
                            key={type.key}
                            className="rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <dt className="text-sm font-bold text-slate-900 dark:text-white">{type.name}</dt>
                            <dd className="mt-1 text-sm text-slate-500 dark:text-slate-400">{type.description}</dd>
                        </div>
                    ))}
                </dl>
            </div>
        </>
    );
}
