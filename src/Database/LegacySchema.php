<?php

declare(strict_types=1);

namespace Engelsystem\Database;

final class LegacySchema
{
    public static function enabled(): bool
    {
        return app()->bound('config') && (bool) config('legacy_database_schema');
    }

    public static function permission(string $permission): ?string
    {
        return match ($permission) {
            'angeltypes.view' => 'angeltypes',
            'angeltypes.edit' => 'admin_angel_types',
            'user.angeltypes.edit' => 'admin_user_angeltypes',
            'angeltypes.goodie.list' => 'angeltype.goodie.list',
            'angeltypes.join' => 'user_angeltypes',
            'user.info.hint' => 'admin_arrive',
            'tag.edit', 'plugin.edit', 'logs.links' => null,
            default => $permission,
        };
    }
}
