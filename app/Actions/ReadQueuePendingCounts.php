<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Horizon\ProvisioningPlan;
use Laravel\Horizon\SupervisorOptions;
use Throwable;
use UnexpectedValueException;

class ReadQueuePendingCounts
{
    /** @return array{total: ?int, queues: list<array{connection: string, name: string, pending: ?int}>} */
    public function handle(): array
    {
        $connection = Config::string('queue.default');
        $usesRedis = config("queue.connections.{$connection}.driver") === 'redis';
        $environment = app()->environment();
        $plan = ProvisioningPlan::get('operations');

        $supervisors = collect($plan->parsed)->first(fn (mixed $options, string $name): bool => Str::is($name, $environment));

        if ($supervisors === null && ! $usesRedis) {
            $plan = new ProvisioningPlan('operations', [$environment => []], Config::array('horizon.defaults', []));
            $supervisors = $plan->parsed[$environment];
        }

        if ($supervisors !== null && ! is_iterable($supervisors)) {
            throw new UnexpectedValueException('Horizon supervisors must be iterable.');
        }

        $queues = [];

        foreach ($supervisors ?? [] as $supervisor) {
            if (! $supervisor instanceof SupervisorOptions) {
                throw new UnexpectedValueException('Horizon supervisors must contain supervisor options.');
            }

            if ($supervisor->maxProcesses <= 0) {
                continue;
            }

            $queueConnection = $usesRedis ? $supervisor->connection : $connection;

            foreach (explode(',', $supervisor->queue) as $name) {
                $name = mb_trim($name);
                $queues["{$queueConnection}:{$name}"] = ['connection' => $queueConnection, 'name' => $name];
            }
        }

        $default = Config::string("queue.connections.{$connection}.queue", 'default');
        $queues["{$connection}:{$default}"] = ['connection' => $connection, 'name' => $default];
        $counts = [];
        $total = 0;
        $unavailableConnections = [];

        foreach ($queues as $queue) {
            $pending = null;

            if (! isset($unavailableConnections[$queue['connection']])) {
                try {
                    $pending = Queue::connection($queue['connection'])->pendingSize($queue['name']);
                } catch (Throwable $exception) {
                    report($exception);
                    $unavailableConnections[$queue['connection']] = true;
                }
            }

            $counts[] = [...$queue, 'pending' => $pending];
            $total += $pending ?? 0;
        }

        return ['total' => $unavailableConnections === [] ? $total : null, 'queues' => $counts];
    }
}
