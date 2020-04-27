<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex flex-row">
            <a href="{{ route('users.show', ['name' => $user->name]) }}" class="text-dark">
                <i class="fas fa-user-circle fa-3x"></i>
            </a>
            @if( Auth::id() !== $user->id )
                <div class="ml-auto d-flex flex-row">
                    <div class="mr-3">
                        <a href="{{ route('users.message', ['name' => $user->name]) }}"><i class="fas fa-envelope fa-2x"></i></a>
                    </div>
                    <follow-button
                            :initial-is-followed-by='@json($user->isFollowedBy(Auth::user()))'
                            :authorized='@json(Auth::check())'
                            endpoint="{{ route('users.follow', ['name' => $user->name]) }}"
                    >
                    </follow-button>
                </div>
            @endif
        </div>
        <h2 class="h5 card-title m-0">
            <a href="{{ route('users.show', ['name' => $user->name]) }}" class="text-dark">
                {{ $user->name }}
            </a>
        </h2>
    </div>
    <div class="card-body">
        <div class="card-text">
            <a href="{{ route('users.followings', ['name' => $user->name]) }}" class="text-muted">
                {{ $user->count_followings }} フォロー
            </a>
            <a href="{{ route('users.followers', ['name' => $user->name]) }}" class="text-muted">
                {{ $user->count_followers }} フォロワー
            </a>
        </div>
    </div>
</div>