<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;

class SchemaPathTest extends Unit
{
    public function testResolveRejectsEmptyVersion(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('cannot be empty');
        new \Database\SchemaPath('/some/path')->resolve('');
    }

    public function testResolveRejectsPathTraversal(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('traversal not allowed');
        new \Database\SchemaPath('/some/path')->resolve('../etc/passwd');
    }

    public function testResolveRejectsInvalidFormat(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid migration path format');
        new \Database\SchemaPath('/some/path')->resolve('invalid_format.sql');
    }

    public function testResolveRejectsNoLeadingNumbers(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid migration path format');
        new \Database\SchemaPath('/some/path')->resolve('ab_name/create_table.sql');
    }

    public function testResolveValidFormatButMissingDir(): void
    {
        // Valid format but base directory doesn't exist
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('escapes base directory');
        new \Database\SchemaPath('/tmp')->resolve('00_test/create_users.sql');
    }

    public function testResolveWithRealFile(): void
    {
        // Use actual migration file from the project
        $appPath = dirname(__DIR__, 2);  // Go up from Tests/Unit to project root
        $result = new \Database\SchemaPath($appPath)->resolve('00_bedrock/create_users.sql');

        $this->assertStringEndsWith('create_users.sql', $result);
        $this->assertFileExists($result);
    }

    public function testResolveRefusesSiblingSharingTheBasePrefix(): void
    {
        $root = sys_get_temp_dir() . '/schemapath_' . bin2hex(random_bytes(6));
        mkdir($root . '/db_schemas/00_test', 0777, true);
        mkdir($root . '/db_schemas_old/00_test', 0777, true);
        file_put_contents($root . '/db_schemas_old/00_test/create_x.sql', '');
        symlink($root . '/db_schemas_old/00_test/create_x.sql', $root . '/db_schemas/00_test/create_x.sql');

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('escapes base directory');
            new \Database\SchemaPath($root)->resolve('00_test/create_x.sql');
        } finally {
            unlink($root . '/db_schemas/00_test/create_x.sql');
            unlink($root . '/db_schemas_old/00_test/create_x.sql');
            rmdir($root . '/db_schemas/00_test');
            rmdir($root . '/db_schemas_old/00_test');
            rmdir($root . '/db_schemas');
            rmdir($root . '/db_schemas_old');
            rmdir($root);
        }
    }
}
