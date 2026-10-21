<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RecordHistoryController;
use App\Models\Organization;
use App\Queries\RecordHistoryQuery;
use Illuminate\Http\Request;
use Inertia\Response;

class OrganizationHistoryController extends Controller
{
    public function __invoke(Request $request, Organization $organization, RecordHistoryQuery $query, RecordHistoryController $history): Response
    {
        abort_unless($organization->isVisibleTo($this->authenticatedUser($request)), 404);

        return $history->render($organization, $query, 'Organization', 'crm.organizations');
    }
}
