@extends('layouts.app')

@section('content')
<div class="col-12 text-center">
    <h1>{{ config('app.name') }}</h1>
    <h6>v{{ config('app.version') }}</h6>
</div>
@endsection