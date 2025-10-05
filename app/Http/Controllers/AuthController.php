<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 显示登录表单
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * 处理登录请求
     */
    public function login(Request $request)
    {
        // 验证输入
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => '请输入邮箱地址',
            'email.email' => '请输入有效的邮箱地址',
            'password.required' => '请输入密码',
        ]);

        // 速率限制 - 防止暴力破解
        $throttleKey = strtolower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "登录尝试次数过多，请在 {$seconds} 秒后重试。",
            ]);
        }

        // 尝试登录
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            // 登录成功，清除速率限制
            RateLimiter::clear($throttleKey);

            // 重新生成 session ID 防止 session fixation 攻击
            $request->session()->regenerate();

            // 记录登录时间
            Auth::user()->update([
                'last_login_at' => now(),
            ]);

            return redirect()->intended(route('home'))
                ->with('success', '欢迎回来，' . Auth::user()->name . '！');
        }

        // 登录失败，增加速率限制计数
        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'email' => '邮箱或密码错误。',
        ]);
    }

    /**
     * 处理登出请求
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // 清除 session 数据
        $request->session()->invalidate();

        // 重新生成 CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', '您已安全退出登录。');
    }
}
