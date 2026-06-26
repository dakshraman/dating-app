<?php

namespace App\Services;

use Laravel\Reverb\Contracts\Connection;

class ConnectionTracker
{
    protected static array $userConnections = [];

    protected static array $connectionToUser = [];

    public static function setUserConnection(int $userId, Connection $connection): void
    {
        self::$userConnections[$userId] = $connection;
        self::$connectionToUser[$connection->id()] = $userId;
    }

    public static function removeUserConnection(Connection $connection): void
    {
        $connectionId = $connection->id();
        if (isset(self::$connectionToUser[$connectionId])) {
            $userId = self::$connectionToUser[$connectionId];
            unset(self::$userConnections[$userId]);
            unset(self::$connectionToUser[$connectionId]);
        }
    }

    public static function removeUserByUserId(int $userId): void
    {
        if (isset(self::$userConnections[$userId])) {
            $connection = self::$userConnections[$userId];
            unset(self::$connectionToUser[$connection->id()]);
            unset(self::$userConnections[$userId]);
        }
    }

    public static function getConnection(int $userId): ?Connection
    {
        return self::$userConnections[$userId] ?? null;
    }

    public static function hasUser(int $userId): bool
    {
        return isset(self::$userConnections[$userId]);
    }

    public static function getAllUserIds(): array
    {
        return array_keys(self::$userConnections);
    }
}
