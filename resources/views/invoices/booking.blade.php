@extends('emails.layout')

@section('title', 'Booking Invoice')

@section('content')
<h1 class="email-title">Booking Invoice #{{ $booking->id }}</h1>
<p class="email-text">Thank you for booking <strong>{{ $booking->property->title }}</strong>.</p>
<table style="width:100%;border-collapse:collapse;margin-top:20px;">
  <tr>
    <td>Check in:</td><td>{{ $booking->check_in->format('Y-m-d') }}</td>
  </tr>
  <tr>
    <td>Check out:</td><td>{{ $booking->check_out->format('Y-m-d') }}</td>
  </tr>
  <tr>
    <td>Total:</td><td>{{ number_format($booking->total_amount,2) }} {{ $booking->currency }}</td>
  </tr>
</table>
@endsection
