<?php

declare(strict_types=1);

use Fisharebest\Webtrees\Registry;
use Komputeryk\Webtrees\JobQueue\JobQueueModule;

require __DIR__ . '/vendor/autoload.php';

return Registry::container()->get(JobQueueModule::class);
