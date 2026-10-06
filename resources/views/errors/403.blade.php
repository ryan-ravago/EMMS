@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Access denied'))
@section('description', __("You don't have permission to view this page. If you think this is a mistake, please contact your administrator."))
