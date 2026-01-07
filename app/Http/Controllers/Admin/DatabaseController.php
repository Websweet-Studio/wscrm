<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

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

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $content = File::get($file->getRealPath());
        $json = json_decode($content, true);

        if (! is_array($json) || ! isset($json['tables'])) {
            return response()->json(['error' => 'Format file tidak valid'], 422);
        }

        $driver = DB::getDriverName();
        $disableFk = $this->getDisableForeignKeyStatement($driver);
        $enableFk = $this->getEnableForeignKeyStatement($driver);

        DB::beginTransaction();
        try {
            if ($disableFk) {
                DB::statement($disableFk);
            }

            foreach ($json['tables'] as $table => $rows) {
                try {
                    DB::table($table)->truncate();
                } catch (\Throwable $e) {
                }

                if (is_array($rows) && count($rows) > 0) {
                    foreach (array_chunk($rows, 500) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }
            }

            if ($enableFk) {
                DB::statement($enableFk);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal import: '.$e->getMessage()], 500);
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

