<?php

declare(strict_types=1);

namespace App\Support;

use App\Modules\ModuleRegistry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

final class AuditMorphs
{
    /** @return class-string<Model>|null */
    public static function model(?string $type): ?string
    {
        $map = app(ModuleRegistry::class)->morphMap();
        $class = Relation::getMorphedModel($type ?? '');
        if ($class !== null && in_array($class, $map, true)) {
            return $class;
        }

        return ! Relation::requiresMorphMap() && in_array($type, $map, true) ? $type : null;
    }

    public static function label(?string $type): string
    {
        $class = self::model($type);

        return $class === null ? 'Unavailable record' : Str::headline(class_basename($class));
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  list<string>  $relations
     * @return Collection<int, Activity>
     */
    public static function load(Collection $activities, array $relations): Collection
    {
        foreach ($relations as $relation) {
            $supported = $activities->filter(function (Activity $activity) use ($relation): bool {
                if (self::model($activity->getAttribute("{$relation}_type")) !== null) {
                    return true;
                }

                $activity->setRelation($relation, null);

                return false;
            });
            $supported->load($relation);
        }

        return $activities;
    }
}
