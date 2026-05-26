<?php

namespace Tests\Feature;

use Tests\TestCase;

class BackupConfigTest extends TestCase
{
    public function test_backup_disks_defaults_to_local_backups(): void
    {
        $this->assertSame(['backups'], config('backup.backup.destination.disks'));
    }

    public function test_backup_disks_env_supports_comma_separated_list(): void
    {
        $parsed = array_values(array_filter(array_map('trim', explode(',', 'backups,s3'))));

        $this->assertSame(['backups', 's3'], $parsed);
    }
}
