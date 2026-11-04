@extends('auth.layouts.app')

@section('title')
    {{ __('Connect :client', ['client' => $client->name]) }} | {{ config('app.name') }}
@endsection

@section('content')
<div>
    <div class="mb-4 text-center">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('Connect :client?', ['client' => $client->name]) }}
        </h1>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ __('This MCP client is asking to use :app on your behalf.', ['app' => config('app.name')]) }}
        </p>
    </div>

    <div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
        <dl class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
            <div class="flex justify-between gap-3">
                <dt class="text-gray-500 dark:text-gray-400">{{ __('Signed in as') }}</dt>
                <dd class="font-medium break-all">{{ $user->email }}</dd>
            </div>
            <div class="mt-2 flex justify-between gap-3">
                <dt class="text-gray-500 dark:text-gray-400">{{ __('Will return to') }}</dt>
                <dd class="font-mono break-all">{{ $redirectHost }}</dd>
            </div>
        </dl>

        <div>
            <p class="font-medium text-gray-900 dark:text-white">{{ __('It will be able to:') }}</p>
            <ul class="mt-2 flex flex-wrap gap-2" aria-label="{{ __('Granted MCP abilities') }}">
                @foreach ($abilities as $ability)
                    <li class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs dark:bg-gray-800">{{ $ability }}</li>
                @endforeach
            </ul>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Abilities follow your account permissions. You can revoke this connection anytime under Settings → MCP.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('mcp.oauth.authorize.store') }}" class="flex gap-3">
            @csrf
            @foreach ($params as $name => $value)
                @if ($value !== null)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach

            <button type="submit" name="decision" value="deny" class="btn-default w-full">
                {{ __('Deny') }}
            </button>
            <button type="submit" name="decision" value="approve" class="btn-primary w-full">
                {{ __('Allow') }}
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 dark:text-gray-400">
            {{ __('Only allow clients you trust. Never approve a request you did not start.') }}
        </p>
    </div>
</div>
@endsection
