<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

use Fisharebest\Webtrees\DB;
use Komputeryk\Webtrees\JobQueue\Job;

class JobQueueRepository
{
    public function schedule(Job $job, int $delaySeconds = 0): void
    {
        $now = $this->getNow();
        $runAfter = $this->getNow($delaySeconds ?? 0);

        DB::table('job_queue')->insert([
            'created_at' => $now,
            'updated_at' => $now,
            'run_after' => $runAfter,
            'priority' => $job->priority,
            'job' => $job->job,
            'params' => json_encode($job->params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'new',
        ]);
    }

    public function getJob(): ?Job
    {
        return DB::transaction(function () {
            $result = DB::table('job_queue')
                ->where('status', 'new')
                ->where('run_after', '<=', $this->getNow())
                ->orderBy('priority', 'desc')
                ->orderBy('updated_at')
                ->lock('FOR UPDATE SKIP LOCKED')
                ->limit(1)
                ->select()
                ->first();

            if ($result === null) {
                return null;
            }

            DB::table('job_queue')
                ->where('id', $result->id)
                ->update(['status' => 'processing', 'updated_at' => $this->getNow()]);

            return Job::fromDb($result);
        });
    }

    public function setJobSuccess(Job $job): void
    {
        DB::table('job_queue')
            ->where('id', $job->id)
            ->update([
                'status' => 'success',
                'updated_at' => $this->getNow(),
            ]);
    }

    public function setJobError(Job $job, string $message): void
    {
        DB::table('job_queue')
            ->where('id', $job->id)
            ->update([
                'status' => 'error',
                'updated_at' => $this->getNow(),
                'message' => $message,
            ]);
    }

    private function formatTimestamp(int $time): string
    {
        return date('Y-m-d H:i:s', $time);
    }

    private function getNow(int $delay = 0): string
    {
        return $this->formatTimestamp(time() + $delay);
    }
}
