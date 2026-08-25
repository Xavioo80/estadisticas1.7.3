<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep=30 : Days to keep backups}';

    protected $description = 'Backup database to storage/backups';

    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $backupPath = storage_path('backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $filename = "backup_{$database}_" . date('Y-m-d_His') . ".sql";
        $filepath = $backupPath . DIRECTORY_SEPARATOR . $filename;
        
        $command = sprintf(
            'mysqldump -h %s -u %s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($filepath)
        );
        
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0) {
            $this->info("Backup created: {$filename}");
            $this->cleanOldBackups($this->option('keep'));
        } else {
            $this->error('Backup failed');
        }
    }
    
    private function cleanOldBackups(int $days)
    {
        $backupPath = storage_path('backups');
        $files = glob($backupPath . '/backup_*.sql');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && $now - filemtime($file) >= 60 * 60 * 24 * $days) {
                unlink($file);
                $this->info("Deleted old backup: " . basename($file));
            }
        }
    }
}
