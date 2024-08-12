<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;


// un trait est un morceau de code regroupe plusieur fonction et/ou methodes afin de l'apporter dans differente classe pour pouvoir les reutiliser sans reecrire les meme methode plusieur fois dans les classe differente
trait CanLoadRelationships
{
    public function loadRelationships(Model|EloquentBuilder|QueryBuilder $for, ?array $relations = null) : Model|EloquentBuilder|QueryBuilder {

        $relations = $relations ?? $this->relations ?? [];
        foreach($relations as $relation){
            $for->when(
                $this->shouldIncludeRelation($relation),
                // si for est une instance de model, alors les relation seront chargee avec load, si pas, chargee avec with
                // Model = relation deja chargee (lazy)/ Builder ou querybuilder = requete en cours de construction (eager)
                fn($q)=>$for instanceof Model ? $for->load($relation) : $q->with($relation)
            );
        }
        return $for;
    }

    protected function shouldIncludeRelation(string $relation): bool
        {
            $include = request()->query('include');

            if(!$include){
                return false;
            }
            $relations = array_map('trim', explode(',',$include));

            return in_array($relation, $relations);
        }
}