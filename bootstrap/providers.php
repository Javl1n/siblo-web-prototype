<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App::environment('local') ? App\Providers\TelescopeServiceProvider::class : null,
];
