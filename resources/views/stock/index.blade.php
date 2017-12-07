@extends('layouts.app')

@php
    // Declare common variables
    const PAGE_NAME = 'Stocks';
    $itemName = substr(PAGE_NAME, 0, strlen(PAGE_NAME) - 1);
    $items = $stocks ?? null;
@endphp

@section('content')
    <h1 class="col-12 text-center">
        {!! PAGE_NAME !!}
    </h1>
    <!-- Add Button -->
    <button
        class='btn rounded-circle button--bottom-float'
        onclick='window.location = "/stock/create"'
        title='Add {!! $itemName !!}';
    >
        +
    </button>

    @if (isset($items))
        @foreach ($items as $item)
            <div class="col-sm-6 col-md-4 mt-3">
                <div class='card border-dark'>
                    <div class="card-header text-white bg-dark">
                        {{ $item->symbol }}
                    </div>
                    <div class="card-body">
                        <div>{{ $item->name }}</div>
                        <div>Available Prices: {{ $item->stockprices()->get()->count() }}</div>
                    </div>
                    <div class="card-footer">
                        <div class='row'>
                            <div class='col px-1'>
                                <button
                                    class='btn btn-primary btn-block'
                                    onclick='window.location = "/stock-price/{{ $item->symbol }}"'
                                >
                                    Prices
                                </button>
                            </div>
                            <div class='col px-1'>
                            <button
                                class='btn btn-primary btn-block'
                                onclick='window.location = "/stock/{{ $item->symbol }}"'
                            >
                                Show
                            </button>
                            </div>
                            <div class='col px-1'>
                            <button
                                class='btn btn-primary btn-block'
                                onclick='window.location = "/stock/{{ $item->symbol }}/edit"'
                            >
                                Edit
                            </button>
                            </div>
                            <div class='col px-1'>
                            {{
                                Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['stock.destroy', $item->symbol],
                                    'class' => 'd-inline'
                                ])
                            }}
                                {{ Form::submit('Delete', ['class' => 'btn btn-danger btn-block']) }}
                            {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection