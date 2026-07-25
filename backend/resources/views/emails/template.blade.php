@extends('emails.layout')

@section('subject', $subjectLine ?? 'VESTRA Notification')

@section('content')
{!! $htmlBody !!}
@endsection
