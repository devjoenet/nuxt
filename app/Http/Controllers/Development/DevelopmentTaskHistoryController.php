<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RecordHistoryController;
use App\Models\DevelopmentTask;
use App\Queries\RecordHistoryQuery;
use Illuminate\Http\Request;
use Inertia\Response;

class DevelopmentTaskHistoryController extends Controller
{
    public function __invoke(Request $request, DevelopmentTask $developmentTask, RecordHistoryQuery $query, RecordHistoryController $history): Response
    {
        $this->authorize('view', $developmentTask);

        return $history->render($developmentTask, $query, 'Development task', 'development.tasks');
    }
}
