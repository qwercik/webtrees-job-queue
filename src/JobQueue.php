<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

final class JobQueue
{
    public static function schedule(Job $job, int $delaySeconds = 0): void
    {
        $jobQueueRepository = new JobQueueRepository();
        $jobQueueRepository->schedule($job, delaySeconds: $delaySeconds);
    }
}
