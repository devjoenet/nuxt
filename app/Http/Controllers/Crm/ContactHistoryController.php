<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use SoutheastCode\Audit\Http\Controllers\RecordHistoryController;
use SoutheastCode\Audit\Queries\RecordHistoryQuery;
use SoutheastCode\Crm\Contacts\Models\Contact;

class ContactHistoryController extends Controller
{
    public function __invoke(Request $request, Contact $contact, RecordHistoryQuery $query, RecordHistoryController $history): Response
    {
        abort_unless($contact->isVisibleTo($this->authenticatedUser($request)), 404);

        return $history->render($contact, $query, 'Contact', 'crm.contacts');
    }
}
