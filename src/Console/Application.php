<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Console;

use Maxiviper117\Laraprep\Command\FortifyBackendCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

final class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('Laraprep');

        $this->addCommand(new FortifyBackendCommand);
        $this->setDefaultCommand('fortify:backend');
    }
}
