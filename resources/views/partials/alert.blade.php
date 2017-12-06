@if (session()->has('success') || session()->has('error'))
@php
    if (session()->has('success')) {
        $alertType = 'success';
        $alertClass = 'alert-success';
    } else if (session()->has('error')) {
        $alertType = 'error';
        $alertClass = 'alert-danger';
    }
@endphp
<div class='row'>
    <div class="col-12">
        <div class="alert alert-dismissible fade show {!! $alertClass !!}" role="alert">
            {!! session()->get($alertType) !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>
@endif