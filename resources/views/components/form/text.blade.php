<div class="form-group">
    {{ Form::label($name, null) }}
    {{ Form::text($name, $value, array_merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')], $attributes ?? [])) }}
    <small class="form-text text-danger">{{ $errors->first($name) }}</small>
</div>