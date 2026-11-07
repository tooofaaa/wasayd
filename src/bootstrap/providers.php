<?php

return [
    App\Providers\AppServiceProvider::class,
    // HorizonServiceProvider disabled — requires Redis which is not running
    // App\Providers\HorizonServiceProvider::class,
    // TelescopeServiceProvider only runs in local env (APP_ENV=production skips it)
    // App\Providers\TelescopeServiceProvider::class,
];
