<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '运动会管理系统')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="drawer lg:drawer-open">
        <input id="main-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Main content -->
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            <div class="w-full navbar shadow-sm bg-white fixed top-0 z-10 h-16">
                <div class="px-2 mx-2 mr-6 flex items-center gap-2">
                    <img src="{{ asset('images/icon.png') }}" alt="Logo" class="h-8 w-8">
                    <h1 class="text-xl font-bold">Athletics</h1>
                </div>
                <ul class="flex space-x-4">
                    <li>
                        <a href="{{ route('competitions.index') }}"
                            class="{{ request()->routeIs('competitions.*') ? 'active' : '' }}">运动会管理</a>
                    </li>
                    <li>
                        <a href="{{ route('events.index') }}"
                            class="{{ request()->routeIs('events.*') ? 'active' : '' }}">比赛项目管理</a>
                    </li>
                </ul>
            </div>

            <!-- Page content -->
            <div class="p-4 pt-24 bg-gray-50 min-h-screen">
                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>

</html>
