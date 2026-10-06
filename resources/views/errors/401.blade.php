@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Sign in required'))
@section('description', __('You need to sign in to view this page.'))
