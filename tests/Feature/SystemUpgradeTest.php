<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SystemUpgradeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_system_status_endpoint_returns_system_info(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/system/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'system' => [
                        'app_name',
                        'app_env',
                        'laravel_version',
                        'php_version',
                        'database' => [
                            'connected',
                            'driver',
                            'applied_migrations_count',
                            'pending_migrations',
                            'pending_migrations_count',
                        ],
                    ],
                    'history',
                    'backups',
                ],
            ]);
    }

    public function test_on_demand_database_backup_and_download(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        // 1. Create backup
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/system/backup');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $filename = $response->json('data.backup.filename');
        $this->assertNotEmpty($filename);

        // 2. Download backup
        $downloadResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/system/backups/'.$filename);

        $downloadResponse->assertStatus(200);

        // Cleanup
        $backupPath = storage_path('app/backups/'.$filename);
        if (File::exists($backupPath)) {
            File::delete($backupPath);
        }
    }

    public function test_system_migrate_endpoint_runs_safely(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/system/migrate');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_system_clear_cache_endpoint(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/system/clear-cache');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_system_upgrade_with_zip_archive_preserves_env_and_runs_safely(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        // Create temporary zip archive
        $tempZip = tempnam(sys_get_temp_dir(), 'test_upg_').'.zip';
        $zip = new \ZipArchive;
        $zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Add dummy update file
        $zip->addFromString('storage/test_should_be_ignored.txt', 'This should not overwrite storage');
        $zip->addFromString('.env', 'MALICIOUS_ENV_OVERWRITE=true');
        $zip->addFromString('resources/test_upgrade_sample.txt', 'JMOS upgrade test payload v1.0');
        $zip->close();

        $uploadedFile = new UploadedFile($tempZip, 'jmos_test_upgrade.zip', 'application/zip', null, true);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/system/upgrade', [
                'archive' => $uploadedFile,
                'run_migrations' => true,
                'create_backup' => true,
                'clear_caches' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Verify the extracted sample file exists
        $samplePath = base_path('resources/test_upgrade_sample.txt');
        $this->assertTrue(File::exists($samplePath));
        $this->assertEquals('JMOS upgrade test payload v1.0', File::get($samplePath));

        // Verify .env was NOT overwritten with the archive's .env
        $envContent = File::get(base_path('.env'));
        $this->assertStringNotContainsString('MALICIOUS_ENV_OVERWRITE=true', $envContent);

        // Cleanup test files
        if (File::exists($tempZip)) {
            File::delete($tempZip);
        }
        if (File::exists($samplePath)) {
            File::delete($samplePath);
        }

        // Cleanup any pre-upgrade backup created during test
        $createdBackup = $response->json('data.record.backup_filename');
        if ($createdBackup && File::exists(storage_path('app/backups/'.$createdBackup))) {
            File::delete(storage_path('app/backups/'.$createdBackup));
        }
    }
}
