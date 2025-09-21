<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Customer;

class CustomerAuthController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('CustomerViews.auth.register');
    }

     public function showLoginForm()
{
    return view('CustomerViews.auth.login'); // Make sure this Blade file exists
}

public function showRegisterForm()
{
    return view('CustomerViews.auth.register'); // Make sure this Blade file exists
}

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

         $lastCustomer = User::where('user_id', 'like', 'C%')
    ->orderByRaw('CAST(SUBSTRING(user_id, 3) AS UNSIGNED) DESC')
    ->first();

// Start from next number (or 1 if none found)
$nextId = $lastCustomer
    ? ((int)substr($lastCustomer->user_id, 2)) + 1
    : 1;

// Loop to make sure the new ID is unique
do {
    $newId = 'C' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    $exists = User::where('user_id', $newId)->exists();
    $nextId++;
} while ($exists);




        try {
    $user = User::create([
        'user_id' => $newId,
        'username' => $request->username,
        'email' => $request->email,
        'phone_number' => $request->phone,
        'password' => Hash::make($request->password),

    ]);

    $customer = Customer::create([
            'customer_id' => $newId,
            'first_name' => $request->username,
            'last_name'=> $request->username,
            'address' => ''
        ]);


    if (!$user) {
        dd('User creation returned null');
    }


} catch (\Exception $e) {
    dd('DB error: ' . $e->getMessage());
}

        event(new Registered($user));

         Auth::login($user);

        return redirect()->route('customer.dashboard');

    }

    public function login(Request $request): RedirectResponse
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();


    if (!$user || !str_starts_with($user->user_id, 'C')) {
        return back()->withErrors([
            'email' => 'You are not authorized to access the customer system.',
        ]);
    }

    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'email' => 'Invalid login credentials.',
        ]);
    }

    session()->forget('url.intended');

    Auth::login($user);

    return redirect()->route('customer.dashboard');
}


}


