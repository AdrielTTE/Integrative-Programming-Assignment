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

            <div class="form-group mt-3">
                <label for="customers">Select Customers (optional):</label>
                <select name="customer_ids[]" id="customers" class="form-control" multiple>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->customer_id }}">
                            {{-- Prefer full name, else fallback gracefully --}}
                            @php
                                $fullName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
                            @endphp

                            {{ $fullName ?: $customer->name ?: 'Customer ' . $customer->customer_id }}
                            @if (!empty($customer->email))
                                ({{ $customer->email }})
                            @endif
                            – ID: {{ $customer->customer_id }}
                        </option>
                    @endforeach
                </select>



            </div>

            <button type="submit" class="btn btn-primary mt-3">Send Announcement</button>
        </form>
    </div>
@endsection
