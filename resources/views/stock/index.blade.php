@extends('layouts.app')

@php
    // Compute common variables
    $folderName = request()->path();                            // e.g. data-source/1
    $folderName = preg_split("_[\\\\/]_", $folderName)[0];      // e.g. data-source
    $itemName = ucwords(str_replace('-', ' ', $folderName));    // e.g. Data Source
    $pageName = $itemName . 's';                                // e.g. Data Sources
@endphp

@section('content')
    <h1 class="col-12 text-center">
        {!! $pageName !!}
    </h1>
    <!-- Add Button -->
    <button
        class='btn rounded-circle button--bottom-float'
        onclick='window.location = "/{!! $folderName !!}/create"'
        title='Add {!! $itemName !!}';
    >
        +
    </button>
    @if (isset($items))
        @foreach ($items as $item)
            <div class="col-sm-6 col-md-4 mt-3">
                <div class='card border-dark'>
                    <div class="card-header text-white bg-dark">
                        {{ $item->getKeyValue() }}
                    </div>
                    <div class="card-body">
                        <div>{{ $item->name }}</div>
                        <div>Available Prices: {{ $item->stockprices()->get()->count() }}</div>
                    </div>
                    <div class="card-footer">
                        <div class='row'>
                            <!-- Prices -->
                            <div class='col px-1'>
                                <button
                                    class='btn btn-primary btn-block'
                                    onclick='window.location = "/stock-price/{{ $item->symbol }}"'
                                >
                                    Prices
                                </button>
                            </div>

                            <!-- Single stock only -->
                            <div class='col px-1'>
                            <button
                                class='btn btn-primary btn-block'
                                onclick='window.location = "/{!! $folderName !!}/{{ $item->symbol }}"'
                            >
                                Show
                            </button>
                            </div>

                            <!-- Edit -->
                            <div class='col px-1'>
                            <button
                                class='btn btn-primary btn-block'
                                onclick='window.location = "/{!! $folderName !!}/{!! $item->getKeyValue() !!}/edit"'
                            >
                                Edit
                            </button>
                            </div>

                            <!-- Delete -->
                            <div class='col px-1'>
                            {{
                                Form::open([
                                    'method' => 'DELETE',
                                    'route' => [$folderName . '.destroy', $item->getKeyValue()],
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