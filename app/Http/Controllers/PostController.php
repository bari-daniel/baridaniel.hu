<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    // 🌍 PUBLIC LIST
    public function index()
    {
        return Post::latest()->get();
    }

    // 🌍 PUBLIC SINGLE
    public function show(Post $post)
    {
        if (!$post->published) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($post);
    }

    // 🔐 CREATE
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'published' => 'boolean'
        ]);

        $data['slug'] = Str::slug($data['title']);

        $post = Post::create($data);

        return response()->json($post, 201);
    }

    // 🔐 UPDATE
    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'published' => 'boolean'
        ]);

        $post->update($data);

        return $post;
    }

    // 🔐 DELETE
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    // 🖼️ UPLOAD (EZ HIÁNYZOTT NÁLAD!)
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
}