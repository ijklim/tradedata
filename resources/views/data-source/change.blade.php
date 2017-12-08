@extends('layouts.app')

@php
    // Compute common variables
    $folderName = preg_split("_[\\\\/]_", request()->path())[0];    // e.g. Retrieve `stock` from `stock/RUT/edit`
    $itemName = ucwords(str_replace('-', ' ', $folderName));        // e.g. Stock
    $item = $dataSource ?? null;
@endphp

@section('content')
    <div class="col-sm-10 col-md-8 col-lg-6 mt-3 mx-auto">
        <div class='card border-dark'>
            <div class="card-header">
            {{ $mode == 'create' ? 'New' : 'Edit' }} {!! $itemName !!}
            </div>
            <div class="card-body">
                @php
                    $formRoute = ($mode == 'create' ? [$folderName . '.store'] : [$folderName . '.update', $item->getKeyValue()]);
                    echo Form::open([
                        'route' => $formRoute,
                        'method' => ($mode == 'create' ? 'post' : 'put')
                    ]);
                        echo Form::bootstrapText(
                            'domain_name', 
                            $mode == 'edit' ? $item->domain_name : null,
                            [
                                'placeholder' => 'e.g. iextrading.com',
                                'required' => 'true'
                            ]
                        );
                        echo Form::bootstrapText(
                            'api_base_url',
                            $mode == 'edit' ? $item->api_base_url : null,
                            [
                                'placeholder' => 'e.g. https://api.iextrading.com/1.0/stock/',
                                'required' => 'true'
                            ]
                        );

                        echo Form::submit($mode == 'create' ? 'Add' : 'Save', ['class' => 'btn btn-primary float-right']);
                    echo Form::close();
                @endphp
            </div>
        </div>
    </div>
    
@endsection