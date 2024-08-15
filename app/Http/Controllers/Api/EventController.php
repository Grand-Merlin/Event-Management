<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // depuis laravel 11 la classe de base 'controler' n'herite plus de \Illuminate\Routing\Controller (il faut donc l'adapter nous meme)
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use Illuminate\Http\Request;
use \App\Models\Event;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    // import du trait
    use CanLoadRelationships;
    // c'est ici que l'on controle ce qui px etre charger de ce que ne px pas l'etre (apres le ?include dans l'url)
    private array $relations = ['user', 'attendees', 'attendees.user'];

    // on applique le middleware dans le constructeur du controlleur pour ne pas devoir traiter chaque route individuellement
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        // permet la limitation des requetes api (ici 60 requete par minute)
        // $this->middleware('throttle:60,1')
        // ou de maniere plus generique dans \route\api.php
        $this->middleware('throttle:api')
        ->only(['store', 'update', 'destroy']);
        $this->authorizeResource(Event::class, 'event');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /* #region V1 */
        // On px retourner directement les models depuis les actions dans le controleur (index, show, etc...)
        // laravel s'occupe de la serialization (json, xml, etc...)(serialization = transformation d'un object en format de stockage, typiquemen json ou xml)
        // return \App\Models\Event::all();
        /* #endregion */


        /* #region V2 sans charger les relation */
        // return EventResource::collection(Event::all());
        /* #endregion */

        /* #region V3 en chargant la relation 'user' du model (permet le whenLoaded) */
        // return EventResource::collection(Event::with('user')->get());
        /* #endregion */


        /* #region V4 en utilisant paginate au lieu de get */
        // $this->shouldIncludeRelation('user');
        // return EventResource::collection(Event::with('user')->paginate());
        /* #endregion */

        /* #region cette partie est declarée au dessou du use */
        // c'est ici que l'on controle ce qui px etre charger de ce que ne px pas l'etre (apres le ?include dans l'url)
        // $relations = ['user', 'attendees', 'attendees.user'];
        /* #endregion */

        // $query = $this->loadRelationships(Event::query(), $relations);

        // grace au trait, ceci est mtn la seul action a faire dans chaque endroit on l'on vx charger les relation
        $query = $this->loadRelationships(Event::query());

        /* #region ce code se trouve desormais dans le trait */
        // foreach ($relations as $relation) {
        //     //when applique une condition a la requete. elle prend deux parametre: une condition, et une callback si true
        //     $query->when(
        //         $this->shouldIncludeRelation($relation),
        //         //$q = requete en cours ($query)
        //         fn($q) => $q->with($relation)
        //     );
        // }
        /* #endregion */

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /* #region Cette methode à été deplacée dans le trait (CanLoadRelationships) */
    // protected function shouldIncludeRelation(string $relation): bool
    // {
    //     // extraire la valeur du parametre 'include' dans l'URL
    //     $include = request()->query('include');

    //     if (!$include) {
    //         return false;
    //     }
    //     // la methode explode permet de cree un tableau a partir d'une chaine de caractere avec un delimiteur specifier, dans ce cas, c'est une virgule (,)
    //     $relations = array_map('trim', explode(',', $include));
    //     // dd($relations);

    //     // methode pour voir si relation est bien dans le tableau relations, utiliser pour savoire quelle relation doivent etre incluse dans le traitement de la reponse et lesquelle pas
    //     return in_array($relation, $relations);
    // }
    /* #endregion */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /* #region V1 */
        // validation des données coté serveur (nullable = pas obligatoire)
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        //     'strat_time' => 'required|date',
        //     'end_time' => 'required|date|after:start_time'
        // ]);
        /* #endregion */

        /* #region V2 */
        $event = Event::create([
            // le splat operator (...) permet de "déplier" chaque champs validé (par la methode validate) soit un element a part entiere du tableau, celui qui sera utiliser pour creer l'objet event
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            // 'user_id' => 1
            'user_id' => $request->user()->id
        ]);
        /* #endregion */

        return new EventResource($this->loadRelationships($event)); // la bonne pratique est de retourner la roussource modifiée
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        /* #region V1 */
        // // return $event;

        // //permet de charger les relations 'user' et 'attendees' du model
        // $event->load('user', 'attendees');
        // // permet de renvoyée les ressources sous forme de tableau en passant par EventReource
        // return new EventResource($event);
        /* #endregion */

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event) //route model binding : l'ID de l'objet est trouver automatiquement par laravel grace a la route (ex:/events/5)
    {
        /* #region V1 avec gate */
        // if (Gate::denies('update-event', $event)) {
        //     abort(403, 'Vous n\'est pas autorisé a modifier cette évenement');
        // }
        /* #endregion */

        /* #region V1 plus concis */
        // Gate::authorize('update-event', $event);
        // $this->authorize('update-event', $event);
        // plus necessaire car nous avons ajouter $this->authorizeResource(Event::class, 'event'); au constructeur
        /* #endregion */

        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
                //sometimes signifie que le champ spécifié n'est requis que si il est présent dans la requête
                // lorsqu'on met a jour un objet, il n'est plus necessaire de specifier tous les attribut obligatoirement(on px en garder certain deja cree avec la methode store)
            ])
        );
        // return $event; // la bonne pratique est de retourner la roussource modifiée

        // permet de renvoyée les ressources sous forme de tableau en passant par EventReource
        // return new EventResource($event);
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        /* #region V1 */
        // on ne retourne plus la ressource, vu qu'elle a été supprimée, mais on retourne un message de succes.
        // return response()->json([
        //     'message' => 'L\'evenement a bien été supprimé'
        // ]);
        /* #endregion */

        // le code 204 sert a signifier au client que la ressource a bien été supprimée sans contenus a lui retourner
        return response(status: 204);
    }
}
