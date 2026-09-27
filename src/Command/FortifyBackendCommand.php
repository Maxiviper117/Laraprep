<?php

declare(strict_types=1);

namespace Maxiviper117\Laraprep\Command;

use InvalidArgumentException;
use Maxiviper117\Laraprep\Ast\FortifyConfigEditor;
use Maxiviper117\Laraprep\Ast\PhpFileEditor;
use Maxiviper117\Laraprep\Ast\ProvidersFileEditor;
use Maxiviper117\Laraprep\Ast\UserModelEditor;
use Maxiviper117\Laraprep\Files\FileBackup;
use Maxiviper117\Laraprep\Files\MarkerBlock;
use Maxiviper117\Laraprep\Fortify\FortifyBackendOptions;
use Maxiviper117\Laraprep\Fortify\FortifyBackendService;
use Maxiviper117\Laraprep\Planning\ChangePlan;
use Maxiviper117\Laraprep\Planning\ChangeResult;
use Maxiviper117\Laraprep\Planning\ChangeStatus;
use Maxiviper117\Laraprep\Process\ArtisanRunner;
use Maxiviper117\Laraprep\Process\ComposerRunner;
use Maxiviper117\Laraprep\Process\ProcessRunner;
use Maxiviper117\Laraprep\Project\ProjectDetector;
use Maxiviper117\Laraprep\Support\CommandFailure;
use Maxiviper117\Laraprep\Support\ExitCode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FortifyBackendCommand extends Command
{
    protected static string $defaultName = 'fortify:backend';

    public function __construct(
        private readonly ProjectDetector $projectDetector = new ProjectDetector,
        private readonly ?FortifyBackendService $service = null
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('fortify:backend')
            ->setDescription('Set up Laravel Fortify as a backend-only authentication layer.')
            ->addOption('registration', null, InputOption::VALUE_NONE, 'Enable registration.')
            ->addOption('no-registration', null, InputOption::VALUE_NONE, 'Disable registration.')
            ->addOption('reset-passwords', null, InputOption::VALUE_NONE, 'Enable password reset support.')
            ->addOption('no-reset-passwords', null, InputOption::VALUE_NONE, 'Disable password reset support.')
            ->addOption('verify-email', null, InputOption::VALUE_NONE, 'Enable email verification.')
            ->addOption('no-verify-email', null, InputOption::VALUE_NONE, 'Disable email verification.')
            ->addOption('two-factor', null, InputOption::VALUE_NONE, 'Enable two-factor authentication.')
            ->addOption('passkeys', null, InputOption::VALUE_NONE, 'Enable passkeys when the installed Fortify version supports them.')
            ->addOption('no-migrate', null, InputOption::VALUE_NONE, 'Skip running migrations.')
            ->addOption('no-backup', null, InputOption::VALUE_NONE, 'Do not create backup files.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Bypass recognized customization warnings.')
            ->addOption('test-route', null, InputOption::VALUE_NONE, 'Add an optional backend auth test route.')
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Apply changes instead of showing the dry-run plan.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $options = FortifyBackendOptions::fromInput($input);
            $project = $this->projectDetector->detect(getcwd() ?: '.');
            $plan = $options->apply
                ? $this->service()->apply($project, $options)
                : $this->service()->plan($project, $options);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return ExitCode::INVALID_USAGE;
        } catch (CommandFailure $exception) {
            $io->error($exception->getMessage());

            return $exception->exitCode();
        }

        $this->renderPlan($io, $plan);

        if (! $options->apply) {
            return $this->containsBlockingProblem($plan)
                ? ExitCode::PLANNING_FAILURE
                : ExitCode::SUCCESS;
        }

        return $plan->partialFailure || $this->containsBlockingProblem($plan)
            ? ExitCode::PARTIAL_APPLY_FAILURE
            : ExitCode::SUCCESS;
    }

    private function renderPlan(SymfonyStyle $io, ChangePlan $plan): void
    {
        $io->title('Laraprep');
        $io->text('Command: fortify:backend');
        $io->text('Mode: '.($plan->applyMode ? 'apply' : 'dry-run'));
        $io->newLine();

        $this->renderSection($io, 'Completed', $plan->results, ChangeStatus::Applied, 'info');
        $this->renderSection($io, 'Planned changes', $plan->results, ChangeStatus::Planned, 'comment');
        $this->renderSection($io, 'Skipped', $plan->results, ChangeStatus::Skipped, 'comment');
        $this->renderSection($io, 'Needs manual review', $plan->results, ChangeStatus::ManualReview, 'warning');
        $this->renderSection($io, 'Failed', $plan->results, ChangeStatus::Failed, 'error');

        $io->section('Backend endpoints');

        foreach ($plan->endpoints as $endpoint) {
            $io->writeln(sprintf(' * %s', $endpoint));
        }

        $io->section('Next steps');
        $index = 1;

        foreach ($plan->nextSteps as $step) {
            $io->writeln(sprintf('%d. %s', $index, $step));
            $index++;
        }

        if (! $plan->applyMode) {
            $io->newLine();
            $io->text('No files were changed.');
            $io->text('To apply these changes, run vendor/bin/laraprep fortify:backend --apply');
        }
    }

    /**
     * @param  list<ChangeResult>  $results
     */
    private function renderSection(SymfonyStyle $io, string $title, array $results, ChangeStatus $status, string $style): void
    {
        $filtered = array_values(array_filter(
            $results,
            static fn (ChangeResult $result): bool => $result->status === $status
        ));

        if ($filtered === []) {
            return;
        }

        $io->section($title);

        foreach ($filtered as $result) {
            $line = sprintf('%s %s', $this->prefixFor($status), $result->message);

            match ($style) {
                'info' => $io->text($line),
                'warning' => $io->warning($result->message),
                'error' => $io->error($result->message),
                default => $io->text($line),
            };
        }
    }

    private function prefixFor(ChangeStatus $status): string
    {
        return match ($status) {
            ChangeStatus::Applied => '✓',
            ChangeStatus::Planned => '*',
            ChangeStatus::Skipped => '-',
            ChangeStatus::ManualReview => '!',
            ChangeStatus::Failed => 'x',
        };
    }

    private function containsBlockingProblem(ChangePlan $plan): bool
    {
        foreach ($plan->results as $result) {
            if ($result->isProblem()) {
                return true;
            }
        }

        return false;
    }

    private function service(): FortifyBackendService
    {
        if ($this->service instanceof FortifyBackendService) {
            return $this->service;
        }

        $phpFileEditor = new PhpFileEditor;
        $processRunner = new ProcessRunner;

        return new FortifyBackendService(
            composerRunner: new ComposerRunner($processRunner),
            artisanRunner: new ArtisanRunner($processRunner),
            fileBackup: new FileBackup,
            markerBlock: new MarkerBlock,
            userModelEditor: new UserModelEditor($phpFileEditor),
            providersFileEditor: new ProvidersFileEditor($phpFileEditor),
            fortifyConfigEditor: new FortifyConfigEditor($phpFileEditor),
        );
    }
}
