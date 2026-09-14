import { Form, Head, Link } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { useEffect, useState } from 'react';
import AlertError from '@/components/alert-error';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send, store } from '@/routes/verification';

const OTP_LENGTH = 6;
const RESEND_COOLDOWN_SECONDS = 60;

export default function VerifyEmail({ status }: { status?: string }) {
    const [code, setCode] = useState('');
    const [cooldown, setCooldown] = useState(0);
    const justResent = status === 'otp-sent';

    useEffect(() => {
        if (justResent) {
            setCooldown(RESEND_COOLDOWN_SECONDS);
        }
    }, [justResent]);

    useEffect(() => {
        if (cooldown <= 0) {
            return;
        }

        const timer = setInterval(() => {
            setCooldown((seconds) => Math.max(0, seconds - 1));
        }, 1000);

        return () => clearInterval(timer);
    }, [cooldown]);

    return (
        <>
            <Head title="Email verification" />

            <div className="space-y-6">
                <p className="text-muted-foreground text-center text-sm">
                    We emailed a 6-digit verification code to your address.
                    Enter it below to confirm your account.
                </p>

                {justResent && (
                    <div className="text-success text-center text-sm font-medium">
                        A new verification code has been sent to your email
                        address.
                    </div>
                )}

                <Form
                    {...store.form()}
                    resetOnError
                    resetOnSuccess
                    className="space-y-4"
                >
                    {({ errors, processing }) => {
                        const isLockedOut = Boolean(
                            errors.code
                                ?.toLowerCase()
                                .includes('too many attempts'),
                        );

                        return (
                            <>
                                <div className="flex flex-col items-center justify-center space-y-3 text-center">
                                    <div className="flex w-full items-center justify-center">
                                        <InputOTP
                                            name="code"
                                            maxLength={OTP_LENGTH}
                                            value={code}
                                            onChange={setCode}
                                            disabled={processing || isLockedOut}
                                            pattern={REGEXP_ONLY_DIGITS}
                                            autoFocus
                                        >
                                            <InputOTPGroup>
                                                {Array.from(
                                                    { length: OTP_LENGTH },
                                                    (_, index) => (
                                                        <InputOTPSlot
                                                            key={index}
                                                            index={index}
                                                        />
                                                    ),
                                                )}
                                            </InputOTPGroup>
                                        </InputOTP>
                                    </div>

                                    {isLockedOut ? (
                                        <AlertError
                                            title="Too many attempts"
                                            errors={[errors.code ?? '']}
                                        />
                                    ) : (
                                        <InputError message={errors.code} />
                                    )}
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={
                                        processing ||
                                        isLockedOut ||
                                        code.length < OTP_LENGTH
                                    }
                                >
                                    {processing && <Spinner />}
                                    Verify email
                                </Button>
                            </>
                        );
                    }}
                </Form>

                <Form {...send.form()} className="space-y-4">
                    {({ processing }) => (
                        <>
                            <Button
                                disabled={processing || cooldown > 0}
                                variant="secondary"
                                className="w-full"
                            >
                                {processing && <Spinner />}
                                {cooldown > 0
                                    ? `Resend code in ${cooldown}s`
                                    : 'Resend code'}
                            </Button>

                            <Link
                                href={logout()}
                                as="button"
                                className="text-muted-foreground hover:text-foreground mx-auto block text-center text-sm underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Log out
                            </Link>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verify your email',
    description:
        'Enter the 6-digit code we sent to your email address to continue.',
};
