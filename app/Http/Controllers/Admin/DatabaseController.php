<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DatabaseController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Admin/Database');
    }

    public function export()
    {
        $driver = DB::getDriverName();
        $database = DB::getDatabaseName();
        $tables = $this->getTables($driver, $database);

        $data = [
            'driver' => $driver,
            'database' => $database,
            'generated_at' => now()->toIso8601String(),
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $rows = DB::table($table)->get()->map(fn ($row) => (array) $row)->toArray();
            $data['tables'][$table] = $rows;
        }

        $dir = storage_path('app/backups');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filePath = $dir.'/db-export-'.date('Y-m-d-H-i-s').'.json';
        File::put($filePath, json_encode($data));

        return Response::download($filePath)->deleteFileAfterSend(true);
    }

    public function clear(Request $request)
    {
        $request->validate([
            'confirm' => ['required', 'in:CLEAR'],
        ]);

        $driver = DB::getDriverName();
        $database = DB::getDatabaseName();
        $tables = $this->getTables($driver, $database);
        $excluded = ['users', 'migrations'];

        $disableFk = $this->getDisableForeignKeyStatement($driver);
        $enableFk = $this->getEnableForeignKeyStatement($driver);

        $clearedTables = 0;

        try {
            DB::transaction(function () use ($tables, $excluded, $driver, $disableFk, &$enableFk, &$clearedTables) {
                if ($disableFk) {
                    DB::statement($disableFk);
                }

                foreach ($tables as $table) {
                    if (in_array($table, $excluded, true)) {
                        continue;
                    }

                    if (! Schema::hasTable($table)) {
                        continue;
                    }

                    DB::table($table)->delete();

                    if ($driver === 'sqlite') {
                        try {
                            DB::statement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
                        } catch (\Throwable $e) {
                        }
                    }

                    $clearedTables++;
                }

                if ($enableFk) {
                    DB::statement($enableFk);
                    $enableFk = null;
                }
            });

            return redirect()->back()->with('success', "Database berhasil dibersihkan. Tabel dibersihkan: {$clearedTables} (users & migrations tidak dihapus).");
        } catch (\Throwable $e) {
            if ($enableFk) {
                try {
                    DB::statement($enableFk);
                } catch (\Throwable $inner) {
                }
            }

            return redirect()->back()->with('error', 'Gagal clear database: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json'],
        ]);

        $file = $request->file('file');
        $content = File::get($file->getRealPath());
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($json) || ! isset($json['tables']) || ! is_array($json['tables'])) {
            throw ValidationException::withMessages([
                'file' => 'Format file tidak valid. Pastikan file berasal dari fitur export (JSON).',
            ]);
        }

        $driver = DB::getDriverName();
        $disableFk = $this->getDisableForeignKeyStatement($driver);
        $enableFk = $this->getEnableForeignKeyStatement($driver);

        $importedTables = 0;
        $importedRows = 0;
        $skippedTables = [];

        try {
            DB::transaction(function () use ($json, $driver, $disableFk, &$enableFk, &$importedTables, &$importedRows, &$skippedTables) {
                if ($disableFk) {
                    DB::statement($disableFk);
                }

                foreach ($json['tables'] as $table => $rows) {
                    if (! is_string($table) || ! Schema::hasTable($table)) {
                        $skippedTables[] = is_string($table) ? $table : '(invalid table name)';
                        continue;
                    }

                    $columns = Schema::getColumnListing($table);
                    $columnSet = array_fill_keys($columns, true);

                    DB::table($table)->delete();

                    if ($driver === 'sqlite') {
                        try {
                            DB::statement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
                        } catch (\Throwable $e) {
                        }
                    }

                    if (! is_array($rows) || count($rows) === 0) {
                        $importedTables++;
                        continue;
                    }

                    foreach (array_chunk($rows, 500) as $chunk) {
                        $filteredChunk = [];
                        foreach ($chunk as $row) {
                            if (! is_array($row)) {
                                continue;
                            }

                            $filtered = array_intersect_key($row, $columnSet);
                            if ($filtered !== []) {
                                $filteredChunk[] = $filtered;
                            }
                        }

                        if ($filteredChunk === []) {
                            continue;
                        }

                        DB::table($table)->insert($filteredChunk);
                        $importedRows += count($filteredChunk);
                    }

                    $importedTables++;
                }

                if ($enableFk) {
                    DB::statement($enableFk);
                    $enableFk = null;
                }
            });

            $message = "Import berhasil. Tabel: {$importedTables}, Baris: {$importedRows}.";
            if (count($skippedTables) > 0) {
                $message .= ' Dilewati (tidak ada di versi sekarang): '.implode(', ', array_slice($skippedTables, 0, 8)).(count($skippedTables) > 8 ? '…' : '').'.';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            if ($enableFk) {
                try {
                    DB::statement($enableFk);
                } catch (\Throwable $inner) {
                }
            }

            return redirect()->back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    private function getTables(string $driver, ?string $database): array
    {
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $res = DB::select('SHOW TABLES');
            $key = 'Tables_in_'.$database;
            return array_map(fn ($r) => $r->$key, $res);
        }

        if ($driver === 'pgsql') {
            $res = DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public'");
            return array_map(fn ($r) => $r->tablename, $res);
        }

        if ($driver === 'sqlite') {
            $res = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            return array_map(fn ($r) => $r->name, $res);
        }

        if ($driver === 'sqlsrv') {
            $res = DB::select("SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE'");
            return array_map(fn ($r) => $r->name, $res);
        }

        return [];
    }

    private function getDisableForeignKeyStatement(string $driver): ?string
    {
        if ($driver === 'mysql' || $driver === 'mariadb') return 'SET FOREIGN_KEY_CHECKS=0;';
        if ($driver === 'pgsql') return 'SET CONSTRAINTS ALL DEFERRED;';
        if ($driver === 'sqlite') return 'PRAGMA foreign_keys = OFF;';
        if ($driver === 'sqlsrv') return null;
        return null;
    }

    private function getEnableForeignKeyStatement(string $driver): ?string
    {
        if ($driver === 'mysql' || $driver === 'mariadb') return 'SET FOREIGN_KEY_CHECKS=1;';
        if ($driver === 'pgsql') return 'SET CONSTRAINTS ALL IMMEDIATE;';
        if ($driver === 'sqlite') return 'PRAGMA foreign_keys = ON;';
        if ($driver === 'sqlsrv') return null;
        return null;
    }
}
