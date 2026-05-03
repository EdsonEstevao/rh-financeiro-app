<?php
// app/Http/Controllers/Admin/SettingsController.php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Artisan, Auth, File, Log};
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display system settings.
     */
    public function index(): View
    {
        $systemInfo = $this->getSystemInfo();
        $emailConfig = $this->getEmailConfig();
        $securitySettings = $this->getSecuritySettings();
        $backupHistory = $this->getBackupHistory();

        return view('admin.settings', compact(
            'systemInfo',
            'emailConfig',
            'securitySettings',
            'backupHistory'
        ));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'timezone'],
            'locale' => ['required', 'string', 'in:pt_BR,en,es'],
            'mail_host' => ['nullable', 'string'],
            'mail_port' => ['nullable', 'integer'],
            'mail_username' => ['nullable', 'string'],
            'mail_password' => ['nullable', 'string'],
            'mail_encryption' => ['nullable', 'string'],
            'mail_from_address' => ['nullable', 'email'],
            'mail_from_name' => ['nullable', 'string'],
        ]);

        try {
            $this->updateEnvVariable('APP_NAME', $validated['app_name']);
            $this->updateEnvVariable('APP_TIMEZONE', $validated['timezone']);
            $this->updateEnvVariable('APP_LOCALE', $validated['locale']);

            if ($request->filled('mail_host')) {
                $this->updateEnvVariable('MAIL_HOST', $validated['mail_host']);
                $this->updateEnvVariable('MAIL_PORT', $validated['mail_port']);
                $this->updateEnvVariable('MAIL_USERNAME', $validated['mail_username']);
                $this->updateEnvVariable('MAIL_ENCRYPTION', $validated['mail_encryption']);
                $this->updateEnvVariable('MAIL_FROM_ADDRESS', $validated['mail_from_address']);
                $this->updateEnvVariable('MAIL_FROM_NAME', '"' . $validated['mail_from_name'] . '"');

                if ($request->filled('mail_password')) {
                    $this->updateEnvVariable('MAIL_PASSWORD', $validated['mail_password']);
                }
            }

            // Clear config cache
            Artisan::call('config:clear');
            Artisan::call('config:cache');

            Log::info('System settings updated', ['user_id' => Auth::id()]);

            return back()->with('success', 'Configurações atualizadas com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to update settings', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao atualizar configurações.');
        }
    }

    /**
     * Create system backup.
     */
    public function backup(): RedirectResponse
    {
        try {
            // Run backup command if spatie/laravel-backup is installed
            if (class_exists('\Spatie\Backup\Commands\BackupCommand')) {
                Artisan::call('backup:run');
                $output = Artisan::output();

                Log::info('System backup created', [
                    'user_id' => Auth::id(),
                    'output' => $output
                ]);

                return back()->with('success', 'Backup criado com sucesso!');
            }

            // Manual backup fallback
            $backupPath = storage_path('app/backups');
            File::ensureDirectoryExists($backupPath);

            $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
            $fullPath = $backupPath . '/' . $filename;

            // Get database config
            $dbConfig = config('database.connections.' . config('database.default'));

            $command = sprintf(
                'mysqldump -u%s -p%s -h%s -P%s %s > %s',
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($fullPath)
            );

            $process = Process::fromShellCommandline($command);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            Log::info('Manual database backup created', [
                'user_id' => Auth::id(),
                'file' => $filename
            ]);

            return back()->with('success', 'Backup do banco de dados criado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to create backup', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao criar backup: ' . $e->getMessage());
        }
    }

    /**
     * Get system information.
     */
    private function getSystemInfo(): array
    {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'database' => config('database.default'),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'url' => config('app.url'),
            'storage_free' => $this->formatBytes(disk_free_space(storage_path())),
        ];
    }

    /**
     * Get email configuration.
     */
    private function getEmailConfig(): array
    {
        return [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }

    /**
     * Get security settings.
     */
    private function getSecuritySettings(): array
    {
        return [
            'two_factor' => config('auth.two_factor_enabled', false),
            'lockout_attempts' => config('auth.lockout_attempts', 5),
            'password_min_length' => config('auth.password_min_length', 8),
            'session_lifetime' => config('session.lifetime', 120),
        ];
    }

    /**
     * Get backup history.
     */
    private function getBackupHistory(): array
    {
        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            return [];
        }

        $files = File::files($backupPath);
        $history = [];

        foreach ($files as $file) {
            $history[] = [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'date' => date('d/m/Y H:i:s', $file->getMTime()),
                'path' => $file->getPathname(),
            ];
        }

        return array_reverse($history);
    }

    /**
     * Update an environment variable in .env file.
     */
    private function updateEnvVariable(string $key, string $value): void
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            throw new \Exception('.env file not found.');
        }

        $content = File::get($path);

        // Escape special characters in value for regex
        $escaped = preg_quote($key, '/');

        if (preg_match("/^{$escaped}=.*/m", $content)) {
            // Update existing key
            $content = preg_replace(
                "/^{$escaped}=.*/m",
                "{$key}={$value}",
                $content
            );
        } else {
            // Add new key
            $content .= "\n{$key}={$value}";
        }

        File::put($path, $content);
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            \Mail::raw('Este é um email de teste enviado em: ' . now()->format('d/m/Y H:i:s'), function ($message) use ($request) {
                $message->to($request->test_email)
                        ->subject('Teste de Configuração de Email - ' . config('app.name'));
            });

            Log::info('Test email sent', [
                'user_id' => Auth::id(),
                'to' => $request->test_email
            ]);

            return back()->with('success', 'Email de teste enviado com sucesso para ' . $request->test_email);

        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao enviar email de teste: ' . $e->getMessage());
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache(): RedirectResponse
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('optimize:clear');

            Log::info('Application cache cleared', ['user_id' => Auth::id()]);

            return back()->with('success', 'Cache do sistema limpo com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to clear cache', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao limpar cache.');
        }
    }

    /**
     * Optimize database.
     */
    public function optimizeDatabase(): RedirectResponse
    {
        try {
            // Optimize tables
            $tables = \DB::select('SHOW TABLES');
            $dbName = 'Tables_in_' . config('database.connections.' . config('database.default') . '.database');

            foreach ($tables as $table) {
                \DB::statement('OPTIMIZE TABLE `' . $table->$dbName . '`');
            }

            Log::info('Database optimized', ['user_id' => Auth::id()]);

            return back()->with('success', 'Banco de dados otimizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to optimize database', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao otimizar banco de dados.');
        }
    }
}
