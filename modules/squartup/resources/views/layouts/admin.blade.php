@extends('squartup::layouts.master')

@section('squartup-admin-content')
    <div class="space-y-6">
        <div>
            {{ $slot }}
        </div>
    </div>
@endsection
