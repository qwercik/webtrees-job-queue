<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

interface JobExecutor
{
    public function run(array $jobs): array;
}
