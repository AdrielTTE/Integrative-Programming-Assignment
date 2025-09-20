@extends('layouts.customerLayout')

@section('content')
    @vite('resources/css/notification.css')

    <div class="container">
        <h1>Notifications</h1>
        </br>
        <div id="notifications">
            @forelse ($notifications as $notification)
                <div class="notification">

                    <div class="text">
                        <p>{{ $notification['message'] }}</p>
                        <small>
                            {{ \Carbon\Carbon::parse($notification['created_at'])->format('M d, Y \a\t h:i A') }}
                        </small>
                    </div>

                    <!-- ✅ Clear Button -->
                    <div class="actions">
                        <form method="POST"
                            action="{{ route('customer.notifications.updateReadAt', $notification['notification_id']) }}">
                            @csrf
                            <button type="submit" class="btn-clear">Clear</button>
                        </form>

                    </div>

                </div>
            @empty
                <p>No notifications yet.</p>
            @endforelse
        </div>
    </div>
@endsection
