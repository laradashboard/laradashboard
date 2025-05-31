@if ($errors->any())
    <x-alerts.errors :errors="$errors" />
@endif

@if (Session::has('success'))
    <x-alerts.success :message="Session::get('success')" />
@endif

@if (Session::has('error'))
    <x-alerts.error :message="Session::get('error')" />
@endif