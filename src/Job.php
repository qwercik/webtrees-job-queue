<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

class Job
{
    public function __construct(
        public readonly int $id,
        public readonly string $job,
        public readonly array $data,
    ) {}
}
