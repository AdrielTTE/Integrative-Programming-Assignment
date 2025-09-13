@vite(['resources/css/app.css', 'resources/css/login.css'])

<x-guest-layout>
    <div class="login-card">

        <div class="mb-4">
            <a href="{{ url('/') }}" class="login-link">Back</a>
        </div>
        </br> <!-- Title -->
        <h2 class="login-title">Welcome Back</h2>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" class="login-label" :value="__('Email')" />
                <x-text-input id="email" class="login-input" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" class="login-label" :value="__('Password')" />
                <x-text-input id="password" class="login-input" type="password" name="password" required
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
            </div>
            </br>
            <!-- Remember Me -->
            <div class="flex items-center justify-between">


                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="login-link">
                        Forgot your password?
                    </a>
                @endif
            </div>
            </br>
            <!-- Submit -->
            <div>
                <button type="submit" class="login-button">
                    {{ __('Log in') }}
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Registration link -->
        <p class="text-center text-gray-600">
            Don’t have an account?
            <a href="{{ url('customer/register') }}" class="login-link">
                Create one here
            </a>
        </p>
    </div>
</x-guest-layout>
