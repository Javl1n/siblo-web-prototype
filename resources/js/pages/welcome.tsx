import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome to SIBLO">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-background p-6 text-foreground lg:justify-center lg:p-8">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-5xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal text-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-foreground transition-colors hover:border-border"
                                >
                                    Log in
                                </Link>
                                {canRegister && (
                                    <Link
                                        href={register()}
                                        className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal text-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        Register
                                    </Link>
                                )}
                            </>
                        )}
                    </nav>
                </header>

                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="flex w-full max-w-[335px] flex-col lg:max-w-5xl lg:flex-row lg:gap-8">
                        {/* Hero Section */}
                        <div className="mb-6 flex-1 rounded-tl-lg rounded-tr-lg bg-gradient-to-br from-primary to-primary/80 p-8 text-primary-foreground shadow-lg lg:mb-0 lg:rounded-lg lg:p-12">
                            <div className="mb-6">
                                <h1 className="mb-2 text-4xl font-bold lg:text-5xl">
                                    SIBLO
                                </h1>
                                <p className="text-lg font-medium opacity-95 lg:text-xl">
                                    Tungo sa Tagumpay
                                </p>
                                <p className="text-sm opacity-90 lg:text-base">
                                    Path to Success
                                </p>
                            </div>

                            <div className="mb-8 space-y-4">
                                <p className="text-base leading-relaxed lg:text-lg">
                                    An educational RPG platform for Filipino students that gamifies learning through creature collection and evolution.
                                </p>
                                <p className="text-sm leading-relaxed opacity-95 lg:text-base">
                                    Collect and evolve Siblons by performing well on quizzes. Turn academic achievement into an exciting adventure!
                                </p>
                            </div>

                            <div className="space-y-3">
                                {!auth.user && canRegister && (
                                    <Link
                                        href={register()}
                                        className="block w-full rounded-sm bg-background px-6 py-3 text-center font-medium text-primary transition-all hover:bg-secondary hover:text-secondary-foreground"
                                    >
                                        Get Started
                                    </Link>
                                )}
                                {!auth.user && (
                                    <Link
                                        href={login()}
                                        className="block w-full rounded-sm border border-primary-foreground/30 px-6 py-3 text-center font-medium text-primary-foreground transition-all hover:border-primary-foreground/50 hover:bg-primary-foreground/10"
                                    >
                                        Sign In
                                    </Link>
                                )}
                                {auth.user && (
                                    <Link
                                        href={dashboard()}
                                        className="block w-full rounded-sm bg-background px-6 py-3 text-center font-medium text-primary transition-all hover:bg-secondary hover:text-secondary-foreground"
                                    >
                                        Go to Dashboard
                                    </Link>
                                )}
                            </div>
                        </div>

                        {/* Features Section */}
                        <div className="flex-1 rounded-br-lg rounded-bl-lg border border-border bg-card p-6 pb-12 shadow-sm lg:rounded-lg lg:p-12">
                            <h2 className="mb-4 text-xl font-semibold text-card-foreground lg:text-2xl">
                                Platform Features
                            </h2>

                            <div className="space-y-6 text-sm leading-relaxed lg:text-base">
                                {/* For Students */}
                                <div className="rounded-lg bg-muted p-4">
                                    <h3 className="mb-2 font-semibold text-card-foreground">
                                        For Students
                                    </h3>
                                    <ul className="space-y-2 text-muted-foreground">
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>Collect and evolve Siblons by mastering quizzes</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>Engage in real-time battles with other students</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>Track your progress and learning achievements</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>Learning aligned with DepEd K-12 curriculum</span>
                                        </li>
                                    </ul>
                                </div>

                                {/* For Teachers */}
                                <div className="rounded-lg bg-muted p-4">
                                    <h3 className="mb-2 font-semibold text-card-foreground">
                                        For Teachers
                                    </h3>
                                    <ul className="space-y-2 text-muted-foreground">
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>AI-powered quiz generation to reduce workload</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>Comprehensive student progress analytics</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>Create and manage quizzes with ease</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                            <span>Track class performance and identify learning gaps</span>
                                        </li>
                                    </ul>
                                </div>

                                {/* Core Values */}
                                <div className="border-t border-border pt-6">
                                    <h3 className="mb-3 font-semibold text-card-foreground">
                                        Our Mission
                                    </h3>
                                    <p className="text-muted-foreground">
                                        We're addressing the lack of student engagement in the Philippine education system by combining game-based learning with quality educational content.
                                    </p>
                                </div>

                                {/* Target Audience */}
                                <div className="rounded-lg border border-primary/20 bg-primary/5 p-4">
                                    <p className="text-center text-sm font-medium text-card-foreground">
                                        Designed for Filipino students in grades 4-10
                                    </p>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>

                {/* Footer */}
                <footer className="mt-8 w-full max-w-[335px] text-center text-xs text-muted-foreground lg:max-w-5xl">
                    <p>
                        SIBLO - Educational RPG Platform for Filipino Students
                    </p>
                    <p className="mt-1">
                        Powered by Laravel, React, and Inertia.js
                    </p>
                </footer>
            </div>
        </>
    );
}
