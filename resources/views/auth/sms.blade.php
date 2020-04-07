@extends('app')

@section('title', 'SMS認証')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a class="text-dark" href="/">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">SMS認証</h2>

                        @include('error_card_list')

                        <div class="card-text">
                            <form method="POST" action="{{ route('verify') }}">
                                @csrf

                                <div class="md-form">
                                    <label for="token">送信された7桁の番号を入力してください。</label>
                                    <input class="form-control" type="text" id="token" name="token" required value="{{ old('token') }}">
                                    <input type="hidden" id="authy_id" name="authy_id" value="{{ $authy_id }}" />
                                </div>

                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">送信</button>

                            </form>

                            <div class="mt-0">
                                <a href="{{ route('register') }}" class="card-text">ユーザー登録はこちら</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection