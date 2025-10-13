<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>登录 - 运动会管理系统</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md">
            <!-- Logo 和标题 -->
            <div class="text-center mb-8">
                <div class="inline-block p-3 bg-primary rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">运动会管理系统</h1>
                <p class="text-gray-600">请登录您的账号</p>
            </div>

            <!-- 登录卡片 -->
            <div class="card bg-base-100 shadow-2xl">
                <div class="card-body">
                    <!-- 成功/错误提示 -->
                    @if (session('success'))
                        <div class="alert alert-success mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-error mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <!-- 登录表单 -->
                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <!-- 邮箱 -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">邮箱地址</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="input input-bordered w-full @error('email') input-error @enderror"
                                placeholder="请输入邮箱地址" required autofocus autocomplete="email">
                            @error('email')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- 密码 -->
                        <div class="form-control w-full mt-4">
                            <label class="label">
                                <span class="label-text font-semibold">密码</span>
                            </label>
                            <input type="password" name="password"
                                class="input input-bordered w-full @error('password') input-error @enderror"
                                placeholder="请输入密码" required autocomplete="current-password">
                            @error('password')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        <!-- 记住我 -->
                        <div class="form-control mt-4">
                            <label class="label cursor-pointer justify-start">
                                <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <span class="label-text ml-2">记住我（7天内自动登录）</span>
                            </label>
                        </div>

                        <!-- 登录按钮 -->
                        <div class="form-control mt-6">
                            <button type="submit" class="btn btn-primary w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                登录
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 底部提示 -->
            <div class="text-center mt-6 text-sm text-gray-600">
                <p>© 2025 运动会管理系统 - 安全登录保护</p>
                <p class="mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    连接已加密，数据传输安全
                </p>
                <p> 蜀ICP备2025166165号-1</p>
            </div>
        </div>
    </div>
</body>

</html>
