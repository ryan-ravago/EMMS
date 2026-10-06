@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Something went wrong'))
@section('description', __('We hit an unexpected problem on our side. Please try again, and if it keeps happening, let your administrator know.'))

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">{{ __('Back to home') }}</a>
    <button type="button" class="btn btn-secondary" data-retry>{{ __('Try again') }}</button>
@endsection
