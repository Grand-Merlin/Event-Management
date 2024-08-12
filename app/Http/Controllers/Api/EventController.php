<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use \App\Models\Event;
use Illuminate\Database\Eloquent\Relations\Relation;

class EventController extends Controller
{
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
    
        $query = Event::query();
        // c'est ici que l'on controle ce qui px etre charger de ce que ne px pas l'etre (apres le ?include dans l'url)
        $relations = ['user', 'attendees', 'attendees.user'];

        foreach($relations as $relation){
            //when applique une condition a la requete. elle prend deux parametre: une condition, et une callback si true
            $query->when(
                $this->shouldIncludeRelation($relation),
                //$q = requete en cours ($query)
                fn($q)=>$q->with($relation)
            );
        }

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    protected function shouldIncludeRelation(string $relation): bool
        {
            // extraire la valeur du parametre 'include' dans l'URL
            $include = request()->query('include');

            if(!$include){
                return false;
            }
            // la methode explode permet de cree un tableau a partir d'une chaine de caractere avec un delimiteur specifier, dans ce cas, c'est une virgule (,)
            $relations = array_map('trim', explode(',',$include));
            // dd($relations);

            // methode pour voir si relation est bien dans le tableau relations, utiliser pour savoire quelle relation doivent etre incluse dans le traitement de la reponse et lesquelle pas
            return in_array($relation, $relations);
        }

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
            'user_id' => 1
        ]);
        /* #endregion */

        return $event; // la bonne pratique est de retourner la roussource modifiée
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // return $event;

        //permet de charger les relations 'user' et 'attendees' du model
        $event->load('user', 'attendees');
        // permet de renvoyée les ressources sous forme de tableau en passant par EventReource
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event) //route model binding : l'ID de l'objet est trouver automatiquement par laravel grace a la route (ex:/events/5)
    {
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
        return new EventResource($event);
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
