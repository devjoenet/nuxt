<?php

declare(strict_types=1);

namespace App\Data;

use SoutheastCode\Crm\Organizations\Enums\OrganizationSort;
use SoutheastCode\Crm\Shared\Enums\CrmSortDirection;

final readonly class OrganizationIndexData
{
    public function __construct(
        public ?string $search,
        public ?int $ownerId,
        public bool $archived,
        public OrganizationSort $sort,
        public CrmSortDirection $direction,
        public bool $sortWasProvided,
        public bool $directionWasProvided,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $search = $attributes['search'] ?? null;
        $owner = $attributes['owner'] ?? null;
        $sort = $attributes['sort'] ?? null;
        $direction = $attributes['direction'] ?? null;

        return new self(
            search: is_string($search) ? $search : null,
            ownerId: is_string($owner) ? (int) $owner : null,
            archived: ($attributes['archived'] ?? false) === true,
            sort: is_string($sort) ? OrganizationSort::tryFrom($sort) ?? OrganizationSort::CreatedAt : OrganizationSort::CreatedAt,
            direction: is_string($direction) ? CrmSortDirection::tryFrom($direction) ?? CrmSortDirection::Desc : CrmSortDirection::Desc,
            sortWasProvided: is_string($sort),
            directionWasProvided: is_string($direction),
        );
    }

    /** @return array<string, int|string> */
    public function filters(): array
    {
        return array_filter([
            'search' => $this->search,
            'owner' => $this->ownerId,
            'sort' => $this->sortWasProvided ? $this->sort->value : null,
            'direction' => $this->directionWasProvided ? $this->direction->value : null,
            'archived' => $this->archived ? '1' : null,
        ], static fn (int|string|null $value): bool => $value !== null);
    }
}
