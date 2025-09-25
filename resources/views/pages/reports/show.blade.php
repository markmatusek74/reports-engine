@extends('reporting-engine::layout')

@section('title', $report->name)

@section('content')
    <livewire:report-viewer :report="$report" />
@endsection