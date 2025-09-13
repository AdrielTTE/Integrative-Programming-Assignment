@extends('layouts.customerLayout')

@section('content')
    @vite('resources/css/notification.css')

    <div class="container">
        <h1>Delivery Notifications</h1>
        <div id="notifications">
            @forelse ($notifications as $notification)
                <div class="notification">
                    <span class="icon">📦</span>
                    <div class="text">
                        <p>{{ $notification->message }}</p>
                        <small>Updated on: {{ $notification->created_at->format('M d, Y \a\t h:i A') }}</small>
                    </div>
                </div>
            @empty
                <p>No notifications yet.</p>
            @endforelse
        </div>
    </div>
@endsection
