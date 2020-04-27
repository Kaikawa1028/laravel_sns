@extends('app')

@section('title', $user->name.' Message')

@section('content')
    @include('nav')
    <div class="container">
        <message
                message-url="{{ route('user.message.post', ['name' => $user->name]) }}"
                :initial-messages="{{ $room }}"
                :auth-user="{{ Auth::user() }}"
                :opponent-user="{{ $user }}"
        >

        </message>
    </div>
@endsection