<?php

use App\Livewire\SurveyResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/survey/{uuid}', SurveyResponse::class)->name('survey.show');
