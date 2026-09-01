<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

function galleryUser(): User
{
    $role = Role::findOrCreate('Gallery Reviewer', 'web');
    $role->givePermissionTo(PermissionName::ArgonGalleryView->value);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('guests are redirected from the Argon gallery to login', function (): void {
    $this->get(route('admin.argonGallery.index'))->assertRedirect(route('login'));
});

test('users without the dedicated permission cannot open gallery routes', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.argonGallery.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.argonGallery.examples.show', 'account-billing'))->assertForbidden();
});

test('the dedicated gallery permission works without unrelated admin permissions', function (): void {
    $user = galleryUser();

    expect($user->can(PermissionName::ArgonGalleryView->value))->toBeTrue()
        ->and($user->can(PermissionName::AdminDashboardView->value))->toBeFalse();

    $this->actingAs($user)
        ->get(route('admin.argonGallery.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Admin/ArgonGallery/Index'));
});

test('the gallery exposes complete manifest status and all family entries', function (): void {
    $user = galleryUser();

    $this->actingAs($user)
        ->get(route('admin.argonGallery.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Admin/ArgonGallery/Index')
            ->where('summary.complete', true)
            ->where('summary.total', 69)
            ->where('summary.routable', 69)
            ->where('summary.available', 66)
            ->where('summary.empty', 3)
            ->where('summary.families', 8)
            ->has('examples', 69)
            ->where('family_counts', [
                'account' => 4,
                'applications' => 5,
                'authentication' => 17,
                'dashboards' => 5,
                'ecommerce' => 8,
                'general' => 22,
                'projects' => 3,
                'team' => 5,
            ]));
});

test('every manifest entry resolves to its protected reference route', function (): void {
    $user = galleryUser();
    $manifest = json_decode(File::get(resource_path('argon/source-manifest.json')), true, flags: JSON_THROW_ON_ERROR);

    foreach ($manifest['examples'] as $example) {
        $this->actingAs($user)
            ->get(route('admin.argonGallery.examples.show', $example['id']))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Admin/ArgonGallery/Show')
                ->where('example.id', $example['id'])
                ->where('example.source_path', $example['source_path'])
                ->where('example.source_status', $example['source_status'])
                ->where('example.divergence_note', fn (mixed $note): bool => is_string($note) && $note !== ''));
    }
});

test('unknown manifest identifiers return not found', function (): void {
    $this->actingAs(galleryUser())
        ->get(route('admin.argonGallery.examples.show', 'not-a-manifest-entry'))
        ->assertNotFound();
});

test('gallery laboratories remain lazy and absent from ordinary entry source', function (): void {
    $labs = File::get(resource_path('js/pages/Admin/ArgonGallery/Components/galleryLabs.ts'));
    $page = File::get(resource_path('js/pages/Admin/ArgonGallery/Index.vue'));
    $applicationEntry = File::get(resource_path('js/app.ts'));
    $ssrEntry = File::get(resource_path('js/ssr.ts'));

    expect($labs)
        ->toContain('defineAsyncComponent')
        ->toContain('import("./FoundationLab.vue")')
        ->toContain('import("./PrimitiveLab.vue")')
        ->toContain('import("./FormLab.vue")')
        ->toContain('import("./LayoutLab.vue")')
        ->toContain('import("./AdvancedLab.vue")')
        ->and($page)->not->toContain('FoundationLab', 'PrimitiveLab', 'FormLab', 'LayoutLab', 'AdvancedLab')
        ->and($applicationEntry)->not->toContain('ArgonGallery', 'GalleryLaboratory', 'AdvancedLab')
        ->and($ssrEntry)->not->toContain('ArgonGallery', 'GalleryLaboratory', 'AdvancedLab');
});

test('the production manifest emits distinct gallery lab chunks outside an ordinary page bundle', function (): void {
    $manifestPath = public_path('build/manifest.json');

    if (! File::exists($manifestPath)) {
        $this->markTestSkipped('Build the frontend before checking production chunk isolation.');
    }

    $manifest = json_decode(File::get($manifestPath), true, flags: JSON_THROW_ON_ERROR);
    $labSources = [
        'resources/js/pages/Admin/ArgonGallery/Components/FoundationLab.vue',
        'resources/js/pages/Admin/ArgonGallery/Components/PrimitiveLab.vue',
        'resources/js/pages/Admin/ArgonGallery/Components/FormLab.vue',
        'resources/js/pages/Admin/ArgonGallery/Components/LayoutLab.vue',
        'resources/js/pages/Admin/ArgonGallery/Components/AdvancedLab.vue',
    ];
    $labChunks = array_map(fn (string $source): string => $manifest[$source]['file'], $labSources);
    $dashboardImports = $manifest['resources/js/pages/Dashboard.vue']['imports'];

    expect($labChunks)->toHaveCount(5)
        ->and(array_unique($labChunks))->toHaveCount(5);

    foreach ($labChunks as $chunk) {
        expect($dashboardImports)->not->toContain($chunk);
    }
});

test('admin role seeding includes the gallery permission and navigation is permission filtered', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $navigation = File::get(resource_path('js/composables/useWorkspaceNavigation.ts'));

    expect($admin->can(PermissionName::ArgonGalleryView->value))->toBeTrue()
        ->and($navigation)
        ->toContain('can("argon.gallery.view")')
        ->toContain('label: "Argon gallery"')
        ->toContain('argonGalleryIndex.url()');
});
