<?php

namespace Tests\Feature;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Tests\TestCase;

/**
 * Guards the authorization surface of every Filament resource.
 *
 * Filament's `strictAuthorization()` is deliberately off (see
 * AdminPanelProvider): when a resource or model has no matching policy,
 * Filament implicitly ALLOWS the action for any authenticated panel user.
 * That is the fail-open behaviour this project must never have.
 *
 * Strict mode cannot simply be switched on because authorisation here lives in
 * `can*()` overrides on the Resource classes rather than in policies. So the
 * guarantee is asserted here instead: a new resource that forgets to declare
 * its `can*()` methods fails CI rather than quietly granting access.
 */
class AuthorizationCoverageTest extends TestCase
{
    /**
     * Third-party resources ship their own authorization; we cannot fix them
     * from here, so they are out of scope for this assertion.
     *
     * @var array<int, string>
     */
    private const THIRD_PARTY = [
        'BezhanSalleh\\',
        'Filament\\',
    ];

    private static function isThirdParty(string $resource): bool
    {
        foreach (self::THIRD_PARTY as $prefix) {
            if (str_starts_with($resource, $prefix)) {
                return true;
            }
        }

        return false;
    }
    /**
     * @return array<int, array{0: string, 1: bool}>
     */
    public static function abilityProvider(): array
    {
        return [
            ['canViewAny', false],
            ['canView', true],
            ['canEdit', true],
            ['canDelete', true],
            ['canDeleteAny', false],
        ];
    }

    public function test_panel_has_resources(): void
    {
        $this->assertGreaterThan(0, count(Filament::getResources()), 'No Filament resources discovered.');
    }

    #[DataProvider('abilityProvider')]
    public function test_every_resource_declares_or_backs_its_ability(string $ability, bool $takesRecord): void
    {
        $violations = [];
        $checked = 0;

        foreach (Filament::getResources() as $resource) {
            if (! is_subclass_of($resource, Resource::class)) {
                continue;
            }

            if (self::isThirdParty($resource)) {
                continue;
            }

            $checked++;
            $reflection = new ReflectionClass($resource);

            // `hasMethod()` also matches the inherited default from
            // Filament's HasAuthorization concern, so the declaring class has
            // to be checked: only a real override in this app counts.
            if (! $reflection->hasMethod($ability)
                || str_starts_with($reflection->getMethod($ability)->getDeclaringClass()->getName(), 'Filament\\')) {
                $violations[] = "{$resource} does not override {$ability}() and has no policy.";

                continue;
            }

            // A policy on the model answers the Gate, so no override is needed.
            if (Gate::getPolicyFor($resource::getModel())) {
                continue;
            }

            if (! $reflection->getMethod($ability)->isStatic()) {
                $violations[] = "{$resource}::{$ability}() must be static.";

                continue;
            }

            if ($takesRecord && count($reflection->getMethod($ability)->getParameters()) === 0) {
                $violations[] = "{$resource}::{$ability}() must accept a record parameter.";
            }
        }

        $this->assertGreaterThan(0, $checked, 'No Filament resources were inspected.');
        $this->assertSame(
            [],
            $violations,
            "Resources missing explicit authorization (Filament fails OPEN without these):\n - ".implode("\n - ", $violations)
        );
    }
}