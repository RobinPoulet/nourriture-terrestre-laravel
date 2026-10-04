<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Carbon;

/**
 * Article WordPress du site Nourriture Terrestre, et extraction du menu qu'il contient
 */
class WordPressPost
{
    private DOMDocument $doc;

    /**
     * @param  array  $post  Article tel que renvoyé par l'API REST WordPress (wp/v2/posts)
     */
    public function __construct(private readonly array $post)
    {
        $this->doc = new DOMDocument;
        $this->doc->loadHTML(
            '<?xml encoding="UTF-8"><div>'.($post['content']['rendered'] ?? '').'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );
    }

    /**
     * Date de publication (Y-m-d)
     */
    public function date(): string
    {
        return Carbon::parse($this->post['date'])->toDateString();
    }

    /**
     * L'article est-il un menu (titre contenant "menu") ?
     */
    public function isMenu(): bool
    {
        return stripos($this->post['title']['rendered'] ?? '', 'menu') !== false;
    }

    /**
     * Noms des plats : contenu des balises <li>
     * (non trimé : les plats existants en base sont retrouvés par leur nom exact)
     *
     * @return string[]
     */
    public function dishNames(): array
    {
        $names = [];
        foreach ($this->doc->getElementsByTagName('li') as $li) {
            if (trim($li->nodeValue) !== '') {
                $names[] = $li->nodeValue;
            }
        }

        return $names;
    }

    /**
     * Attribut src de la première image
     */
    public function firstImageUrl(): string
    {
        return $this->doc->getElementsByTagName('img')->item(0)?->getAttribute('src') ?? '';
    }

    /**
     * Texte de la première légende <figcaption>
     */
    public function firstFigcaption(): string
    {
        return $this->doc->getElementsByTagName('figcaption')->item(0)->nodeValue ?? '';
    }

    /**
     * Catégorie d'un plat déduite de sa position dans le menu :
     * index 0 → entrée, derniers max(1, total - 3) → dessert, reste → plat
     */
    public static function categoryForPosition(int $index, int $total): string
    {
        if ($total <= 2) {
            return 'plat';
        }
        if ($index === 0) {
            return 'entree';
        }

        return $index >= $total - max(1, $total - 3) ? 'dessert' : 'plat';
    }
}
