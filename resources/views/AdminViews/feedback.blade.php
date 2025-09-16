@extends('layouts.adminLayout')

@section('content')
    @vite('resources/css/adminDashboard.css')
<table class="feedback-table">
    <thead>
        <tr>
            <th>Feedback ID</th>
            <th>Delivery ID</th>
            <th>Customer ID</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($feedbacks as $feedback)
            <tr>
                <td>{{ $feedback->feedback_id }}</td>
                <td>{{ $feedback->delivery_id }}</td>
                <td>{{ $feedback->customer_id }}</td>
                <td>{{ $feedback->rating }} ⭐</td>
                <td>{{ $feedback->comment }}</td>
                <td>{{ $feedback->created_at->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>



@endsection
