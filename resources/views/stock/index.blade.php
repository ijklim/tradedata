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
                        <div>{{ $stock->name }}</div>
                        <div>Available Data: {{ $stock->stockprices()->get()->count() }}</div>

                        <div class='mt-2 text-center'>
                            <button
                                class='btn btn-primary'
                                onclick='window.location = "/stock-price/{{ $stock->symbol }}"'
                            >
                                Open
                            </button>
                            <button
                                class='btn btn-primary'
                                onclick='window.location = "/stock/{{ $stock->symbol }}/edit"'
                            >
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection