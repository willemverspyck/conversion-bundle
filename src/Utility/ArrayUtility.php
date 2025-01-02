<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Utility;

use Exception;

final class ArrayUtility
{
    public static function hasKeysInArray(array $keys, array $data): bool
    {
        foreach ($keys as $key) {
            if (false === array_key_exists($key, $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public static function hasKeysInArrayWithException(array $keys, array $data): void
    {
        if (false === self::hasKeysInArray($keys, $data)) {
            throw new Exception(sprintf('Field not found (%s)', implode(', ', $keys)));
        }
    }
}
