@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @foreach($threads as $thread)
                    
                    <div class="card mb-5">
                        <div class="card-header bg-dark text-light">
                            <div class="level">
                                <h4 class="flex">
                                    <a class="text-light" href="{{ $thread->path() }}">{{ $thread->title }}</a>
                                </h4>
                                <a class="text-light" href="{{ $thread->path() }}">
                                    <strong class="float-right">{{ $thread->replies_count }} {{ str_plural('resposta', $thread->replies_count) }}</strong>
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="body">{{ $thread->body }}</div>
                        </div>
                    </div>

                @endforeach

            </div>
        </div>
    </div>
@endsection
