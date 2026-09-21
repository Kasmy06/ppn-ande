@php $__logoIcone = \App\Models\ParametreApplication::get('logo_path'); @endphp
<link rel="icon" href="{{ $__logoIcone ? \Illuminate\Support\Facades\Storage::disk('public')->url($__logoIcone) : asset('favicon.ico') }}"/>
