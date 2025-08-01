<?php

namespace Mortezamasumi\FbProfile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string encodeEmail(string $email)
 * @method static string generateRandomCode()
 * @method static string createCode(Model $user)
 *
 * @see \Mortezamasumi\FbProfile\FbProfile
 */
class FbProfile extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mortezamasumi\FbProfile\FbProfile::class;
    }
}
