@extends('errors::minimal')

@php
    $retry = isset($exception) ? ($exception->getHeaders()['Retry-After'] ?? null) : null;
@endphp

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Too many requests'))
@section('description')
    {{ __("You're doing that a bit too fast. Please wait a moment and try again.") }}
    @if (is_numeric($retry))
        {{ __('You can try again in :seconds seconds.', ['seconds' => (int) $retry]) }}
    @endif
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">{{ __('Back to home') }}</a>
    <button type="button" class="btn btn-secondary" data-retry>{{ __('Try again') }}</button>
@endsection
