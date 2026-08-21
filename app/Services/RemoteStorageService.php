<?php

namespace App\Services;

use App\Models\LandingPageSetting;
use Exception;
use Illuminate\Support\Facades\Log;

class RemoteStorageService
{
    /**
     * Get all Remote Offsite Backup configurations.
     */
    public static function getAllConfigs(): array
    {
        return [
            'keep_local' => LandingPageSetting::get('backup_keep_local', '1') == '1',
            'auto_upload_target' => LandingPageSetting::get('backup_auto_upload_target', 'none'), // 'none', 'ftp', 'sftp', 'both'

            'ftp' => [
                'enabled' => LandingPageSetting::get('backup_ftp_enabled', '0') == '1',
                'host' => LandingPageSetting::get('backup_ftp_host', ''),
                'port' => (int) LandingPageSetting::get('backup_ftp_port', '21'),
                'username' => LandingPageSetting::get('backup_ftp_username', ''),
                'password' => LandingPageSetting::get('backup_ftp_password', ''),
                'path' => LandingPageSetting::get('backup_ftp_path', '/backups/'),
                'ssl' => LandingPageSetting::get('backup_ftp_ssl', '0') == '1',
                'passive' => LandingPageSetting::get('backup_ftp_passive', '1') == '1',
            ],

            'sftp' => [
                'enabled' => LandingPageSetting::get('backup_sftp_enabled', '0') == '1',
                'host' => LandingPageSetting::get('backup_sftp_host', ''),
                'port' => (int) LandingPageSetting::get('backup_sftp_port', '22'),
                'username' => LandingPageSetting::get('backup_sftp_username', ''),
                'password' => LandingPageSetting::get('backup_sftp_password', ''),
                'private_key' => LandingPageSetting::get('backup_sftp_private_key', ''),
                'path' => LandingPageSetting::get('backup_sftp_path', '/backups/'),
            ],
        ];
    }

    /**
     * Save Remote Offsite Backup settings.
     */
    public static function saveConfigs(array $data): void
    {
        LandingPageSetting::set('backup_keep_local', !empty($data['keep_local']) ? '1' : '0', 'backup');
        LandingPageSetting::set('backup_auto_upload_target', $data['auto_upload_target'] ?? 'none', 'backup');

        // FTP
        LandingPageSetting::set('backup_ftp_enabled', !empty($data['ftp_enabled']) ? '1' : '0', 'backup');
        LandingPageSetting::set('backup_ftp_host', (string) ($data['ftp_host'] ?? ''), 'backup');
        LandingPageSetting::set('backup_ftp_port', (string) ($data['ftp_port'] ?? '21'), 'backup');
        LandingPageSetting::set('backup_ftp_username', (string) ($data['ftp_username'] ?? ''), 'backup');
        if (isset($data['ftp_password']) && trim((string)$data['ftp_password']) !== '') {
            LandingPageSetting::set('backup_ftp_password', (string) $data['ftp_password'], 'backup');
        }
        LandingPageSetting::set('backup_ftp_path', (string) ($data['ftp_path'] ?? '/backups/'), 'backup');
        LandingPageSetting::set('backup_ftp_ssl', !empty($data['ftp_ssl']) ? '1' : '0', 'backup');
        LandingPageSetting::set('backup_ftp_passive', !empty($data['ftp_passive']) ? '1' : '0', 'backup');

        // SFTP
        LandingPageSetting::set('backup_sftp_enabled', !empty($data['sftp_enabled']) ? '1' : '0', 'backup');
        LandingPageSetting::set('backup_sftp_host', (string) ($data['sftp_host'] ?? ''), 'backup');
        LandingPageSetting::set('backup_sftp_port', (string) ($data['sftp_port'] ?? '22'), 'backup');
        LandingPageSetting::set('backup_sftp_username', (string) ($data['sftp_username'] ?? ''), 'backup');
        if (isset($data['sftp_password']) && trim((string)$data['sftp_password']) !== '') {
            LandingPageSetting::set('backup_sftp_password', (string) $data['sftp_password'], 'backup');
        }
        if (isset($data['sftp_private_key'])) {
            LandingPageSetting::set('backup_sftp_private_key', (string) $data['sftp_private_key'], 'backup');
        }
        LandingPageSetting::set('backup_sftp_path', (string) ($data['sftp_path'] ?? '/backups/'), 'backup');
    }

    /**
     * Test FTP Connection and credentials.
     */
    public static function testFtpConnection(array $config): array
    {
        $host = trim($config['host'] ?? '');
        $port = (int) ($config['port'] ?? 21);
        $username = trim($config['username'] ?? '');
        $password = $config['password'] ?? '';
        $path = '/' . trim($config['path'] ?? '/', '/');
        $ssl = !empty($config['ssl']);
        $passive = !empty($config['passive']);

        if (empty($host) || empty($username)) {
            return ['success' => false, 'message' => 'FTP Host and Username are required.'];
        }

        $protocol = $ssl ? 'ftps' : 'ftp';
        $url = "{$protocol}://{$host}:{$port}{$path}/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FTPLISTONLY, 1);
        curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, 1);

        if ($ssl) {
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($passive) {
            curl_setopt($ch, CURLOPT_FTP_USE_EPSV, 1);
        }

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($errno === 0 || $httpCode === 226 || $httpCode === 200) {
            return [
                'success' => true,
                'message' => "Successfully connected to FTP server {$host}:{$port} as '{$username}'.",
            ];
        }

        return [
            'success' => false,
            'message' => "FTP Connection Error ({$errno}): " . ($error ?: "Server returned code {$httpCode}"),
        ];
    }

