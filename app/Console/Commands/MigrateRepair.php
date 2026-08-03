<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Throwable;

class MigrateRepair extends Command
{
    protected $signature = 'migrate:repair';

    protected $description = 'Run pending migrations; skip and mark as run any that fail because the table/column already exists (repairs SQLSTATE 1050/1060/1054).';

    public function handle(Migrator $migrator): int
    {
        $migrator->setOutput($this->output);

        $repository = $migrator->getRepository();
        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }

        $paths = array_values(array_unique(array_merge(
            [database_path('migrations')],
            $migrator->paths(),
        )));

        $files = $migrator->getMigrationFiles($paths);
        $ran = $repository->getRan();

        $pending = collect($files)
            ->reject(fn ($path, $name) => in_array($name, $ran, true))
            ->all();

        if ($pending === []) {
            $this->info('Nothing to repair.');

            return self::SUCCESS;
        }

        $batch = $repository->getNextBatchNumber();
        $migrated = 0;
        $skipped = 0;

        foreach ($pending as $name => $path) {
            try {
                $migrator->requireFiles([$path]);
                $migrator->runPending([$path]);
                $migrated++;
                $this->info("  Ran: {$name}");
            } catch (Throwable $e) {
                if (! $this->alreadyApplied($e)) {
                    $this->error("  Failed: {$name}");
                    $this->error('  '.$e->getMessage());

                    return self::FAILURE;
                }

                $repository->log($name, $batch);
                $skipped++;
                $this->warn("  Skipped (already exists): {$name}");
            }
        }

        $this->newLine();
        $this->info("Done. {$migrated} migrated, {$skipped} marked as already applied.");

        return self::SUCCESS;
    }

    private function alreadyApplied(Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'already exists')   // 1050: create table yang sudah ada
            || str_contains($message, 'Duplicate column') // 1060: add kolom yang sudah ada
            || str_contains($message, 'Unknown column');  // 1054: drop/modify kolom yang sudah hilang
    }
}
