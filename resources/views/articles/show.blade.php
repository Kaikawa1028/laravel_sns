@extends('app')

@section('title', '記事詳細')

@section('content')
    @include('nav')
    <div class="container">
        @include('articles.card')
        <comment-list
                comment-url="{{ route('articles.comment', ['article' => $article]) }}"
                :is-login='@json(Auth::check())'
        >
        </comment-list>
    </div>
@endsection