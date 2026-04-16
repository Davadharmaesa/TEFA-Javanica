@extends('layouts.app')

@section('title', 'Beranda - TEFA Javanica')

@section('content')
    @if(Auth::user() && strtolower(Auth::user()->role) === 'operator')
        @include('dashboard.operator')
    @else
        @include('dashboard.admin')
    @endif
@endsection
