<?php

use App\Http\Controllers\NpsResponseController;
use App\Livewire\SurveyResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/survey/{uuid}', SurveyResponse::class)->name('survey.show');

Route::get('/nps/{token}/responder', [NpsResponseController::class, 'score'])->name('nps.respond');
Route::post('/nps/{token}/comentario', [NpsResponseController::class, 'storeComment'])->name('nps.comment');
