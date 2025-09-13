@vite(['resources/css/app.css', 'resources/css/login.css'])
<x-guest-layout>
    <div class="login-card">
        <div class="mb-4">
            <a href="{{ url('/') }}" class="login-link">Back</a>
        </div>
        </br>
        <!-- Title -->
        <h2 class="login-title">Create Account</h2>

        </br>


        <form method="POST" action="{{ route('admin.register.submit') }}" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="username" class="login-label" :value="__('Name')" />
                <x-text-input id="username" class="login-input" type="text" name="username" :value="old('username')"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('username')" class="mt-2 text-sm text-red-600" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" class="login-label" :value="__('Email')" />
                <x-text-input id="email" class="login-input" type="email" name="email" :value="old('email')"
                    required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
            </div>

            <!-- Phone -->
            <div>
                <x-input-label for="phone" class="login-label" :value="__('Phone')" />
                <x-text-input id="phone" class="login-input" type="tel" name="phone" :value="old('phone')"
                    required autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2 text-sm text-red-600" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" class="login-label" :value="__('Password')" />
                <x-text-input id="password" class="login-input" type="password" name="password" required
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" class="login-label" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="login-input" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600" />
            </div>
            </br>
            <!-- Submit -->
            <div>
                <button type="submit" class="login-button">
                    {{ __('Register') }}
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Already have an account -->
        <p class="text-center text-gray-600">
            Already registered?
            <a href="{{ route('login') }}" class="login-link">
                Log in
            </a>
        </p>
    </div>
</x-guest-layout>
