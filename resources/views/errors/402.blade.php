@extends('errors::minimal')

@section('title', __('Payment Required'))
@section('code', '402')
@section('message', __('Payment required'))
@section('description', __("This request can't be completed right now."))
