<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class CrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::factory()->create();

        Organization::factory()
            ->count(8)
            ->has(Contact::factory()->count(2))
            ->create()
            ->each(function (Organization $organization) use ($author): void {
                $organization->contacts->each(function (Contact $contact) use ($author, $organization): void {
                    $lead = Lead::factory()->recycle([$organization, $contact])->create([
                        'organization_id' => $organization->id,
                        'contact_id' => $contact->id,
                    ]);

                    CrmActivity::factory()->count(2)->recycle($lead)->create();
                    FollowUpTask::factory()->recycle($lead)->create();
                    Note::factory()->recycle([$lead, $author])->create([
                        'subject_type' => Lead::class,
                        'subject_id' => $lead->id,
                        'user_id' => $author->id,
                    ]);
                });
            });
    }
}
