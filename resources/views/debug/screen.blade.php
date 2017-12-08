<div style='position:fixed;bottom:0px;right:0px;background:none;z-index:9999;'>
@php
    $styles = 'float:left;width:40px;text-align:center;font-size:1rem;line-height:1.3rem;background-color:#';
    $screenSizes = array(
        ['xl', 'fffc4c'],
        ['lg', '7f7d00'],
        ['md', 'fffa00'],
        ['sm', '7f7e26'],
        ['xs', 'ccc800']
    );
@endphp
@foreach ($screenSizes as $screenSize)
    <div
        style='float:left;width:40px;text-align:center;font-size:1rem;line-height:1.3rem;background-color:#{{ $screenSize[1] }}'
        class='{{ $screenSize[0] == 'xs' ? '' : 'd-none' }} d-{{ $screenSize[0] }}-inline'
    >
        {{ $screenSize[0] }}
    </div>
@endforeach
</div>