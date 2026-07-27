<?php

declare(strict_types=1);

namespace Shared\Deletion\Metrics;

interface MetricsCollectorInterface
{
    /**
     * @param non-negative-int     $value
     * @param array<string,string> $labels
     */
    public function increment(string $name, int $value = 1, array $labels = []): void;
}
