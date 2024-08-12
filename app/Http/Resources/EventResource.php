<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // ici on determine qu'elle propriete on désire retourner, les autres, seront ignorées
        // c'est ici que l'on choisi les attribut que l'on souhaite caché
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'description'=>$this->description,
            'start_time'=>$this->start_time, // ici est un bon endroit pour retournée des date formatée comme on le souhaite
            'end_time'=>$this->end_time,
            // ici whenLoaded('user') est le nom de la relation definie dans le model
            // whenloaded verifie si la relation a été chargée avec le model avec la methode with()
            'user' => new UserResource($this->whenLoaded('user')),
            'attendees' => AttendeeResource::collection(
                $this->whenLoaded('attendees')
            )
        ];
    }
}
