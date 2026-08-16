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

    /**
     * Kolom yang TIDAK ikut diekspor (nilai dikosongkan):
     * - ai_credits.api_key / api_key_hash: API key customer TIDAK terenkripsi (plaintext).
     * - ai_providers.api_key, website_clients.wp_app_password, directadmin_settings
     *   (key=password): terenkripsi dengan APP_KEY, tidak bisa dipulihkan di server
     *   lain. Dikosongkan agar backup portabel & aman dibagikan.
     */
    private const SENSITIVE_COLUMNS = [
        'ai_providers' => ['api_key'],
        'website_clients' => ['wp_app_password'],
        'ai_credits' => ['api_key', 'api_key_hash'],
    ];

    /** Tabel runtime/transien yang dibuat ulang otomatis — tidak perlu diekspor. */
    private const RUNTIME_TABLES = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

    public function export()
    {
        $driver = DB::getDriverName();
        $database = DB::getDatabaseName();
        $tables = $this->getTables($driver, $database);

        $dir = storage_path('app/backups');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filePath = $dir.'/db-export-'.date('Y-m-d-H-i-s').'.json';

        // Stream langsung ke file agar memory tetap kecil walau DB besar.
        $handle = fopen($filePath, 'w');

        fwrite($handle, "{\n");
        fwrite($handle, implode(",\n", [
            '"driver":'.json_encode($driver),
            '"database":'.json_encode($database),
            '"generated_at":'.json_encode(now()->toIso8601String()),
            '"version":2',
            '"note":'.json_encode('Kolom rahasia dikosongkan saat ekspor. Isi ulang API key & password via menu Admin setelah restore.'),
        ]));
        fwrite($handle, ",\n\"tables\": {\n");

        $firstTable = true;
        foreach ($tables as $table) {
            if (in_array($table, self::RUNTIME_TABLES, true)) {
                continue;
            }

            if (! $firstTable) {
                fwrite($handle, ",\n");
            }
            $firstTable = false;

            fwrite($handle, json_encode((string) $table).': [');

            $firstRow = true;
            foreach (DB::table($table)->cursor() as $row) {
                $arr = (array) $row;
                foreach (self::SENSITIVE_COLUMNS[$table] ?? [] as $column) {
                    $arr[$column] = null;
                }
                if ($table === 'directadmin_settings' && ($arr['key'] ?? null) === 'password') {
                    $arr['value'] = null;
                }

                fwrite($handle, ($firstRow ? '' : ',').json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $firstRow = false;
            }

            fwrite($handle, ']');
        }

        fwrite($handle, "\n}\n}\n");
        fclose($handle);

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
        // Batas ukuran 50MB — file lebih besar ditolak sebelum diproses.
        $request->validate([
            'file' => ['required', 'file', 'mimes:json', 'max:51200'],
            // Restore menghapus data yang ada → wajib konfirmasi eksplisit.
            'confirm_restore' => ['required', 'accepted'],
        ], [
            'confirm_restore.required' => 'Centang konfirmasi bahwa Anda paham data yang ada akan ditimpa.',
            'confirm_restore.accepted' => 'Centang konfirmasi bahwa Anda paham data yang ada akan ditimpa.',
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
                                $filteredChunk[] = $this->normalizeDatetimeValues($filtered);
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

    /**
     * json_encode(Carbon) menghasilkan ISO8601 dengan 'T'/'Z', mis. dari file
     * backup yang dibuat tool lain. MySQL strict mode menolak format itu →
     * konversi ke format datetime standar sebelum insert.
     */
    private function normalizeDatetimeValues(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})$/', $value)) {
                $row[$key] = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i:s');
            }
        }

        return $row;
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
