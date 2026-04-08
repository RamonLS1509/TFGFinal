<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'title'          => 'Cyberpunk 2077',
                'description'    => 'Un RPG de acción en mundo abierto ambientado en la megalópolis Night City, donde el poder, el glamour y las modificaciones corporales conviven con la violencia.',
                'developer'      => 'CD Projekt Red',
                'publisher'      => 'CD Projekt',
                'price'          => 39.99,
                'genres'         => ['RPG', 'Action', 'Open World'],
                'platforms'      => ['Windows', 'PlayStation', 'Xbox'],
                'release_date'   => '2020-12-10',
                'metacritic_score' => 86,
                'cover_image'    => 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/1091500/header.jpg',
            ],
            [
                'title'          => 'The Witcher 3: Wild Hunt',
                'description'    => 'Eres Geralt de Rivia, un cazarrecompensas de monstruos. Embárcate en una épica aventura en un mundo de fantasía devastado por la guerra.',
                'developer'      => 'CD Projekt Red',
                'publisher'      => 'CD Projekt',
                'price'          => 9.99,
                'genres'         => ['RPG', 'Action', 'Open World'],
                'platforms'      => ['Windows', 'PlayStation', 'Xbox', 'Nintendo Switch'],
                'release_date'   => '2015-05-19',
                'metacritic_score' => 93,
                'cover_image'    => 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/292030/header.jpg',
            ],
            [
                'title'          => 'Elden Ring',
                'description'    => 'Adéntrate en las Tierras Intermedias y enfréntate a los semidioses en este RPG de acción de mundo abierto creado por FromSoftware y George R.R. Martin.',
                'developer'      => 'FromSoftware',
                'publisher'      => 'Bandai Namco',
                'price'          => 54.99,
                'genres'         => ['RPG', 'Action', 'Souls-like'],
                'platforms'      => ['Windows', 'PlayStation', 'Xbox'],
                'release_date'   => '2022-02-25',
                'metacritic_score' => 95,
                'cover_image'    => 'https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/1245620/header.jpg',
            ],
        ];

        foreach ($games as $game) {
            Game::create($game);
        }
    }
}
