<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = BlogComment::withTrashed()->with(['user', 'post'])->withCount('reports')->latest();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('isi', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('post', function($postQuery) use ($search) {
                      $postQuery->where('judul', 'like', "%{$search}%");
                  });
            });
        }

        $comments = $query->paginate(20)->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogComment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.comments.index')
            ->with('success', 'Komentar berhasil dihapus!');
    }
    public function clear()
    {
        // Hapus reports dulu menghindari foreign key constraint error
        \App\Models\BlogCommentReport::query()->delete();
        BlogComment::query()->delete();

        return redirect()->route('admin.comments.index')
            ->with('success', 'Semua komentar berhasil dihapus secara permanen!');
    }
}
