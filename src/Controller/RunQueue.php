<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue\Controller;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Komputeryk\Webtrees\JobQueue\JobQueueRepository;

final class RunQueue implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (Auth::isAdmin() === false) {
            return response()->withStatus(403);
        }

        $limit = Validator::queryParams($request)->integer('limit');
        $pendingJobs = JobQueueRepository::getPendingJobs($limit)->groupBy('job');
        foreach ($pendingJobs as $jobName => $jobs) {
            $executor = Registry::container()->get($jobName);
            $results = $executor->run($jobs->toArray());
            foreach ($results as $id => $result) {
                JobQueueRepository::updateJob($id, $result);
            }
        }

        return response();
    }
}
