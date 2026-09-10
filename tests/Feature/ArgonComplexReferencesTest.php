<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

test('all eighteen complex examples have protected implemented destinations and provenance', function (): void {
    app(RolePermissionSeeder::class)->run();
    $reviewer = User::factory()->create();
    $reviewer->givePermissionTo(PermissionName::ArgonGalleryView->value);
    $this->withoutVite();
    $manifest = json_decode(File::get(resource_path('argon/source-manifest.json')), true, flags: JSON_THROW_ON_ERROR);
    $examples = array_values(array_filter($manifest['examples'], static fn (array $example): bool => in_array($example['family'], ['applications', 'dashboards', 'ecommerce'], true)));

    expect($examples)->toHaveCount(18);

    foreach ($examples as $example) {
        expect(File::exists(base_path($example['planned_vue_destination'])))->toBeTrue($example['id']);
        $this->actingAs($reviewer)->get(route('admin.argonGallery.examples.show', $example['id']))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Admin/ArgonGallery/Show')
                ->where('example.id', $example['id'])
                ->where('example.implementation_state', 'implemented')
                ->where('example.source_path', $example['source_path'])
                ->where('example.divergence_note', fn (mixed $note): bool => is_string($note) && str_contains($note, 'Task 12:') && str_contains($note, 'lazy')));
    }
});

test('complex example routes refuse users without gallery permission and reject writes', function (): void {
    app(RolePermissionSeeder::class)->run();
    $user = User::factory()->create();
    $url = route('admin.argonGallery.examples.show', 'ecommerce-products-new-product');

    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs($user)->get($url)->assertForbidden();
    $user->givePermissionTo(PermissionName::ArgonGalleryView->value);
    $this->actingAs($user)->post($url, ['name' => 'Unwanted product'])->assertMethodNotAllowed();
});
