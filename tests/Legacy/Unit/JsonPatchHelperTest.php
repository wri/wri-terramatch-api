<?php

namespace Tests\Legacy\Unit;

use App\Helpers\JsonPatchHelper;
use Tests\Legacy\LegacyTestCase;

final class JsonPatchHelperTest extends LegacyTestCase
{
    private function assertIdentical($a, $b): Void
    {
        $this->assertSame(json_encode($a), json_encode($b));
    }

    public function testRemoveOpsAreReorderedCorrectly(): void
    {
        $before = [
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
        ];
        $after = [
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'remove', 'path' => '/foo/1'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }

    public function testRemoveOpsAreReorderedCorrectlyAgain(): void
    {
        $before = [
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'remove', 'path' => '/foo/3'],
        ];
        $after = [
            (object) ['op' => 'remove', 'path' => '/foo/3'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'remove', 'path' => '/foo/1'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }

    public function testRemoveOpsAreReorderedCorrectlyAgainAgain(): void
    {
        $before = [
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
        ];
        $after = [
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }

    public function testRemoveOpsAreReorderedCorrectlyAgainAgainAgain(): void
    {
        $before = [
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
        ];
        $after = [
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }

    public function testRemoveOpsAreReorderedCorrectlyAgainAgainAgainAgain(): void
    {
        $before = [
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/3'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
        ];
        $after = [
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/3'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/2'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
            (object) ['op' => 'remove', 'path' => '/foo/1'],
            (object) ['op' => 'add', 'path' => '/bar', 'value' => 'bar'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }

    public function testRemoveOpsAreReorderedCorrectlyAgainAgainAgainAgainAgain(): void
    {
        $before = [
            (object) ['op' => 'remove', 'path' => '/foo/1'],
        ];
        $after = [
            (object) ['op' => 'remove', 'path' => '/foo/1'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }

    public function testExistingOpsArentAffected(): void
    {
        $before = [
            (object) ['op' => 'add', 'path' => 'foo', 'value' => 'foo'],
            (object) ['op' => 'add', 'path' => 'bar', 'value' => 'bar'],
        ];
        $after = [
            (object) ['op' => 'add', 'path' => 'foo', 'value' => 'foo'],
            (object) ['op' => 'add', 'path' => 'bar', 'value' => 'bar'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }

    public function testExistingOpsArentAffectedAgain(): void
    {
        $before = [
            (object) ['op' => 'add', 'path' => 'foo', 'value' => 'foo'],
            (object) ['op' => 'add', 'path' => 'bar', 'value' => 'bar'],
            (object) ['op' => 'add', 'path' => 'baz', 'value' => 'baz'],
        ];
        $after = [
            (object) ['op' => 'add', 'path' => 'foo', 'value' => 'foo'],
            (object) ['op' => 'add', 'path' => 'bar', 'value' => 'bar'],
            (object) ['op' => 'add', 'path' => 'baz', 'value' => 'baz'],
        ];
        $this->assertIdentical($after, JsonPatchHelper::reorderRemoveOps($before));
    }
}
