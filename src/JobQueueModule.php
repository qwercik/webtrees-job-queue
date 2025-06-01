<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

use Aura\Router\RouterContainer;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Registry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Komputeryk\Webtrees\JobQueue\Controller\RunQueue;
use Komputeryk\Webtrees\JobQueue\Helper\MigrationsTrait;

class JobQueueModule extends AbstractModule implements ModuleCustomInterface, MiddlewareInterface
{
    use ModuleCustomTrait;
    use MigrationsTrait;

    public const MODULE_DIR = __DIR__ . '/../';

    public function boot(): void
    {
        $router = Registry::container()->get(RouterContainer::class)->getMap();
        $router->get(RunQueue::class, '/run-queue');
    }

    public function title(): string
    {
        return I18N::translate('LBL_MODULE_NAME');
    }

    public function description(): string
    {
        return I18N::translate('LBL_MODULE_DESCRIPTION');
    }

    public function customModuleAuthorName(): string
    {
        return 'Eryk Andrzejewski';
    }

    public function customModuleVersion(): string
    {
        return file_get_contents(static::MODULE_DIR . 'VERSION');
    }

    public function customModuleLatestVersionUrl(): string
    {
        return 'https://github.com/qwercik/webtrees-job-queue/raw/master/VERSION';
    }

    public function customModuleSupportUrl(): string
    {
        return 'https://github.com/qwercik/webtrees-job-queue';
    }

    public function customTranslations(string $language): array
    {
        $file = $this->getLangFilePath($language);
        return file_exists($file)
            ? require $file
            : require $this->getLangFilePath('en');
    }

    private function getLangFilePath(string $language): string
    {
        return $this->resourcesFolder() . "lang/{$language}.php";
    }

    public function resourcesFolder(): string
    {
        return static::MODULE_DIR . 'resources/';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->runMigrations('JQ');
        return $handler->handle($request);
    }
}
