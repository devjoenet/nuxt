<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Inertia\Inertia;
use Inertia\Response;

class JobOperationsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($user->can(Permission::JobsView->value), 403);

        $connection = (string) config('queue.default');
        $queue = (string) config(sprintf('queue.connections.%s.queue', $connection), 'default');

        return Inertia::render('Admin/Jobs/Index', [
            'queue' => [
                'connection' => $connection,
                'default' => $queue,
                'production_driver' => 'redis',
            ],
            'stats' => [
                ['label' => 'Pending jobs', 'value' => Queue::size($queue)],
                ['label' => 'Failed database jobs', 'value' => DB::table((string) config('queue.failed.table'))->count()],
                ['label' => 'Batches', 'value' => DB::table((string) config('queue.batching.table'))->count()],
            ],
            'links' => [
                [
                    'label' => 'Horizon dashboard',
                    'description' => 'Live supervisors, throughput, wait times, and queue health.',
                    'url' => route('horizon.index', ['view' => 'dashboard']),
                ],
                [
                    'label' => 'Pending jobs',
                    'description' => 'Inspect jobs waiting for a worker in Horizon.',
                    'url' => route('horizon.index', ['view' => 'jobs/pending']),
                ],
                [
                    'label' => 'Failed jobs',
                    'description' => 'Inspect failures and retry jobs where supported.',
                    'url' => route('horizon.index', ['view' => 'failed']),
                ],
                [
                    'label' => 'Batches',
                    'description' => 'Review batch progress and retry failed batch jobs.',
                    'url' => route('horizon.index', ['view' => 'batches']),
                ],
                [
                    'label' => 'Job search',
                    'description' => 'Search monitored jobs by the tags used by this workspace.',
                    'url' => route('horizon.index', ['view' => 'monitoring']),
                ],
                [
                    'label' => 'Metrics',
                    'description' => 'Review queue and job metrics populated by scheduled snapshots.',
                    'url' => route('horizon.index', ['view' => 'metrics']),
                ],
            ],
        ]);
    }
}
