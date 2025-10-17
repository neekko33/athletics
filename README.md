# 🏃 运动会管理系统

一个基于 Laravel + DaisyUI 的现代化运动会管理系统，支持运动员管理、赛事分组、日程安排、秩序册生成等完整功能。

## ⚡ 快速开始

### 1. 环境要求

- PHP >= 8.2
- Composer
- Node.js >= 18
- SQLite

### 2. 安装项目

```bash
# 克隆项目
cd athletics

# 安装 PHP 依赖
composer install

# 安装前端依赖
npm install

# 配置环境
cp .env.example .env
php artisan key:generate

# 运行迁移和种子数据
php artisan migrate
php artisan db:seed

# 创建管理员账号
php artisan admin:create
```

### 3. 启动开发服务器

```bash
# 终端1：启动 Laravel 服务
php artisan serve

# 终端2：启动 Vite 前端构建
npm run dev
```
