@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <h1>Agency Admin Dashboard</h1>
    <p>Welcome, {{ Auth::user()->name }}!</p>
@endsection
