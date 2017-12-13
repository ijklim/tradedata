<div class="form-group">
    {{ Form::label($label ?? $name, null) }}
    <select class='custom-select d-block form-control' name='{{ $name }}' id='{{ $name }}'>
        <option value='' {{ $value ? '' : 'selected' }}>Choose...</option>
        @foreach ($options as $option)
            <option value="{{ $option->id }}" {{ $value == $option->id ? 'selected' : '' }}>{{ $option->domain_name }}</option>
        @endforeach
    </select>
    <small class="form-text text-danger">{{ $errors->first($name) }}</small>
</div>