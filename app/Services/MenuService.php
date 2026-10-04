<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Menu de la semaine : construit à partir du dernier article WordPress puis mis en cache en base
 */
class MenuService
{
    /** @var int Durée de validité du menu en base avant de reconsulter WordPress (4h) */
    private const int CACHE_VALIDITY_SECONDS = 4 * 60 * 60;

    /** @var string Dossier public des images de menu */
    public const string IMAGE_DIRECTORY = 'assets/IMG';

    /** @var ?Menu Menu déjà résolu pour la requête courante */
    private ?Menu $current = null;

    public function __construct(private readonly WordPressClient $wordPress) {}

    /**
     * Menu de la semaine (depuis le cache s'il est récent, sinon reconstruit depuis WordPress)
     */
    public function current(): Menu
    {
        if ($this->current !== null) {
            return $this->current;
        }

        $menu = Menu::query()->latest('id')->first();
        if ($menu === null || now()->diffInSeconds($menu->modification_date, true) > self::CACHE_VALIDITY_SECONDS) {
            $menu = $this->build();
        }

        if (! $menu->img_src || ! File::exists(public_path(self::IMAGE_DIRECTORY.'/'.$menu->img_src))) {
            $imgSrc = $this->downloadImage($this->wordPress->lastPost());
            if ($imgSrc !== null && ! $menu->img_src) {
                $menu->update(['img_src' => $imgSrc]);
            }
        }

        return $this->current = $menu->load('dishes');
    }

    /**
     * Construit (ou met à jour) le menu en base à partir du dernier article WordPress
     */
    public function build(): Menu
    {
        $post = $this->wordPress->lastPost();
        $menu = Menu::query()->where('creation_date', $post->date())->first();
        $isNewMenu = $menu === null;

        if ($isNewMenu) {
            $menu = Menu::query()->create([
                'img_src' => $this->downloadImage($post),
                'img_figcaption' => $post->firstFigcaption(),
                'is_open' => $post->isMenu(),
                'creation_date' => $post->date(),
            ]);
        }

        // La date de modification sert de cache
        $menu->update(['modification_date' => now()]);

        if ($menu->is_open) {
            $this->syncDishes($menu, $post->dishNames(), $isNewMenu);
        }

        return $menu;
    }

    /**
     * Crée ou met à jour les plats du menu (retrouvés par leur nom)
     *
     * @param  string[]  $dishNames  Noms des plats, dans l'ordre du menu
     * @param  bool  $isNewMenu  Première construction du menu : on incrémente le compteur d'apparitions
     */
    private function syncDishes(Menu $menu, array $dishNames, bool $isNewMenu): void
    {
        $total = count($dishNames);

        DB::transaction(function () use ($menu, $dishNames, $isNewMenu, $total) {
            foreach ($dishNames as $index => $dishName) {
                $attributes = [
                    'menu_id' => $menu->id,
                    'position' => $index,
                    'category' => WordPressPost::categoryForPosition($index, $total),
                ];

                $dish = Dish::query()->where('name', $dishName)->first();
                if ($dish === null) {
                    Dish::query()->create($attributes + [
                        'name' => $dishName,
                        'total' => 1,
                        'creation_date' => $menu->creation_date,
                    ]);
                } else {
                    $dish->update($attributes + ['total' => $isNewMenu ? $dish->total + 1 : $dish->total]);
                }
            }
        });
    }

    /**
     * Télécharge l'image du menu dans public/assets/IMG
     *
     * @return ?string Nom du fichier, null en cas d'échec
     */
    public function downloadImage(WordPressPost $post): ?string
    {
        $imageUrl = $post->firstImageUrl();
        if (! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            Log::error('Image du menu invalide : '.$imageUrl);

            return null;
        }

        $imageName = Str::afterLast(rawurldecode($imageUrl), '/');
        $content = $this->wordPress->download($imageUrl);
        if ($content === null) {
            Log::error("Échec du téléchargement de l'image : ".$imageUrl);

            return null;
        }

        $directory = public_path(self::IMAGE_DIRECTORY);
        File::ensureDirectoryExists($directory);
        if (File::put($directory.'/'.$imageName, $content) === false) {
            Log::error("Échec de l'enregistrement de l'image : ".$imageName);

            return null;
        }

        return $imageName;
    }
}
