<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Fortify;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

final readonly class FortifyBackendOptions
{
    public function __construct(
        public bool $apply,
        public bool $registration,
        public bool $resetPasswords,
        public bool $verifyEmail,
        public bool $twoFactor,
        public bool $passkeys,
        public bool $migrate,
        public bool $backup,
        public bool $force,
        public bool $testRoute
    ) {}

    public static function fromInput(InputInterface $input): self
    {
        self::guardContradiction($input, 'registration', 'no-registration');
        self::guardContradiction($input, 'reset-passwords', 'no-reset-passwords');
        self::guardContradiction($input, 'verify-email', 'no-verify-email');

        return new self(
            apply: (bool) $input->getOption('apply'),
            registration: ! $input->hasParameterOption('--no-registration'),
            resetPasswords: ! $input->hasParameterOption('--no-reset-passwords'),
            verifyEmail: ! $input->hasParameterOption('--no-verify-email'),
            twoFactor: (bool) $input->getOption('two-factor'),
            passkeys: (bool) $input->getOption('passkeys'),
            migrate: ! $input->getOption('no-migrate'),
            backup: ! $input->getOption('no-backup'),
            force: (bool) $input->getOption('force'),
            testRoute: (bool) $input->getOption('test-route'),
        );
    }

    private static function guardContradiction(InputInterface $input, string $enabledFlag, string $disabledFlag): void
    {
        if ($input->hasParameterOption('--'.$enabledFlag) && $input->hasParameterOption('--'.$disabledFlag)) {
            throw new InvalidArgumentException(
                sprintf('Options --%s and --%s cannot be used together.', $enabledFlag, $disabledFlag)
            );
        }
    }
}
