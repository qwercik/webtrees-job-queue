<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

use stdClass;

class Job
{    
    public function __construct(
        public readonly ?int $id,
        public readonly string $job,
        public readonly array $params,
        public int $priority = 0,
    ) {}

    public static function create(string $job, array $params = [], int $priority = 0): self
    {
        return new self(
            id: null,
            job: $job,
            params: $params,
            priority: $priority,
        );
    }

    public static function fromDb(stdClass $row): self
    {        
        return new self(
            id: (int)$row->id,
            job: $row->job,
            params: json_decode($row->params, true) ?? [],
            priority: (int)$row->priority,
        );
    }
}
