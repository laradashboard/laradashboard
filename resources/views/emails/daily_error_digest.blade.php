<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Daily Error Digest') }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; background: #f9fafb; margin: 0; padding: 24px;">
    <div style="max-width: 720px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px;">
        <h1 style="font-size: 18px; margin: 0 0 8px;">
            {{ __(':app — Error digest', ['app' => config('app.name', 'Application')]) }}
        </h1>
        <p style="margin: 0 0 16px; color: #4b5563;">
            {{ __(':count new unique error(s) seen in the last :hours hours.', ['count' => $errors->count(), 'hours' => $windowHours]) }}
        </p>

        @foreach ($errors as $error)
            <div style="border-top: 1px solid #e5e7eb; padding: 16px 0;">
                <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: #b91c1c; font-weight: 600;">
                    {{ $error->level }}
                </div>
                <div style="font-size: 14px; margin: 4px 0 8px; word-break: break-word;">
                    {{ \Illuminate\Support\Str::limit($error->message, 400) }}
                </div>
                <div style="font-size: 12px; color: #6b7280;">
                    @if ($error->file)
                        <div><strong>{{ __('Location') }}:</strong> {{ $error->file }}@if ($error->line):{{ $error->line }}@endif</div>
                    @endif
                    <div>
                        <strong>{{ __('Occurrences') }}:</strong> {{ $error->occurrences }}
                        &nbsp;·&nbsp;
                        <strong>{{ __('First seen') }}:</strong> {{ $error->first_seen_at?->toDateTimeString() }}
                        &nbsp;·&nbsp;
                        <strong>{{ __('Last seen') }}:</strong> {{ $error->last_seen_at?->toDateTimeString() }}
                    </div>
                </div>
            </div>
        @endforeach

        <p style="margin: 24px 0 0; font-size: 12px; color: #9ca3af;">
            {{ __('You are receiving this because Error Notifications are enabled. Each unique error is reported once; later occurrences only update the counter.') }}
        </p>
    </div>
</body>
</html>
