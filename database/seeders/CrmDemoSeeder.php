<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleName;
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
        $author->assignRole(RoleName::SalesOps->value);

        Organization::factory()
            ->count(8)
            ->has(Contact::factory()->count(2))
            ->create(['owner_id' => $author->id])
            ->each(function (Organization $organization) use ($author): void {
                $organization->contacts->each(function (Contact $contact) use ($author, $organization): void {
                    $contact->update(['owner_id' => $author->id]);
                    $lead = Lead::factory()->recycle([$organization, $contact])->create([
                        'organization_id' => $organization->id,
                        'contact_id' => $contact->id,
                        'owner_id' => $author->id,
                    ]);

                    CrmActivity::factory()->count(2)->recycle($lead)->create();
                    FollowUpTask::factory()->recycle($lead)->create();
                    Note::factory()->recycle([$lead, $author])->create([
                        'subject_type' => (new Lead)->getMorphClass(),
                        'subject_id' => $lead->id,
                        'user_id' => $author->id,
                    ]);
                });
            });
    }
}
