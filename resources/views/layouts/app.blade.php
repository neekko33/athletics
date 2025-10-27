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
                <div class="px-2 mx-2 mr-12 flex items-center gap-2">
                    <img src="{{ asset('images/icon.png') }}" alt="Logo" class="h-8 w-8">
                    <h1 class="text-xl font-bold">Athletics</h1>
                </div>
                <ul class="flex space-x-6 h-full flex-1">
                    <li class="h-full">
                        <a href="{{ route('competitions.index') }}"
                            class="{{ request()->routeIs('competitions.*') ? 'font-bold' : '' }} h-full flex items-center justify-center px-2 hover:bg-gray-100 rounded-md">运动会管理</a>
                    </li>
                    <li class="h-full">
                        <a href="{{ route('events.index') }}"
                            class="{{ request()->routeIs('events.*') ? 'font-bold' : '' }} h-full flex items-center justify-center px-2 hover:bg-gray-100 rounded-md">比赛项目管理</a>
                    </li>
                </ul>

                <!-- 用户信息和登出 -->
                <div class="flex items-center gap-4 px-4">
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost">
                            <span class="text-gray-500 text-lg">{{ Auth::user()->name }}</span>
                        </label>
                        <ul tabindex="0"
                            class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52">
                            <li class="disabled">
                                <a class="text-xs text-gray-500">{{ Auth::user()->email }}</a>
                            </li>
                            @if (Auth::user()->last_login_at)
                                <li class="disabled">
                                    <a
                                        class="text-xs text-gray-500">上次登录：{{ Auth::user()->last_login_at->diffForHumans() }}</a>
                                </li>
                            @endif
                            <div class="divider my-1"></div>
                            <li>
                                <form action="{{ route('logout') }}" method="POST"
                                    class="w-full flex items-center justify-center">
                                    @csrf
                                    <button type="submit" class="w-full cursor-pointer text-error font-bold">
                                        退出登录
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
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

                @if (session('warning'))
                    <div class="alert alert-warning mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>

</html>
