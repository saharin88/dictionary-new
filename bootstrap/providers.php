<?php

use App\Providers\AppServiceProvider;
use App\Providers\DevelopmentServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    DevelopmentServiceProvider::class,
    AdminPanelProvider::class,
];
