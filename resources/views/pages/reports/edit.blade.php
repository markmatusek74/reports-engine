@extends('reporting-engine::layout')

@section('title', 'Edit Report')

@section('content')
    <livewire:report-builder :report="$report" />
@endsection