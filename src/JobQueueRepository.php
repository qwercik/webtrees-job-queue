<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue;

use Fisharebest\Webtrees\DB;
use Illuminate\Support\Collection;
use Komputeryk\Webtrees\JobQueue\Job;

class JobQueueRepository
{
    public static function getPendingJobs(int $limit): Collection
    {
        $status = uniqid();

        DB::table('job_queue')
            ->where('status', '=', 'new')
            ->orWhere(fn($q) =>
                $q->whereNotIn('status', ['new', 'error', 'success'])
                ->where('updated_at', '<', static::getTimestamp(-60))
            )
            ->limit($limit)
            ->orderBy('updated_at', 'asc')
            ->update(['status' => $status, 'updated_at' => static::getTimestamp()]);

        return DB::table('job_queue')
            ->where('status', '=', $status)
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn($job) => new Job(
                (int)$job->id,
                $job->job,
                json_decode($job->data, true)
            ));
    }

    public static function schedule(string $jobName, array $data): void
    {
        $timestamp = static::getTimestamp();
        DB::table('job_queue')->updateOrInsert([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'job' => $jobName,
            'data' => self::encodeData($data),
            'status' => 'new',
        ]);
    }

    public static function isScheduled(string $jobName, array $data): bool
    {
        $count = DB::table('job_queue')
            ->where('job', '=', $jobName)
            ->where('data', '=', self::encodeData($data))
            ->where('status', '=', 'new')
            ->count();

        return $count > 0;
    }

    public static function updateJob(int $id, array $result): void
    {
        DB::table('job_queue')
            ->where('id', '=', $id)
            ->update([
                ...$result,
                'updated_at' => static::getTimestamp(),
            ]);
    }

    private static function encodeData(array $data): string
    {
        ksort($data);
        return json_encode($data);
    }

    private static function getTimestamp(int $offset = 0): string
    {
        return date('Y-m-d H:i:s', time() + $offset);
    }
}
