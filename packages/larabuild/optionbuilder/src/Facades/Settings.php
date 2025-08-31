<?php

namespace Larabuild\Optionbuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method bool store($section, $key, $value)
 * @method bool get($key)
 * @method bool getSectionSetting($params, $fields)
 *
 * @see \Optionbuillder\Settings\Settings
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'settings';
    }
}
