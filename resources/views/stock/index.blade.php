@extends('layouts.app')

@section('style')
    .row.equal-height {
        display: flex;
        flex-wrap: wrap;
    }
    .row.equal-height > [class*='col-'] {
        display: flex;
        flex-direction: column;
    }

    .card {
        flex: 1;
    }
@endsection

@section('content')
    <h1 class="col-12 text-center">
        {!! $itemName . 's' !!}
    </h1>
    <!-- Add Button -->
    <button
        class='btn rounded-circle button--bottom-float'
        onclick='window.location = "/{!! $folderName !!}/create"'
        title='Add {!! $itemName !!}'
    >
        +
    </button>

    <div class='col-12'>
    @if (isset($items))
        <div class='row equal-height'>
            @foreach ($items as $item)
                <div class='col-12 col-md-6 col-lg-4 mt-3'>
                    <div class='card'>
                        <div class="card-header text-white bg-dark">
                            {{ $item->getKeyValue() }}
                        </div>
                        <div class="card-body">
                            <div>{{ $item->name }}</div>
                            <div>Data Source: {{ optional($item->dataSource()->first())->domain_name }}</div>
                            <div>Available Prices: {{ $item->stockprices()->get()->count() }}</div>
                        </div>
                        <div class="card-footer">
                            <div class='row'>
                                @php
                                    $buttonWrapperClass = 'col-3 px-1';
                                    $buttonClass = 'btn btn-block p-1';
                                @endphp
                                <!-- Stock prices -->
                                <div class='{{ $buttonWrapperClass }}'>
                                    <button
                                        class='{{ $buttonClass }} btn-primary'
                                        onclick='window.location = "/{!! $folderName !!}/{{ $item->symbol }}"'
                                    >
                                        Prices
                                    </button>
                                </div>

                                <!-- Raw json data -->
                                <div class='{{ $buttonWrapperClass }}'>
                                    <button
                                        class='{{ $buttonClass }}'
                                        onclick='window.location = "/stock-price/{{ $item->symbol }}"'
                                    >
                                        Json
                                    </button>
                                </div>

                                <!-- Edit -->
                                <div class='{{ $buttonWrapperClass }}'>
                                    <button
                                        class='{{ $buttonClass }}'
                                        onclick='window.location = "/{!! $folderName !!}/{!! $item->getKeyValue() !!}/edit"'
                                    >
                                        Edit
                                    </button>
                                </div>

                                <!-- Delete -->
                                <div class='{{ $buttonWrapperClass }}'>
                                    {{
                                        Form::open([
                                            'method' => 'DELETE',
                                            'route' => [$folderName . '.destroy', $item->getKeyValue()],
                                            'class' => 'd-inline'
                                        ])
                                    }}
                                        {{ Form::submit('Delete', ['class' => $buttonClass . ' btn-danger']) }}
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    </div>
@endsection