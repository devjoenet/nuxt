<?php

declare(strict_types=1);

it('checks for missing debug statements', function (): void {
    expect(['dd', 'dump', 'ray'])
        ->not->toBeUsed();
});

it('checks the validator facade is not used', function (): void {
    expect(Illuminate\Support\Facades\Validator::class)
        ->not->toBeUsed()
        ->ignoring('App\Actions\Fortify');
});

test('action handlers do not accept untyped structured payloads', function (): void {
    foreach (glob(app_path('Actions/*.php')) ?: [] as $path) {
        $class = 'App\\Actions\\'.pathinfo($path, PATHINFO_FILENAME);
        $method = (new ReflectionClass($class))->getMethod('handle');

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            $typeNames = match (true) {
                $type instanceof ReflectionNamedType => [$type->getName()],
                $type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType => array_map(
                    fn (ReflectionNamedType $type): string => $type->getName(),
                    $type->getTypes(),
                ),
                default => [],
            };

            expect($typeNames)->not->toBeEmpty();
            expect($typeNames)->not->toContain('array');
            expect($typeNames)->not->toContain('mixed');
        }
    }
});

test('query builders do not accept untyped structured filters', function (): void {
    $classes = array_map(
        fn (string $path): string => 'App\\Queries\\'.pathinfo($path, PATHINFO_FILENAME),
        glob(app_path('Queries/*.php')) ?: [],
    );

    foreach ([...$classes, SoutheastCode\Crm\Leads\Queries\LeadQuery::class] as $class) {
        $reflection = new ReflectionClass($class);

        if (! $reflection->hasMethod('build')) {
            continue;
        }

        $method = $reflection->getMethod('build');

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            $typeNames = match (true) {
                $type instanceof ReflectionNamedType => [$type->getName()],
                $type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType => array_map(
                    fn (ReflectionNamedType $type): string => $type->getName(),
                    $type->getTypes(),
                ),
                default => [],
            };

            expect($typeNames)->not->toBeEmpty();
            expect($typeNames)->not->toContain('array');
            expect($typeNames)->not->toContain('mixed');
        }
    }
});
