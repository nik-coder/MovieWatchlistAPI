<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-watchlist', function () {
    $this->info('Movie Watchlist API is installed.');
});
