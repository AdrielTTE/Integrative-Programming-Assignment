<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('packages.index') }}" class="text-xl font-bold text-gray-900">
                    Package Management
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('packages.index') }}" class="text-gray-700 hover:text-gray-900">Packages</a>
                <a href="{{ route('packages.dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                <a href="{{ route('packages.track') }}" class="text-gray-700 hover:text-gray-900">Track</a>
            </div>
        </div>
    </div>
</nav>