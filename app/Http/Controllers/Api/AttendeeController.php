<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $attendees = $event->attendees()->latest();

        //retourne une collection paginée de participant (paginate donne des infos supplementair comme la numero de page, des data supplementaire interessante pour le front end comme react ou vue)
        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        // create = mass assignement (donc ne pas oublier de declarer cela dans le models)
        // create save directement dans la DB
        $attendee = $event->attendees()->create([
            'user_id' => 1
        ]);

        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($attendee);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    // L'ordre des attributs dans la signature de la méthode doit correspondre à l'ordre des segments dynamiques dans l'URL (/events/{event}/attendees/{attendee})
    public function destroy(string $event, Attendee $attendee)
    {
        $attendee->delete();
        return response(status:204);
    }
}
