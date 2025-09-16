<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Track Pack - Welcome</title>
    @vite('resources/css/welcome.css')



</head>

<body>
    <div class="container">
        <h1>Track Pack</h1>
        <p>Welcome! Please login:</p>

        <button class="btn btn-admin" onclick="window.location.href='{{ route('admin.login') }}'">
            Admin Login
        </button>

        <button class="btn btn-admin" onclick="window.location.href='{{ route('driver.login') }}'">
            Driver Login
        </button>

        <button class="btn btn-customer" onclick="window.location.href='{{ route('customer.login') }}'">
            Customer Login
        </button>

    </div>
</body>

</html>
