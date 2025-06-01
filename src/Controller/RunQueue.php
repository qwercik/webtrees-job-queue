<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue\Controller;

use Fisharebest\Webtrees\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Komputeryk\Webtrees\JobQueue\JobQueueScheduler;

final class RunQueue implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (Auth::isAdmin() === false) {
            return response()->withStatus(403);
        }

        $scheduler = new JobQueueScheduler();
        $scheduler->run();
        return response();
    }
}
