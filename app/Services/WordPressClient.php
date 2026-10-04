<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Accès à l'API REST du WordPress qui publie le menu de la semaine
 */
class WordPressClient
{
    public function __construct(private readonly string $baseUrl) {}

    /**
     * Dernier article publié
     *
     * @throws ConnectionException|RequestException
     */
    public function lastPost(): WordPressPost
    {
        $posts = $this->request()
            ->get('/wp-json/wp/v2/posts', ['per_page' => 1, 'order' => 'desc', 'orderby' => 'date'])
            ->throw()
            ->json();

        if (empty($posts[0])) {
            throw new RuntimeException('Aucun article WordPress trouvé');
        }

        return new WordPressPost($posts[0]);
    }

    /**
     * Tous les articles, du plus ancien au plus récent
     *
     * @return WordPressPost[]
     *
     * @throws ConnectionException|RequestException
     */
    public function allPosts(?callable $onPage = null): array
    {
        $posts = [];
        $page = 1;

        do {
            $response = $this->request()
                ->get('/wp-json/wp/v2/posts', ['per_page' => 100, 'order' => 'asc', 'orderby' => 'date', 'page' => $page])
                ->throw();

            $totalPages = (int) ($response->header('X-WP-TotalPages') ?: 1);
            $pagePosts = $response->json() ?? [];
            if ($onPage) {
                $onPage($page, $totalPages, count($pagePosts));
            }

            foreach ($pagePosts as $post) {
                $posts[] = new WordPressPost($post);
            }
            $page++;
        } while (! empty($pagePosts) && $page <= $totalPages);

        return $posts;
    }

    /**
     * Télécharge une image
     *
     * @return ?string Contenu binaire, null en cas d'échec
     */
    public function download(string $url): ?string
    {
        try {
            $response = Http::timeout(10)->withoutVerifying()->get($url);
        } catch (ConnectionException) {
            return null;
        }

        return $response->successful() ? $response->body() : null;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(15)
            ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3')
            ->acceptJson();
    }
}
