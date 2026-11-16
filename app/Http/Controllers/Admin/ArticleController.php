<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function __construct(private ArticleService $articles) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/articles/index', [
            'articles' => $this->articles->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/articles/create', [
            'defaultSortOrder' => $this->articles->nextSortOrder(),
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $this->articles->create(
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Article created successfully.']);

        return redirect()->route('admin.articles.index');
    }

    public function edit(Article $article): Response
    {
        return Inertia::render('admin/articles/edit', [
            'article' => $article,
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $this->articles->update(
            $article,
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Article updated successfully.']);

        return redirect()->route('admin.articles.index');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->articles->delete($article);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Article deleted successfully.']);

        return redirect()->back();
    }
}
