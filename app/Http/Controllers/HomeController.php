<?php

namespace App\Http\Controllers;
use App\Models\BlogPost;
use Illuminate\Http\Request;


class HomeController extends Controller
{
    public function index()
    {
        $posts = BlogPost::latest('published_at')->take(3)->get();
        return view('index', compact('posts'));
    }

}
