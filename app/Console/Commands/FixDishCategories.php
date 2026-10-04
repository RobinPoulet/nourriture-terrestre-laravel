<?php

namespace App\Console\Commands;

use App\Models\Dish;
use App\Services\WordPressClient;
use App\Services\WordPressPost;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Régularise positions et catégories des plats à partir de tous les menus publiés sur WordPress
 * (remplace scripts/fix_dish_categories.php)
 */
#[Signature('dishes:fix-categories {--dry-run : Affiche les changements sans écrire en base}')]
#[Description('Régularise les positions et catégories des plats depuis l\'historique WordPress')]
class FixDishCategories extends Command
{
    public function handle(WordPressClient $wordPress): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('[MODE DRY-RUN — aucune écriture en base]');
        }

        $this->line('Récupération des posts WordPress…');
        $posts = $wordPress->allPosts(fn ($page, $totalPages, $count) => $this->line("  Page $page/$totalPages — $count articles"));
        $menuPosts = array_values(array_filter($posts, fn (WordPressPost $post) => $post->isMenu()));
        $this->newLine();
        $this->info(count($menuPosts).' menus trouvés sur '.count($posts).' articles.');

        $counts = ['updated' => 0, 'skipped' => 0, 'notFound' => 0];

        foreach ($menuPosts as $post) {
            $dishNames = $post->dishNames();
            if (empty($dishNames)) {
                $this->line("[{$post->date()}] Aucun plat trouvé — ignoré");

                continue;
            }

            $total = count($dishNames);
            $this->line("[{$post->date()}] $total plat(s) : ".implode(' | ', array_map('trim', $dishNames)));

            foreach ($dishNames as $index => $dishName) {
                $category = WordPressPost::categoryForPosition($index, $total);
                $dish = Dish::query()->where('name', $dishName)->first();

                if (! $dish) {
                    $this->line("  [$index] \"".trim($dishName).'" → non trouvé en base');
                    $counts['notFound']++;

                    continue;
                }

                $previous = "pos=$dish->position cat=$dish->category";
                $changed = $dish->position !== $index || $dish->category !== $category;
                if ($changed && ! $dryRun) {
                    $dish->update(['position' => $index, 'category' => $category]);
                }

                $tag = $changed ? ($dryRun ? '[dry]' : '✓') : '—';
                $this->line("  [$index] \"".trim($dishName)."\" → $category  $tag".($changed ? " (était $previous)" : ''));
                $counts[$changed ? 'updated' : 'skipped']++;
            }
            $this->newLine();
        }

        $this->info('=== Terminé ===');
        $this->line("  Mis à jour : {$counts['updated']}");
        $this->line("  Inchangés  : {$counts['skipped']}");
        $this->line("  Non trouvés: {$counts['notFound']}");

        return self::SUCCESS;
    }
}
