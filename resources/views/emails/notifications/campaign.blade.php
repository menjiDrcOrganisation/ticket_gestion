@extends('emails.layouts.base')

@section('title', $subjectLine)
@section('heading', $subjectLine)
@if($recipientName)
    @section('greeting', 'Bonjour '.$recipientName.',')
@endif

@section('content')
    <p>{!! nl2br(e($messageBody)) !!}</p>
@endsection
