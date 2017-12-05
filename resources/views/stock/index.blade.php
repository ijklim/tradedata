@extends('layouts.app')

@section('content')
    @if (isset($stocks))
        @foreach ($stocks as $stock)
            <div class="col-sm-6 mt-3">
                <div class='card border-dark'>
                    <div class="card-header text-white bg-dark">
                        {{ $stock->symbol }}
                    </div>
                    <div class="card-body">
                        {{ $stock->name }}
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection