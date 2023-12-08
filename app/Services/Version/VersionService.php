<?php

namespace App\Services\Version;

use App\Services\Search\Conditions;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * This class provides a clean and consistent way of managing versioned models.
 * Assuming you stick to the following pattern, this class can be used for all
 * models that require some sort of versioning:
 *
 * 1. There should be parents and parent_versions (AKA children) tables.
 * 2. The parent should only contain and ID and any foreign keys.
 * 3. The child should hold all the data and also status, approved_rejected_at,
 *    approved_rejected_by, and rejected_reason columns.
 * 4. The models should allow any attribute to be set through mass assignment.
 * 5. There should be parent and child resources to transform the data in the
 *    controllers.
 *
 * The return types of these methods are largely dedicated parent and child
 * classes, these ensure a parent and child are always returned correctly.
 */
class VersionService
{
    public $parentModel = null;

    public $childModel = null;

    private $foreignKey = null;

    public function __construct(Model $parentModel, Model $childModel)
    {
        $this->parentModel = $parentModel;
        $this->childModel = $childModel;
        $this->foreignKey = Str::snake(explode_pop('\\', get_class($this->parentModel))). '_id';
    }

    public function createParentAndChild(array $parentData, array $childData): ParentAndChild
    {
        $parent = $this->parentModel->newInstance($parentData);
        $parent->saveOrFail();
        $parent->refresh();
        $child = $this->childModel->newInstance($childData);
        $child->{$this->foreignKey} = $parent->id;
        $child->status = 'pending';
        $child->saveOrFail();
        $child->refresh();

        return new ParentAndChild($parent, $child);
    }

    public function findParent(int $parentId): ParentAndChild
    {
        $parent = $this->parentModel->findOrFail($parentId);
        $child = $this->childModel->where('status', '=', 'approved')->where($this->foreignKey, '=', $parent->id)->first();

        return new ParentAndChild($parent, $child);
    }

    public function updateChild(int $parentId, array $childData): ParentAndChild
    {
        $parent = $this->parentModel->findOrFail($parentId);
        $statuses = ['pending', 'approved', 'rejected', 'archived'];
        foreach ($statuses as $status) {
            $child = $this->childModel->where($this->foreignKey, '=', $parent->id)->where('status', '=', $status)->orderByDesc('id')->first();
            if (! is_null($child)) {
                break;
            }
        }
        if (is_null($child)) {
            throw new Exception();
        }
        $this->childModel->where($this->foreignKey, '=', $parent->id)->where('status', '=', 'pending')->update(['status' => 'archived']);
        $child = $this->childModel->newInstance($child->toArray());
        $child->fill($childData);
        $child->status = 'pending';
        unset(
            $child->id,
            $child->approved_rejected_by,
            $child->approved_rejected_at,
            $child->rejected_reason,
            $child->rejected_reason_body,
            $child->created_at,
            $child->updated_at
        );
        $child->saveOrFail();
        $child->refresh();

        return new ParentAndChild($parent, $child);
    }

    public function findAllParents($conditions = []): Collection
    {
        $parents = $this->parentModel->where($conditions)->get();
        $parentIds = $parents->pluck('id')->toArray();
        $children = $this->childModel->where('status', '=', 'approved')->whereIn($this->foreignKey, $parentIds)->get();
        $collection = new Collection();
        foreach ($parents as $parent) {
            $child = $children->where($this->foreignKey, '=', $parent->id)->first();
            if (! is_null($child)) {
                $collection->add(new ParentAndChild($parent, $child));
            }
        }

        return $collection;
    }

    public function findChild(int $childId): ParentAndChild
    {
        $child = $this->childModel->findOrFail($childId);
        $parent = $this->parentModel->findOrFail($child->{$this->foreignKey});

        return new ParentAndChild($parent, $child);
    }

