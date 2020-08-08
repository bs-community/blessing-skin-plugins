<?php

Route::get('', 'DescriptionController@read');
Route::put('', 'DescriptionController@update')->middleware('authorize');
