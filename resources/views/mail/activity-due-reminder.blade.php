<x-mail::message>
# {{ $headingLine }}

{{ __('Subject: :subject', ['subject' => $activity->subject]) }}

@if($activity->due_at)
{{ __('Due: :when', ['when' => $activity->due_at->timezone(config('app.timezone'))->format(config('app.datetime_format'))]) }}
@endif

<x-mail::button :url="$viewUrl">
{{ __('Open activity') }}
</x-mail::button>

{{ __('Thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>
