@if(flash()->message)
<div>
    <div class="alert d-flex align-items-center alert-{{ flash()->class?? 'success' }}" role="alert">
       
    @if(flash()->class == 'danger' || flash()->class == 'warning')
        <i class="bi-exclamation-triangle-fill me-2"></i>
        {{ flash()->message }}
    @endif

    @if(flash()->class == 'success')
        <i class="bi-check-circle-fill me-2"></i>
        {{ flash()->message }}
    @endif

    @if(flash()->class == 'info')
        <i class="bi-info-circle-fill me-2"></i>
        {{ flash()->message }}
    @endif

</div>
@endif