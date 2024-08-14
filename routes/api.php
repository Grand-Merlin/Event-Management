<?php

use App\Http\Controllers\Api\AttendeeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
// Avant que la methode logout soit executée, le middleware verifie si l'utilisateur est bien authentifier.
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::apiResource('events', EventController::class);

// Ressource imbriquée, cela signifie que les participant sont associer a un evemenet specifique (l'URL refletera cette relation perent-enfant)
Route::apiResource('events.attendees', AttendeeController::class)
// le scope permet de rechercher le participant, uniquement parmis les participant d'un evenement specifique
    // ->scoped(['attendee' => 'event']);
// avec les roussource, plus besoin de determiner la relation
// on specifie egalement qu'on ne vx pas utiliser la methode update (on l'a commentéé)
->scoped()->except(['update']);
