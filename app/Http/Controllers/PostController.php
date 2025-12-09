<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Digunakan untuk mengelola file

class PostController extends Controller
{
    // Get all posts
    public function index()
    {
        $posts = Post::all();

        $posts->each(function ($post) {
            if ($post->image) {
                // Menambahkan URL gambar untuk setiap post
                $post->image_url = url('storage/' . $post->image);
            }
        });

        return response()->json($posts);
    }

    // Create a new post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'article' => 'required',
            // Aturan validasi gambar: opsional, harus berupa gambar, format jpeg/jpg/png, maks 2MB (2048 KB)
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
        ]);

        $data = $request->only(['title', 'author', 'article']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalName();
            // Menyimpan gambar ke folder 'posts' dalam disk 'public'
            $imagePath = $image->storeAs('posts', $imageName, 'public'); 
            $data['image'] = $imagePath;
        }

        $post = Post::create($data);

        if ($post->image) {
            $post->image_url = url('storage/' . $post->image);
        }

        return response()->json($post, 201);
    }

    // Get a single post by ID
    public function show($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        if ($post->image) {
            $post->image_url = url('storage/' . $post->image);
        }

        return response()->json($post);
    }

    // Update a post by ID
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $request->validate([
            'title' => 'string|max:255',
            'author' => 'string|max:255',
            'article' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['title', 'author', 'article']);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            // Simpan gambar baru
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('posts', $imageName, 'public');
            $data['image'] = $imagePath;
        } else {
            // Jika tidak ada gambar baru, pastikan kolom 'image' tidak terhapus jika tidak ada dalam request data
            // (Dalam kode ini, $data hanya berisi title, author, article, jadi kolom 'image' lama tetap utuh kecuali ada gambar baru)
            // Jika Anda ingin mengizinkan penghapusan gambar lama tanpa mengunggah yang baru, logika tambahan diperlukan di sini.
        }

        $post->update($data);

        if ($post->image) {
            $post->image_url = url('storage/' . $post->image);
        }

        return response()->json($post);
    }

    // Delete a post by ID
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Hapus file gambar dari penyimpanan publik
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}