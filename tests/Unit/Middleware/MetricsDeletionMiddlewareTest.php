<?php

declare(strict_types=1);

namespace Shared\Deletion\Tests\Unit\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Deletion\Metrics\MetricsCollectorInterface;
use Shared\Deletion\Middleware\MetricsDeletionMiddleware;

final class MetricsDeletionMiddlewareTest extends TestCase
{
    private MetricsCollectorInterface&MockObject $metrics;
    private MetricsDeletionMiddleware $middleware;

    protected function setUp(): void
    {
        $this->metrics = $this->createMock(MetricsCollectorInterface::class);
        $this->middleware = new MetricsDeletionMiddleware($this->metrics);
    }

    public function testSupportsAnyEntityClass(): void
    {
        self::assertTrue($this->middleware->supports(RootEntity::class));
        self::assertTrue($this->middleware->supports(ChildEntity::class));
    }

    public function testBeforeMethodsDoNotRecordMetrics(): void
    {
        $root = new RootEntity();

        $this->metrics->expects(self::never())->method('increment');

        $this->middleware->beforeDetachRelations(
            RootEntity::class,
            ChildEntity::class,
            [1, 2],
            ['joinTable' => 'root_child'],
            $root
        );
        $this->middleware->beforeDeleteChildren(ChildEntity::class, [1, 2], $root);
        $this->middleware->beforeDeleteRoot($root);
    }

    public function testRecordsNumberOfSuccessfullyDetachedRelations(): void
    {
        $this->metrics
            ->expects(self::once())
            ->method('increment')
            ->with(
                'deletion_detached_relations_total',
                3,
                [
                    'parent_class' => RootEntity::class,
                    'child_class' => ChildEntity::class,
                ]
            )
        ;

        $this->middleware->afterDetachRelations(
            RootEntity::class,
            ChildEntity::class,
            [10, 20, 30],
            ['joinTable' => 'root_child'],
            new RootEntity()
        );
    }

    public function testRecordsNumberOfSuccessfullyDeletedChildren(): void
    {
        $this->metrics
            ->expects(self::once())
            ->method('increment')
            ->with(
                'deletion_deleted_children_total',
                2,
                [
                    'root_class' => RootEntity::class,
                    'child_class' => ChildEntity::class,
                ]
            )
        ;

        $this->middleware->afterDeleteChildren(
            ChildEntity::class,
            [10, 20],
            new RootEntity()
        );
    }

    public function testRecordsSuccessfullyDeletedRoot(): void
    {
        $this->metrics
            ->expects(self::once())
            ->method('increment')
            ->with(
                'deletion_deleted_roots_total',
                1,
                ['root_class' => RootEntity::class]
            )
        ;

        $this->middleware->afterDeleteRoot(new RootEntity());
    }
}

final class RootEntity
{
}

final class ChildEntity
{
}
