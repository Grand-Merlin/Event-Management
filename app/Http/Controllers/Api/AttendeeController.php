<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Gate;

class AttendeeController extends Controller
{
    use CanLoadRelationships;
    // c'est ici que l'on controle ce qui px etre charger de ce que ne px pas l'etre (apres le ?include dans l'url)
    // que user car c'est la seul relation dans attendee
    private array $relations = ['user'];

        // on applique le middleware dans le constructeur du controlleur pour ne pas devoir traiter chaque route individuellement
        public function __construct()
        {
            $this->middleware('auth:sanctum')->except(['index', 'show']);
            // permet la limitation des requetes api (ici 60 requete par minute)
            // $this->middleware('throttle:60,1')
            // ou de maniere plus generique dans \route\api.php
            $this->middleware('throttle:api')
                ->only(['store', 'destroy']);
            $this->authorizeResource(Attendee::class, 'attendee');
        }

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        /* #region V1 */
        // $attendees = $event->attendees()->latest();

        // //retourne une collection paginée de participant (paginate donne des infos supplementair comme la numero de page, des data supplementaire interessante pour le front end comme react ou vue)
        // return AttendeeResource::collection(
        //     $attendees->paginate()
        // );
        /* #endregion */

        $attendees = $this->loadRelationships($event->attendees()->latest());
        return EventResource::collection(
            $attendees->latest()->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        /* #region V1 */
        // // create = mass assignement (donc ne pas oublier de declarer cela dans le models)
        // // create save directement dans la DB
        // $attendee = $event->attendees()->create([
        //     'user_id' => 1
        // ]);

        // return new AttendeeResource($attendee);
        /* #endregion */

        $attendee = $this->loadRelationships($event->attendees()->create([
            'user_id'=> $request->user()->id
        ]));
        return new AttendeeResource($attendee); // la bonne pratique est de retourner la roussource modifiée

    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        // return new AttendeeResource($attendee);
        return new AttendeeResource($this->loadRelationships($attendee));
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
    // public function destroy(string $event, Attendee $attendee)
    public function destroy(Event $event, Attendee $attendee)
    {
        // $this->authorize('delete-attendee', [$event, $attendee]);// specifique a authorisation, je commente car j'ai utiliser une policy a la place
        $attendee->delete();
        return response(status: 204);
    }
}
