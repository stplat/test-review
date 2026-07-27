<?php

declare(strict_types=1);

namespace Shared\Deletion\Middleware;

use Shared\Deletion\Metrics\MetricsCollectorInterface;

final class MetricsDeletionMiddleware implements DeletionMiddlewareInterface
{
    private const string DETACHED_RELATIONS_TOTAL = 'deletion_detached_relations_total';
    private const string DELETED_CHILDREN_TOTAL = 'deletion_deleted_children_total';
    private const string DELETED_ROOTS_TOTAL = 'deletion_deleted_roots_total';

    public function __construct(private readonly MetricsCollectorInterface $metrics)
    {
    }

    public function supports(string $entityClass): bool
    {
        return true;
    }

    public function beforeDetachRelations(string $parentClass, string $childClass, array $childIds, array $relation, object $root): void
    {
    }

    public function afterDetachRelations(string $parentClass, string $childClass, array $childIds, array $relation, object $root): void
    {
        $this->metrics->increment(
            self::DETACHED_RELATIONS_TOTAL,
            count($childIds),
            [
                'parent_class' => $parentClass,
                'child_class' => $childClass,
            ]
        );
    }

    public function beforeDeleteChildren(string $childClass, array $childIds, object $root): void
    {
    }

    public function afterDeleteChildren(string $childClass, array $childIds, object $root): void
    {
        $this->metrics->increment(
            self::DELETED_CHILDREN_TOTAL,
            count($childIds),
            [
                'root_class' => $root::class,
                'child_class' => $childClass,
            ]
        );
    }

    public function beforeDeleteRoot(object $root): void
    {
    }

    public function afterDeleteRoot(object $root): void
    {
        $this->metrics->increment(
            self::DELETED_ROOTS_TOTAL,
            labels: ['root_class' => $root::class]
        );
    }
}
