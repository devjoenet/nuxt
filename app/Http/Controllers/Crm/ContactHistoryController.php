<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RecordHistoryController;
use App\Models\Contact;
use App\Queries\RecordHistoryQuery;
use Illuminate\Http\Request;
use Inertia\Response;

class ContactHistoryController extends Controller
{
    public function __invoke(Request $request, Contact $contact, RecordHistoryQuery $query, RecordHistoryController $history): Response
    {
        abort_unless($contact->isVisibleTo($this->authenticatedUser($request)), 404);

        return $history->render($contact, $query, 'Contact', 'crm.contacts');
    }
}
