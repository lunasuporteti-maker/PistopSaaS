@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
