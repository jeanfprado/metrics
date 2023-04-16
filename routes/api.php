<?php

use Illuminate\Support\Facades\Route;

Route::get('/metrics/{metric}', 'MetricController@show');
