<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class CrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        Organization::factory()
            ->count(8)
            ->has(Contact::factory()->count(2))
            ->create()
            ->each(function (Organization $organization): void {
                $organization->contacts->each(function (Contact $contact) use ($organization): void {
                    $lead = Lead::factory()->recycle([$organization, $contact])->create([
                        'organization_id' => $organization->id,
                        'contact_id' => $contact->id,
                    ]);

                    CrmActivity::factory()->count(2)->recycle($lead)->create();
                    FollowUpTask::factory()->recycle($lead)->create();
                });
            });
    }
}
