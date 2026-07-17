<?php

declare(strict_types=1);

use Maxiviper117\Laraprep\Command\FortifyBackendCommand;
use Maxiviper117\Laraprep\Fortify\FortifyBackendOptions;
use Symfony\Component\Console\Input\ArgvInput;

it('resolves default fortify backend options', function (): void {
    $options = FortifyBackendOptions::fromInput(new ArgvInput(['laraprep'], (new FortifyBackendCommand)->getDefinition()));

    expect($options->apply)->toBeFalse()
        ->and($options->registration)->toBeTrue()
        ->and($options->resetPasswords)->toBeTrue()
        ->and($options->verifyEmail)->toBeTrue()
        ->and($options->twoFactor)->toBeFalse()
        ->and($options->passkeys)->toBeFalse()
        ->and($options->migrate)->toBeTrue()
        ->and($options->backup)->toBeTrue()
        ->and($options->force)->toBeFalse()
        ->and($options->testRoute)->toBeFalse();
});

it('rejects contradictory fortify backend options', function (): void {
    FortifyBackendOptions::fromInput(new ArgvInput(['laraprep', '--registration', '--no-registration'], (new FortifyBackendCommand)->getDefinition()));
})->throws(InvalidArgumentException::class);
