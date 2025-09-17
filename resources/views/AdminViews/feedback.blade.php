@extends('layouts.adminLayout')

@section('content')
    @vite('resources/css/feedback.css')


    <div class="feedback-section">
        <h2>Customer Feedback</h2>

        <!-- Rating Filter -->
        <form method="GET" action="{{ route('admin.feedback') }}" class="mb-4 filter-group">
            <label for="rating">Filter by Rating:</label>
            <select name="rating" id="rating" onchange="this.form.submit()">
                <option value="all" {{ request('rating') === null || request('rating') === 'all' ? 'selected' : '' }}>All
                </option>
                <option value="5" {{ request('rating') === '5' ? 'selected' : '' }}>5</option>
                <option value="4" {{ request('rating') === '4' ? 'selected' : '' }}>4</option>
                <option value="3" {{ request('rating') === '3' ? 'selected' : '' }}>3</option>
                <option value="2" {{ request('rating') === '2' ? 'selected' : '' }}>2</option>
                <option value="1" {{ request('rating') === '1' ? 'selected' : '' }}>1</option>
                <option value="0" {{ request('rating') === '0' ? 'selected' : '' }}>0</option>
            </select>

            <label for="category">Filter by Category:</label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="all"
                    {{ request('category') === null || request('category') === 'all' ? 'selected' : '' }}>All</option>
                <option value="Delivery" {{ request('category') === 'Delivery' ? 'selected' : '' }}>Delivery</option>
                <option value="Package" {{ request('category') === 'Package' ? 'selected' : '' }}>Package</option>
                <option value="Driver" {{ request('category') === 'Driver' ? 'selected' : '' }}>Driver</option>
            </select>
        </form>


        <table class="feedback-table">
            <thead>
                <tr>
                    <th>Feedback ID</th>
                    <th>Delivery ID</th>
                    <th>Customer ID</th>
                    <th>Category</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($feedbacks as $feedback)
                    <tr>
                        <td>{{ $feedback['feedback_id'] }}</td>
                        <td>{{ $feedback['delivery_id'] }}</td>
                        <td>{{ $feedback['customer_id'] }}</td>
                        <td>{{ $feedback['category'] }}</td>
                        <td>{{ $feedback['rating'] }}</td>
                        <td class="comment">{{ $feedback['comment'] }}</td>
                        <td class="date">{{ \Carbon\Carbon::parse($feedback['created_at'])->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 1rem; color: #6b7280;">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $feedbacks->appends(['rating' => request('rating')])->links() }}
        </div>
    </div>
@endsection
