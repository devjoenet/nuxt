<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\DevelopmentWorkflow\Contracts\LegacyPageUrlResolver;
use SoutheastCode\DevelopmentWorkflow\Models\UserTestingPage;
use SoutheastCode\DevelopmentWorkflow\Support\DefaultLegacyPageUrlResolver;
use SoutheastCode\Modules\WorkspaceUser;

final class AgencyLegacyPageUrlResolver implements LegacyPageUrlResolver
{
    public function __construct(private readonly DefaultLegacyPageUrlResolver $defaultResolver) {}

    public function url(UserTestingPage $page, WorkspaceUser $user): ?string
    {
        if (! $user instanceof Model) {
            return null;
        }

        $record = match ($page->route_name) {
            'crm.leads.show', 'crm.leads.edit' => Lead::query()->visibleTo($user)->oldest('created_at')->first(),
            'crm.organizations.show', 'crm.organizations.edit' => Organization::query()->visibleTo($user)->oldest('created_at')->first(),
            'crm.contacts.show', 'crm.contacts.edit' => Contact::query()->visibleTo($user)->oldest('created_at')->first(),
            default => null,
        };

        if ($record instanceof Model) {
            return route($page->route_name, $record, false);
        }

        return $this->defaultResolver->url($page, $user);
    }
}
