<?php

use App\Http\Controllers\RoleController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

// add custom resource routes here
Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
});