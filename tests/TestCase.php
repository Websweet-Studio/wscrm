<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected static bool $testingDatabaseEnsured = false;

    public function createApplication()
    {
        $this->ensureTestingDatabaseExists();

        return parent::createApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Force testing environment and disable CSRF
        $this->app['env'] = 'testing';
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    private function ensureTestingDatabaseExists(): void
    {
        if (self::$testingDatabaseEnsured) {
            return;
        }

        if ((string) env('DB_CONNECTION') !== 'mysql') {
            self::$testingDatabaseEnsured = true;

            return;
        }

        $database = (string) env('DB_DATABASE');
        if ($database === '') {
            self::$testingDatabaseEnsured = true;

            return;
        }

        $host = (string) env('DB_HOST', '127.0.0.1');
        $port = (string) env('DB_PORT', '3306');
        $username = (string) env('DB_USERNAME', 'root');
        $password = (string) env('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $quotedDb = '`'.str_replace('`', '``', $database).'`';
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$quotedDb} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        self::$testingDatabaseEnsured = true;
    }
}
