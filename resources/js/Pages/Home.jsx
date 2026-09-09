import { Head } from '@inertiajs/react';
import { Heart, Lock, Shield, Zap } from 'lucide-react';
import DocumentTypeSelector from '../Components/Generator/DocumentTypeSelector';
import HeroCards from '../Components/Generator/HeroCards';
import PromptBox from '../Components/Generator/PromptBox';

const FEATURES = [
    {
        icon: Zap,
        title: 'Fast & Easy',
        body: 'Create and download in seconds.',
        tone: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    },
    {
        icon: Lock,
        title: 'No Login Required',
        body: '100% free, no account needed.',
        tone: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    },
    {
        icon: Shield,
        title: 'Your Privacy Matters',
        body: 'Your documents are not stored.',
        tone: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    },
    {
        icon: Heart,
        title: 'Professionally Designed',
        body: 'Beautiful templates for every need.',
        tone: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    },
];

export default function Home({ types, examples }) {
    return (
        <>
            <Head title="Free PDF Generator" />

            <section className="relative overflow-hidden bg-gradient-to-b from-blue-50/70 via-white to-white dark:from-indigo-500/5 dark:via-slate-950 dark:to-slate-950">
                <div className="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 lg:px-8 lg:pt-20">
                    <HeroCards />

                    <div className="mx-auto max-w-4xl text-center">
                        <p className="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.14em] text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                            Free <span aria-hidden="true">•</span> No login <span aria-hidden="true">•</span> No limits
                        </p>

                        <h1 className="mt-6 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                            Create Professional PDFs
                            <span className="mt-1 block text-indigo-600 dark:text-indigo-400">in Seconds</span>
                        </h1>

                        <p className="mx-auto mt-5 max-w-xl text-base text-slate-600 sm:text-lg dark:text-slate-400">
                            Generate invoices, quotes, receipts, contracts and more — for free. No account required.
                            Just create and download.
                        </p>
                    </div>

                    <div className="mt-9">
                        <PromptBox examples={examples} />
                    </div>

                    <div className="mt-14">
                        <div className="mb-5 flex items-center gap-4">
                            <span className="h-px flex-1 bg-slate-200 dark:bg-slate-800" />
                            <span className="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                                Or choose a document type
                            </span>
                            <span className="h-px flex-1 bg-slate-200 dark:bg-slate-800" />
                        </div>

                        <DocumentTypeSelector types={types} limit={8} showViewAll />
                    </div>
                </div>
            </section>

            <section className="border-t border-slate-200/80 bg-slate-50/60 py-12 dark:border-slate-800 dark:bg-slate-900/40">
                <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                    {FEATURES.map(({ icon: Icon, title, body, tone }) => (
                        <div key={title} className="flex items-start gap-4">
                            <span className={`grid h-11 w-11 shrink-0 place-items-center rounded-2xl ${tone}`}>
                                <Icon className="h-5 w-5" />
                            </span>
                            <div>
                                <div className="text-sm font-bold text-slate-900 dark:text-white">{title}</div>
                                <div className="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{body}</div>
                            </div>
                        </div>
                    ))}
                </div>
            </section>
        </>
    );
}
