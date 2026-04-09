<?php

namespace App\Http\Controllers;
use App\Models\BlogPost;
use Illuminate\Http\Request;


class HomeController extends Controller
{
    public function index()
    {
        // Cache data for 10 minutes (600 seconds) 
        // to prevent repetitive DB queries on the homepage
        $posts = \Illuminate\Support\Facades\Cache::remember('home_latest_posts_v2', 600, function () {
            return BlogPost::with(['author', 'category'])
                ->withCount(['comments', 'likes'])
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->take(3)
                ->get();
        });

        return view('index', compact('posts'));
    }

}
