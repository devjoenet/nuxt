<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\CrmSortDirection;
use App\Enums\LeadSort;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;

final readonly class LeadIndexData
{
    public function __construct(
        public ?string $search,
        public ?LeadStatus $status,
        public ?LeadSource $source,
        public ?int $ownerId,
        public bool $archived,
        public LeadSort $sort,
        public CrmSortDirection $direction,
        public bool $sortWasProvided,
        public bool $directionWasProvided,
        public bool $unassigned = false,
        public bool $overdue = false,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $search = $attributes['search'] ?? null;
        $status = $attributes['status'] ?? null;
        $source = $attributes['source'] ?? null;
        $owner = $attributes['owner'] ?? null;
        $sort = $attributes['sort'] ?? null;
        $direction = $attributes['direction'] ?? null;

        return new self(
            unassigned: filter_var($attributes['unassigned'] ?? false, FILTER_VALIDATE_BOOLEAN),
            overdue: filter_var($attributes['overdue'] ?? false, FILTER_VALIDATE_BOOLEAN),
            search: is_string($search) ? $search : null,
            status: is_string($status) ? LeadStatus::from($status) : null,
            source: is_string($source) ? LeadSource::from($source) : null,
            ownerId: is_string($owner) ? (int) $owner : null,
            archived: ($attributes['archived'] ?? false) === true,
            sort: is_string($sort) ? LeadSort::tryFrom($sort) ?? LeadSort::CreatedAt : LeadSort::CreatedAt,
            direction: is_string($direction) ? CrmSortDirection::tryFrom($direction) ?? CrmSortDirection::Desc : CrmSortDirection::Desc,
            sortWasProvided: is_string($sort),
            directionWasProvided: is_string($direction),
        );
    }

    /** @return array<string, int|string> */
    public function filters(): array
    {
        return array_filter([
            'unassigned' => $this->unassigned ? '1' : null,
            'overdue' => $this->overdue ? '1' : null,
            'search' => $this->search,
            'status' => $this->status?->value,
            'source' => $this->source?->value,
            'owner' => $this->ownerId,
            'sort' => $this->sortWasProvided ? $this->sort->value : null,
            'direction' => $this->directionWasProvided ? $this->direction->value : null,
            'archived' => $this->archived ? '1' : null,
        ], static fn (int|string|null $value): bool => $value !== null);
    }
}
