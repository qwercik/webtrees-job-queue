<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

use Fisharebest\Webtrees\Contracts\ContainerInterface;
use Fisharebest\Webtrees\Registry;

class JobQueueScheduler {
    private const INTERVAL = 60;

    private float $startTime;
    private JobQueueRepository $jobQueueRepository;
    private ContainerInterface $container;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->jobQueueRepository = new JobQueueRepository();
        $this->container = Registry::container();
    }

    public function run(): void
    {
        while ($this->shouldContinue()) {
            $job = $this->jobQueueRepository->getJob();
            if ($job === null) {
                break;
            }

            $this->executeJob($job);
        }
    }

    private function shouldContinue(): bool
    {
        return (microtime(true) - $this->startTime) < self::INTERVAL;
    }

    private function executeJob(Job $job): void
    {
        try {
            $this->container->get($job->job)->run($job);
            $this->jobQueueRepository->setJobSuccess($job);
        } catch (\Throwable $e) {
            $this->jobQueueRepository->setJobError($job, $e->getMessage());
        }
    }
}
