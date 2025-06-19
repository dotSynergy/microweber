@extends('modules.setupwizard::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('modules.setupwizard.name') !!}</p>
@endsection
