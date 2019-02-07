@extends('layouts.app')

@section('content')
<thread-view inline-template :initial-replies-count="{{ $thread->replies_count }}">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-light">
                        <div class="level">
                            <span class="flex">
                                <a class="font-weight-bold" href="/profiles/{{ $thread->creator->name }}">{{ $thread->creator->name }}</a> publicou:
                                {{ $thread->title }}
                            </span>
                            @can('update', $thread)
                                <form action="{{ $thread->path() }}" method="POST">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                    <button type="submit" class="btn btn-link">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        {{ $thread->body }}
                    </div>
                </div>

                <replies @removed="repliesCount--" @added="repliesCount--"></replies>

            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <p>
                            Essa thread foi publicada {{ $thread->created_at->diffForHumans() }} por 
                            <a href="/profiles/{{ $thread->creator->name }}">{{ $thread->creator->name }}</a> 
                            e atualmente possui <span v-text="repliesCount"></span> {{ str_plural('resposta', $thread->replies_count) }}.
                        </p>
                        <p>
                            <subscribe-button :initial-active="{{ $thread->isSubscribedTo ? 'false' : 'true' }}"></subscribe-button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</thread-view>
<flash message="{{ session('aviso') }}"></flash>
@endsection
