<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // on recuper la table complete des utilisateur, on en selectionne un au hasard et on l'accocie a un evement d-que l'on cree. on fait ça 200 fois
    public function run(): void
    {
        $users = User::all();
        for($i = 0; $i < 200; $i++){
            $user = $users->random();
            \App\Models\Event::factory()->create([
                'user_id' => $user->id
            ]);
        }
    }
}
