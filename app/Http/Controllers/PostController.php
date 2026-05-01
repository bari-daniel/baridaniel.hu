<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    // 🌍 PUBLIC LIST (lang support)
    public function index(Request $request)
    {
        $lang = $request->query('lang', 'hu');

        return Post::published()
            ->latest()
            ->get()
            ->map(fn($post) => $this->transform($post, $lang));
    }

    // 🌍 PUBLIC SINGLE
    public function show(Request $request, Post $post)
    {
        if (!$post->published && !$request->user('sanctum')) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $lang = $request->query('lang', 'hu');

        return response()->json($this->transform($post, $lang));
    }

    // 🔐 ADMIN LIST
    public function adminIndex()
    {
        return Post::latest()->get();
    }

    // 🔐 CREATE
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'title_en' => 'nullable|string',
            'content' => 'required|string',
            'content_en' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'published' => 'boolean'
        ]);

        $base = Str::slug($data['title']);
        $slug = $base;
        $i = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        $data['slug'] = $slug;

        return Post::create($data);
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'title_en' => 'nullable|string',
            'content' => 'sometimes|string',
            'content_en' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'published' => 'boolean'
        ]);

        if (isset($data['title']) && $data['title'] !== $post->title) {
            $base = Str::slug($data['title']);
            $slug = $base;
            $i = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        $post->update($data);

        return $post;
    }

    // 🔐 DELETE
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // 🖼️ UPLOAD
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120'
        ]);

        $path = $request->file('image')->store('posts', 'public');

        return response()->json([
            'url' => asset("storage/$path")
        ]);
    }

    // 🧠 CENTRAL TRANSLATION LOGIC
    private function transform(Post $post, string $lang)
    {
        $isEn = $lang === 'en';

        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'cover_image' => $post->cover_image,
            'published' => $post->published,
            'created_at' => $post->created_at,

            'title' => $isEn ? ($post->title_en ?? $post->title) : $post->title,
            'excerpt' => $isEn ? ($post->excerpt_en ?? $post->excerpt) : $post->excerpt,
            'content' => $isEn ? ($post->content_en ?? $post->content) : $post->content,
        ];
    }
}