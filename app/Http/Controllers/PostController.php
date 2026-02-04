<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locale = app()->getLocale();
        $posts = Post::all();

        return view('posts.index', compact('posts', 'locale'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check permission
        if (!auth()->user()->can('create posts')) {
            abort(403, 'Sizda post yaratish ruxsati yo\'q');
        }

        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        $this->authorize('create', Post::class);

        $validated = $request->validate([
            'slug' => 'required|unique:posts',
            'en.title' => 'required|string',
            'en.content' => 'required|string',
            'uz.title' => 'required|string',
            'uz.content' => 'required|string',
            'published' => 'boolean',
        ]);

        // Create post with translatable attributes
        $post = Post::create($validated);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post muvaffaqiyatli yaratildi');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $locale = app()->getLocale();
        $title = $post->getTranslation('title', $locale);
        $content = $post->getTranslation('content', $locale);

        return view('posts.show', compact('post', 'title', 'content', 'locale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        // Check permission
        if (!auth()->user()->can('edit posts')) {
            abort(403, 'Sizda post tahrir qilish ruxsati yo\'q');
        }

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        // Check permission
        $this->authorize('update', $post);

        $validated = $request->validate([
            'slug' => 'required|unique:posts,slug,' . $post->id,
            'en.title' => 'required|string',
            'en.content' => 'required|string',
            'uz.title' => 'required|string',
            'uz.content' => 'required|string',
            'published' => 'boolean',
        ]);

        // Update with translatable attributes
        $post->update($validated);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post muvaffaqiyatli yangilandi');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Check permission
        if (!auth()->user()->can('delete posts')) {
            abort(403, 'Sizda post o\'chirish ruxsati yo\'q');
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post muvaffaqiyatli o\'chirildi');
    }
}
