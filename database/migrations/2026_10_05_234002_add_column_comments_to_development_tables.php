<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $timestamps = [
            'development_tasks' => [
                'submitted_at' => 'Set on submission and retained in every stage. Displayed as Submitted in request/review lists (date) and task summary (date/time), in the browser time zone.',
                'information_requested_at' => 'First entry into needs_information; null before clarification is requested and retained when review resumes. Internal milestone; no separate date display.',
                'review_started_at' => 'First entry into under_review; null before review and retained through planning, work and terminal stages. Internal milestone; no separate date display.',
                'planned_at' => 'First promotion to planned; null before roadmap acceptance and retained in later stages. Used for roadmap ordering; no separate date display.',
                'started_at' => 'First entry into in_progress; null before work starts and retained through blocking, testing and completion. Cleared by an accidental pre-testing roadmap reset. No separate date display.',
                'blocked_at' => 'First entry into blocked; null before a blocker and retained after work resumes. Cleared by an accidental pre-testing roadmap reset. No separate date display.',
                'ready_for_testing_at' => 'First entry into ready_for_testing; null before testing is requested and retained through rework and completion. Later rounds use their own requested_at. No separate date display.',
                'changes_requested_at' => 'First entry into changes_requested after testing; null before rework is requested and retained through subsequent work and testing. No separate date display.',
                'completed_at' => 'First entry into completed after accepted testing; null in earlier stages. Completion also archives the task. Internal milestone; no separate date display.',
                'rejected_at' => 'First entry into rejected during review; null before rejection. Rejection also archives the task. Internal milestone; no separate date display.',
                'cancelled_at' => 'First entry into cancelled; null before cancellation. Cancellation also archives the task. Internal milestone; no separate date display.',
                'created_at' => 'Task row creation, normally at submission; retained in all stages and used for ordering. Not displayed separately from submitted_at.',
                'updated_at' => 'Latest task row change in any stage, including assignment, priority, queue or status changes. Available in task responses; no separate date display.',
                'deleted_at' => 'Soft archive time; null while active and set on completion, rejection or cancellation. Archived tasks leave active queues while retaining history, notes and links. No separate date display.',
            ],
            'development_task_notes' => [
                'created_at' => 'Note creation in any task stage; displayed with the discussion entry as date/time in the browser time zone. Retained after task archival.',
                'updated_at' => 'Latest note row change, independent of the task stage. Retained after task archival; no separate date display.',
            ],
            'development_task_links' => [
                'created_at' => 'Link creation in any task stage; retained after task archival and included in task responses. No separate date display.',
                'updated_at' => 'Latest link row change, independent of the task stage. Retained after task archival; no separate date display.',
            ],
            'development_test_runs' => [
                'requested_at' => 'Set for each pending testing round when testing is requested. Displayed as Requested in testing history and round instructions, as date/time in the browser time zone. Retained after results and archival.',
                'completed_at' => 'Null while the round is pending; set on accepted or changes_requested feedback. Acceptance completes and archives the task; changes_requested returns it to rework. Included in responses; no separate date display.',
                'created_at' => 'Testing round row creation, normally at requested_at. Each round is retained through rework, later rounds and task archival. No separate date display.',
                'updated_at' => 'Latest change to this testing round, including result and feedback; independent of later rounds. Retained after task archival; no separate date display.',
            ],
        ];

        foreach ($timestamps as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $columnName => $comment) {
                    $table->timestamp($columnName)
                        ->nullable(! in_array($columnName, ['submitted_at', 'requested_at'], true))
                        ->comment($comment)
                        ->change();
                }
            });
        }

        $foreignKeys = [
            'development_tasks' => [
                'submitter_id' => 'users.id',
                'reviewer_id' => 'users.id',
                'assignee_id' => 'users.id',
            ],
            'development_task_notes' => [
                'development_task_id' => 'development_tasks.id',
                'author_id' => 'users.id',
            ],
            'development_task_links' => [
                'development_task_id' => 'development_tasks.id',
                'added_by_id' => 'users.id',
            ],
            'development_test_runs' => [
                'development_task_id' => 'development_tasks.id',
                'tester_id' => 'users.id',
                'requested_by_id' => 'users.id',
            ],
        ];

        foreach ($foreignKeys as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $columnName => $target) {
                    $table->unsignedBigInteger($columnName)
                        ->nullable($columnName !== 'development_task_id')
                        ->comment("Relates to `{$target}`.")
                        ->change();
                }
            });
        }
    }
};
