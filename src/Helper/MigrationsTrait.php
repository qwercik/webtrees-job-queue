<?php

namespace Komputeryk\Webtrees\JobQueue\Helper;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\MigrationService;

trait MigrationsTrait
{
    private function runMigrations(string $module): void
    {
        $migrations = Registry::container()->get(MigrationService::class);
        $migrations->updateSchema($this->getMigrationsNamespace(), $module . '_SCHEMA_VERSION', $this->getSchemaVersion());
    }

    private function getSchemaVersion(): int
    {
        $classPrefix = $this->getMigrationsNamespace() . '\\Migration';
        for ($i = 0; class_exists($classPrefix . (string)$i); $i++) {}
        return $i;
    }

    private function getMigrationsNamespace(): string
    {
        $class = get_called_class();
        $moduleNamespace = (new \ReflectionClass($class))->getNamespaceName();
        return $moduleNamespace . '\\Migration';
    }
}
