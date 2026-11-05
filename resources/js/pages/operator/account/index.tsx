import { Head, Link } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard, logout } from '@/routes';
import { index as accountIndex } from '@/routes/account';

interface AccountProps {
    account: {
        firstName: string;
        lastName: string;
        company: string;
        email: string;
        phone: string;
    };
}

export default function AccountIndex({ account }: AccountProps) {
    const [firstName, setFirstName] = useState(account.firstName);
    const [lastName, setLastName] = useState(account.lastName);
    const [company, setCompany] = useState(account.company);

    const handleProfile = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        toast.success('Account updated.');
    };

    const handleEmail = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        toast.success('Email updated.');
        event.currentTarget.reset();
    };

    const handlePhone = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        toast.success('Phone updated.');
        event.currentTarget.reset();
    };

    return (
        <>
            <Head title="Account info" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Account info
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Update your profile and password. Changing email or
                        phone needs a one-time code.
                    </p>
                </div>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <h2 className="mb-4 text-lg font-semibold">Profile</h2>
                    <form
                        onSubmit={handleProfile}
                        className="grid gap-4 sm:grid-cols-2"
                    >
                        <div className="space-y-2">
                            <Label htmlFor="first-name">First name</Label>
                            <Input
                                id="first-name"
                                value={firstName}
                                onChange={(event) =>
                                    setFirstName(event.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="last-name">Last name</Label>
                            <Input
                                id="last-name"
                                value={lastName}
                                onChange={(event) =>
                                    setLastName(event.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-2 sm:col-span-2">
                            <Label htmlFor="company">Company name</Label>
                            <Input
                                id="company"
                                value={company}
                                onChange={(event) =>
                                    setCompany(event.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                value={account.email}
                                readOnly
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="phone">Phone</Label>
                            <Input
                                id="phone"
                                value={account.phone}
                                readOnly
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="password">New password</Label>
                            <Input
                                id="password"
                                type="password"
                                minLength={8}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="password-confirm">
                                Confirm password
                            </Label>
                            <Input
                                id="password-confirm"
                                type="password"
                                minLength={8}
                            />
                        </div>
                        <div className="sm:col-span-2">
                            <Button
                                type="submit"
                                className="bg-brass text-brass-ink hover:bg-brass/90"
                            >
                                Update account
                            </Button>
                        </div>
                    </form>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold">
                            Update email
                        </h2>
                        <form onSubmit={handleEmail} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="new-email">New email</Label>
                                <Input
                                    id="new-email"
                                    type="email"
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="email-code">6-digit code</Label>
                                <Input
                                    id="email-code"
                                    inputMode="numeric"
                                    maxLength={6}
                                    required
                                    placeholder="Sent to your new email"
                                />
                            </div>
                            <Button type="submit" variant="outline">
                                Verify email
                            </Button>
                        </form>
                    </div>

                    <div className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold">
                            Update phone
                        </h2>
                        <form onSubmit={handlePhone} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="new-phone">New phone</Label>
                                <Input
                                    id="new-phone"
                                    inputMode="tel"
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="phone-code">6-digit code</Label>
                                <Input
                                    id="phone-code"
                                    inputMode="numeric"
                                    maxLength={6}
                                    required
                                    placeholder="Texted to your new phone"
                                />
                            </div>
                            <Button type="submit" variant="outline">
                                Verify phone
                            </Button>
                        </form>
                    </div>
                </div>

                <Button variant="outline" asChild>
                    <Link href={logout()} method="post" as="button">
                        Sign out
                    </Link>
                </Button>
            </div>
        </>
    );
}

AccountIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Account info', href: accountIndex() },
    ],
};
