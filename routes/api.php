<?php

use App\Http\Controllers\Api\AttendeeController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('events', EventController::class);

// Ressource imbriquée, cela signifie que les participant sont associer a un evemenet specifique (l'URL refletera cette relation perent-enfant)
Route::apiResource('events.atendees', AttendeeController::class)
// le scope permet de rechercher le participant, uniquement parmis les participant d'un evenement specifique
    ->scoped(['attendee' => 'event']);