    public function findAllChildren(int $parentId): ParentAndChildren
    {
        $parent = $this->parentModel->findOrFail($parentId);
        $children = $this->childModel->where($this->foreignKey, '=', $parent->id)->get();

        return new ParentAndChildren($parent, $children);
    }

    public function approveChild(int $childId, int $userId): ParentAndChild
    {
        $child = $this->childModel->findOrFail($childId);
        $this->childModel->where($this->foreignKey, '=', $child->{$this->foreignKey})->where('status', '=', 'approved')->update(['status' => 'archived']);
        $child->status = 'approved';
        $child->approved_rejected_at = new DateTime('now', new DateTimeZone('UTC'));
        $child->approved_rejected_by = $userId;
        $child->saveOrFail();
        $parent = $this->parentModel->findOrFail($child->{$this->foreignKey});

        return new ParentAndChild($parent, $child);
    }

    public function rejectChild(int $childId, int $userId, string $rejectedReason, string $rejectedReasonBody): ParentAndChild
    {
        $child = $this->childModel->findOrFail($childId);
        $child->status = 'rejected';
        $child->approved_rejected_at = new DateTime('now', new DateTimeZone('UTC'));
        $child->approved_rejected_by = $userId;
        $child->rejected_reason = $rejectedReason;
        $child->rejected_reason_body = $rejectedReasonBody;
        $child->saveOrFail();
        $parent = $this->parentModel->findOrFail($child->{$this->foreignKey});

        return new ParentAndChild($parent, $child);
    }

    public function deleteChild(int $childId): void
    {
        $child = $this->childModel->findOrFail($childId);
        $child->status = 'archived';
        $child->saveOrFail();
    }

    public function findAllPendingChildren(): Collection
    {
        $children = $this->childModel->where('status', '=', 'pending')->get();
        $parentIds = $children->pluck($this->foreignKey)->toArray();
        $parents = $this->parentModel->whereIn('id', $parentIds)->get();
        $collection = new Collection();
        foreach ($children as $child) {
            $parent = $parents->where('id', '=', $child->{$this->foreignKey})->first();
            $collection->add(new ParentAndChild($parent, $child));
        }

        return $collection;
    }

    public function searchAllApprovedChildren(Conditions $conditions, Int $organisationId = null): Collection
    {
        $children = $this->childModel->search($conditions, $organisationId)->where('status', '=', 'approved')->get();
        $parentIds = $children->pluck($this->foreignKey)->toArray();
        $parents = $this->parentModel->whereIn('id', $parentIds)->get();
        $collection = new Collection();
        foreach ($children as $child) {
            $parent = $parents->where('id', '=', $child->{$this->foreignKey})->first();
            $collection->add(new ParentAndChild($parent, $child));
        }

        return $collection;
    }

    public function groupAllChildren($conditions = []): Collection
    {
        $parents = $this->parentModel->where($conditions)->get();
        $parentIds = $parents->pluck('id')->toArray();
        $children = $this->childModel->whereIn($this->foreignKey, $parentIds)->get();
        $collection = new Collection();
        foreach ($parents as $parent) {
            $statuses = ['pending', 'approved', 'rejected', 'archived'];
            foreach ($statuses as $status) {
                $child = $children->where($this->foreignKey, '=', $parent->id)->where('status', '=', $status)->sortByDesc('created_at')->first();
                if (! is_null($child)) {
                    break;
                }
            }
            if (is_null($child)) {
                throw new Exception();
            }
            $collection->add(new ParentAndChild($parent, $child));
        }

        return $collection;
    }

    public function updateParentVisibility(int $parentId, string $visibility): ParentAndChild
    {
        $parent = $this->parentModel->findOrFail($parentId);
        $child = $this->childModel->where('status', '=', 'approved')->where($this->foreignKey, '=', $parent->id)->first();
        $parent->visibility = $visibility;
        $parent->visibility_updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $parent->saveOrFail();

        return new ParentAndChild($parent, $child);
    }
}
