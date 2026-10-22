<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Sleep::fake();

        $this->freezeTime();
    })
    ->in('Browser', 'Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something(): void
{
    // ..
}

/** @return array<int, Symfony\Component\Finder\SplFileInfo> */
function frontendContractFiles(): array
{
    $files = Illuminate\Support\Facades\File::allFiles(resource_path('js'));
    foreach (Composer\InstalledVersions::getInstalledPackages() as $name) {
        if (! str_starts_with($name, 'southeastcode/') || $name === Composer\InstalledVersions::getRootPackage()['name']) {
            continue;
        }
        $path = Composer\InstalledVersions::getInstallPath($name);
        if ($path !== null && is_dir($path.'/resources/js')) {
            array_push($files, ...Illuminate\Support\Facades\File::allFiles($path.'/resources/js'));
        }
    }

    return $files;
}
