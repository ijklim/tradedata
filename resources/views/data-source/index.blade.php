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

@php
    // Declare common fields
    $itemFields = [
        'Domain Name' => 'domain_name',
        'API Url' => 'api_url'
    ];
@endphp

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
                <div class='col-12 col-lg-6 mt-3'>
                    <div class='card'>
                        <div class="card-header text-white bg-dark">
                            {{ $item->getKeyValue() }}
                        </div>
                        <div class="card-body">
                            @foreach ($itemFields as $itemKey => $itemField)
                                <div>{{ $itemKey . ': ' . $item->$itemField }}</div>
                            @endforeach
                        </div>
                        <div class="card-footer">
                            <div class='row'>
                                @php
                                    $buttonWrapperClass = 'col-6 px-1';
                                    $buttonClass = 'btn btn-block p-1';
                                @endphp
                                <!-- Edit -->
                                <div class='{{ $buttonWrapperClass }}'>
                                    <button
                                        class='{{ $buttonClass }} btn-primary'
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