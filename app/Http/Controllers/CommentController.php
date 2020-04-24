<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use App\Comment;

class CommentController extends Controller
{
    public function index(Article $article)
    {
        $comments = Comment::with('user')->where('article_id', $article->id)->get();

        return $comments->toJson();
    }

    public function store(Request $request, Article $article)
    {
        $article->comments()->attach(
            ['user_id' => $request->user()->id],
            ['text' => $request->text]
        );

        $comments = Comment::with('user')->where('article_id', $article->id)->get();

        return $comments->toJson();
    }

}
