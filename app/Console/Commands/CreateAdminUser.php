<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--name= : 管理员姓名}
                            {--email= : 管理员邮箱}
                            {--password= : 管理员密码}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建管理员账号（用于生产环境）';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==================================');
        $this->info('   创建运动会管理系统管理员账号   ');
        $this->info('==================================');
        $this->newLine();

        // 获取或询问姓名
        $name = $this->option('name') ?: $this->ask('请输入管理员姓名');

        // 获取或询问邮箱
        $email = $this->option('email') ?: $this->ask('请输入管理员邮箱');

        // 验证邮箱格式
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ], [
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '该邮箱已被使用',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());
            return Command::FAILURE;
        }

        // 获取或询问密码
        $password = $this->option('password') ?: $this->secret('请输入管理员密码（至少8位）');

        // 验证密码长度
        if (strlen($password) < 8) {
            $this->error('密码长度至少为8位！');
            return Command::FAILURE;
        }

        // 确认密码（仅在交互模式下）
        if (!$this->option('password')) {
            $passwordConfirm = $this->secret('请再次输入密码确认');

            if ($password !== $passwordConfirm) {
                $this->error('两次输入的密码不一致！');
                return Command::FAILURE;
            }
        }

        // 确认创建
        $this->newLine();
        $this->table(
            ['字段', '值'],
            [
                ['姓名', $name],
                ['邮箱', $email],
                ['密码', str_repeat('*', strlen($password))],
            ]
        );

        if (!$this->confirm('确认创建以上管理员账号？', true)) {
            $this->warn('操作已取消');
            return Command::SUCCESS;
        }

        // 创建用户
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->newLine();
            $this->info('✓ 管理员账号创建成功！');
            $this->newLine();
            $this->line("用户ID: {$user->id}");
            $this->line("姓名: {$user->name}");
            $this->line("邮箱: {$user->email}");
            $this->newLine();
            $this->comment('现在可以使用该账号登录系统了。');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('创建失败：' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

