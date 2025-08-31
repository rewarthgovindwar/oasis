<?php

namespace App\Enums;

class MenuType
{
    public const IFRAME = 'iframe';

    public const URL = 'url';

    public const CONTENT = 'content';

    public static function getValues(): array
    {
        return [
            self::IFRAME,
            self::URL,
            self::CONTENT,
        ];
    }
}
