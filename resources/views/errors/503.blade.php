@extends('errors::minimal')

{{--
    Shown while the app is in maintenance mode (php artisan down).

    With --render="errors::503" Laravel pre-renders this page when "down" runs, so it is served
    without booting the framework (safe while composer/migrations are running). In that case
    $retryAfter is the --retry value; otherwise it comes from the exception's Retry-After header.
--}}
@php
    $retry = $retryAfter ?? (isset($exception) ? ($exception->getHeaders()['Retry-After'] ?? null) : null);
    $minutes = is_numeric($retry) && $retry > 0 ? (int) ceil($retry / 60) : null;
@endphp

@section('head')
    <meta http-equiv="refresh" content="30">
@endsection

@section('title', __('Under Maintenance'))
@section('code', '503')
@section('message', __('Under maintenance'))
@section('description', __(':app is temporarily unavailable while we install some updates. We\'ll be back shortly, and there is nothing you need to do.', ['app' => config('app.name')]))

@section('extra')
    <dl class="panel">
        <dt><span class="status">{{ __('Maintenance in progress') }}</span></dt>
        <dd>
            @if ($minutes)
                {{ __('Please try again in about :time.', ['time' => $minutes.' '.($minutes === 1 ? 'minute' : 'minutes')]) }}
            @else
                {{ __('Please try again in a few minutes.') }}
            @endif
        </dd>

        <dt>{{ __('Will this page update by itself?') }}</dt>
        <dd>{{ __('Yes. It checks again every 30 seconds and loads the page as soon as we are done.') }}</dd>
    </dl>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary" data-retry>{{ __('Try again') }}</button>
@endsection
