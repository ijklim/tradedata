@extends('layouts.app')

@php
    // Declare common fields
    $itemFields = [
        'Domain Name' => 'domain_name',
        'API Base Url' => 'api_base_url'
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
    @if (isset($items))
        @foreach ($items as $item)
            <div class="col-sm-6 mt-3">
                <div class='card border-dark'>
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