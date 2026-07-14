<?php

use App\Livewire\SurveyResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/survey/{uuid}', SurveyResponse::class)->name('survey.show');
