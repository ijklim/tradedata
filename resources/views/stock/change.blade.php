@extends('layouts.app')

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
                            'symbol', 
                            $mode == 'edit' ? $item->getKeyValue() : null,
                            [
                                'maxlength' => 5,
                                'placeholder' => 'e.g. QQQ',
                                'required' => 'true'
                            ]
                        );
                        echo Form::bootstrapText(
                            'name',
                            $mode == 'edit' ? $item->name : null,
                            [
                                'maxlength' => 50,
                                'placeholder' => 'e.g. PowerShares QQQ Trust (ETF)',
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