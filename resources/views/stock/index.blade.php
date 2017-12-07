@extends('layouts.app')

@section('content')
    @if (isset($stocks))
        @foreach ($stocks as $stock)
            <div class="col-sm-6 col-md-4 mt-3">
                <div class='card border-dark'>
                    <div class="card-header text-white bg-dark">
                        {{ $stock->symbol }}
                    </div>
                    <div class="card-body">
                        <div>{{ $stock->name }}</div>
                        <div>Available Prices: {{ $stock->stockprices()->get()->count() }}</div>

                        <div class='mt-2 text-center'>
                            <button
                                class='btn btn-primary'
                                onclick='window.location = "/stock-price/{{ $stock->symbol }}"'
                            >
                                Prices
                            </button>
                            <button
                                class='btn btn-primary'
                                onclick='window.location = "/stock/{{ $stock->symbol }}"'
                            >
                                Show
                            </button>
                            <button
                                class='btn btn-primary'
                                onclick='window.location = "/stock/{{ $stock->symbol }}/edit"'
                            >
                                Edit
                            </button>
                            {{
                                Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['stock.destroy', $stock->symbol],
                                    'class' => 'd-inline'
                                ])
                            }}
                                {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection