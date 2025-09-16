{{-- resources/views/layouts/customerLayout.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Package Tracking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: height 0.3s ease;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            height: 80%;
        }
    </style>
</head>

<body class="flex h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Sidebar -->
    <aside class="w-72 bg-white shadow-2xl flex flex-col h-full">
        <!-- Header -->
        <div class="gradient-bg p-6 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-4 right-4 w-20 h-20 bg-white rounded-full opacity-10"></div>
                <div class="absolute bottom-4 left-4 w-16 h-16 bg-white rounded-full opacity-10"></div>
            </div>
            <div class="relative z-10">
                <i class="fas fa-shipping-fast text-3xl mb-2"></i>
                <h1 class="text-2xl font-bold">Track Pack</h1>
                <p class="text-blue-100 text-sm">Professional Package Tracking</p>
            </div>
        </div>

        <!-- Scrollable navigation -->
        <div class="flex-1 overflow-y-auto p-6 space-y-3">
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            
            <a href="{{ route('customer.packages.index') }}"
                class="nav-link {{ request()->routeIs('customer.packages.index') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-box w-5 mr-3"></i>
                <span class="font-medium">Package Management</span>
            </a>

            {{--
            <a href="{{ route('admin.packages.assign') }}"
                class="nav-link {{ request()->routeIs('admin.packages.assign') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-tasks w-5 mr-3"></i>
                <span class="font-medium">Assign Packages</span>
            </a> --}}

            <a href="{{ route('admin.proof.index') }}"
                class="nav-link {{ request()->routeIs('admin.proof.index') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-truck w-5 mr-3"></i>
                <span class="font-medium">Delivery & Proof</span>
            </a>
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('notifications') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-bell w-5 mr-3"></i>
                <span class="font-medium">Notifications</span>
            </a>
            <a href="{{ route('admin.search') }}"
                class="nav-link {{ request()->routeIs('admin.search') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-bell w-5 mr-3"></i>
                <span class="font-medium">Search Package</span>
            </a>
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('packages.history') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-history w-5 mr-3"></i>
                <span class="font-medium">Package History</span>
            </a>
            <a href="{{ route('admin.proof.history') }}"
                class="nav-link {{ request()->routeIs('admin.proof.history') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-history w-5 mr-3"></i>
                <span class="font-medium">Proof History</span>
            </a>
            <a href="{{ route('admin.feedback') }}"
                class="nav-link {{ request()->routeIs('admin.feedback') ? 'active bg-gradient-to-r from-blue-50 to-indigo-50 text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }} flex items-center px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                <i class="fas fa-star w-5 mr-3"></i>
                <span class="font-medium">Feedback & Rating</span>
            </a>
        </div>

        <!-- Bottom: user info + logout -->
        <div class="px-6 py-4 border-t">
            @auth
                <div class="mb-4 p-3 bg-indigo-50 rounded-xl text-center shadow text-indigo-700 text-sm font-medium">
                    <div class="mb-1">Logged in as</div>
                    <div class="text-base font-bold">{{ Auth::user()->user_id }}</div>
                </div>
            @endauth

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-medium rounded-xl shadow-md hover:from-red-600 hover:to-red-700 transition-all duration-300">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-auto">
        @yield('content')
    </main>
</body>

</html>
