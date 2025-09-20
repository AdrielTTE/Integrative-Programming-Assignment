@extends('layouts.adminLayout')

@section('content')
    <div class="container">
        <h1>Send Announcement</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.announcement.send') }}">
            @csrf
            <div class="form-group">
                <label for="message">Announcement Message:</label>
                <textarea name="message" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Send Announcement</button>
        </form>
    </div>
@endsection
