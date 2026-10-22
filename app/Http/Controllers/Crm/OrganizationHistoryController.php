<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use SoutheastCode\Audit\Http\Controllers\RecordHistoryController;
use SoutheastCode\Audit\Queries\RecordHistoryQuery;
use SoutheastCode\Crm\Organizations\Models\Organization;

class OrganizationHistoryController extends Controller
{
    public function __invoke(Request $request, Organization $organization, RecordHistoryQuery $query, RecordHistoryController $history): Response
    {
        abort_unless($organization->isVisibleTo($this->authenticatedUser($request)), 404);

        return $history->render($organization, $query, 'Organization', 'crm.organizations');
    }
}
