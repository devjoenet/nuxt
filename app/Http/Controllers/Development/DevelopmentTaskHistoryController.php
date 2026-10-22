<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentTask;
use Illuminate\Http\Request;
use Inertia\Response;
use SoutheastCode\Audit\Http\Controllers\RecordHistoryController;
use SoutheastCode\Audit\Queries\RecordHistoryQuery;

class DevelopmentTaskHistoryController extends Controller
{
    public function __invoke(Request $request, DevelopmentTask $developmentTask, RecordHistoryQuery $query, RecordHistoryController $history): Response
    {
        $this->authorize('view', $developmentTask);

        return $history->render($developmentTask, $query, 'Development task', 'development.tasks');
    }
}