    /**
     * Test SFTP Connection and credentials.
     */
    public static function testSftpConnection(array $config): array
    {
        $host = trim($config['host'] ?? '');
        $port = (int) ($config['port'] ?? 22);
        $username = trim($config['username'] ?? '');
        $password = $config['password'] ?? '';
        $path = '/' . trim($config['path'] ?? '/', '/');

        if (empty($host) || empty($username)) {
            return ['success' => false, 'message' => 'SFTP Host and Username are required.'];
        }

        $url = "sftp://{$host}:{$port}{$path}/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_DIRLISTONLY, 1);
        curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, 1);

        $output = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($errno === 0) {
            return [
                'success' => true,
                'message' => "Successfully connected to SFTP server {$host}:{$port} as '{$username}'.",
            ];
        }

        return [
            'success' => false,
            'message' => "SFTP Connection Error ({$errno}): " . ($error ?: "Failed to authenticate"),
        ];
    }

    /**
     * Upload a local backup file to remote FTP.
     */
    public static function uploadToFtp(string $localFilePath, ?array $overrideConfig = null): array
    {
        if (!file_exists($localFilePath)) {
            throw new Exception("Local backup file does not exist: {$localFilePath}");
        }

        $cfg = $overrideConfig ?: self::getAllConfigs()['ftp'];
        $host = trim($cfg['host'] ?? '');
        $port = (int) ($cfg['port'] ?? 21);
        $username = trim($cfg['username'] ?? '');
        $password = $cfg['password'] ?? '';
        $remoteDir = '/' . trim($cfg['path'] ?? '/', '/');
        $ssl = !empty($cfg['ssl']);
        $passive = !empty($cfg['passive']);

        if (empty($host) || empty($username)) {
            throw new Exception("FTP host and username are not configured.");
        }

        $filename = basename($localFilePath);
        $remoteUrl = ($ssl ? 'ftps' : 'ftp') . "://{$host}:{$port}{$remoteDir}/{$filename}";

        $fp = fopen($localFilePath, 'rb');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remoteUrl);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFilePath));
        curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        if ($ssl) {
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($passive) {
            curl_setopt($ch, CURLOPT_FTP_USE_EPSV, 1);
        }

        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        fclose($fp);

        if ($errno !== 0) {
            throw new Exception("FTP Upload Failed ({$errno}): {$error}");
        }

        return [
            'success' => true,
            'protocol' => 'ftp',
            'destination' => "{$host}:{$port}{$remoteDir}/{$filename}",
            'size' => filesize($localFilePath),
            'upload_time' => $info['total_time'] ?? 0,
        ];
    }

    /**
     * Upload a local backup file to remote SFTP.
     */
    public static function uploadToSftp(string $localFilePath, ?array $overrideConfig = null): array
    {
        if (!file_exists($localFilePath)) {
            throw new Exception("Local backup file does not exist: {$localFilePath}");
        }

        $cfg = $overrideConfig ?: self::getAllConfigs()['sftp'];
        $host = trim($cfg['host'] ?? '');
        $port = (int) ($cfg['port'] ?? 22);
        $username = trim($cfg['username'] ?? '');
        $password = $cfg['password'] ?? '';
        $remoteDir = '/' . trim($cfg['path'] ?? '/', '/');

        if (empty($host) || empty($username)) {
            throw new Exception("SFTP host and username are not configured.");
        }

        $filename = basename($localFilePath);
        $remoteUrl = "sftp://{$host}:{$port}{$remoteDir}/{$filename}";

        $fp = fopen($localFilePath, 'rb');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remoteUrl);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFilePath));
        curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        fclose($fp);

        if ($errno !== 0) {
            throw new Exception("SFTP Upload Failed ({$errno}): {$error}");
        }

        return [
            'success' => true,
            'protocol' => 'sftp',
            'destination' => "{$host}:{$port}{$remoteDir}/{$filename}",
            'size' => filesize($localFilePath),
            'upload_time' => $info['total_time'] ?? 0,
        ];
    }

    /**
     * Dispatch upload of a backup file to requested remote targets.
     */
    public static function dispatchRemoteUploads(string $filename, string $target = 'auto'): array
    {
        $path = BackupService::getBackupPath($filename);
        if (!$path || !file_exists($path)) {
            throw new Exception("Backup archive not found: {$filename}");
        }

        $configs = self::getAllConfigs();
        $results = [];

        $shouldFtp = ($target === 'ftp' || $target === 'both') || ($target === 'auto' && in_array($configs['auto_upload_target'], ['ftp', 'both']));
        $shouldSftp = ($target === 'sftp' || $target === 'both') || ($target === 'auto' && in_array($configs['auto_upload_target'], ['sftp', 'both']));

        if ($shouldFtp) {
            try {
                $results['ftp'] = self::uploadToFtp($path);
            } catch (Exception $e) {
                $results['ftp'] = ['success' => false, 'error' => $e->getMessage()];
                Log::warning("Remote Backup FTP upload error: " . $e->getMessage());
            }
        }

        if ($shouldSftp) {
            try {
                $results['sftp'] = self::uploadToSftp($path);
            } catch (Exception $e) {
                $results['sftp'] = ['success' => false, 'error' => $e->getMessage()];
                Log::warning("Remote Backup SFTP upload error: " . $e->getMessage());
            }
        }

        // If configured to NOT keep local, delete local file after at least one remote succeeded
        if (!$configs['keep_local'] && !empty($results)) {
            $hasSuccess = ($results['ftp']['success'] ?? false) || ($results['sftp']['success'] ?? false);
            if ($hasSuccess) {
                BackupService::deleteBackup($filename);
                $results['local_deleted'] = true;
            }
        } else {
            $results['local_kept'] = true;
        }

        return $results;
    }
}
