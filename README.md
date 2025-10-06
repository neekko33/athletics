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

访问：http://localhost:8000

### 4. 登录系统

使用刚才创建的管理员账号登录

## 📚 功能模块

### ✅ 运动会管理
- 创建/编辑/删除运动会
- 配置比赛日期、每日时间段、跑道数量
- 运动会详情仪表板

### ✅ 比赛项目管理
- 预置 30 个标准项目（径赛 + 田赛）
- 自定义项目（名称、类型、性别、最大人数、平均用时）
- 项目分类展示

### ✅ 年级班级管理
- 添加参赛年级
- 每个年级下管理班级
- 自定义排序

### ✅ 运动员管理
- 手动添加/编辑运动员
- Excel 批量导入
- 自动生成运动员编号
- 下载导入模板

### ✅ 赛事分组
- 径赛自动分组（按年级、跑道数）
- 田赛自动分组
- 接力项目按班级分组
- 手动调整分组和道次

### ✅ 日程安排
- 单个日程创建
- 批量添加日程（自动计算时间间隔）
- 格式化日程查看（按日期/年级/性别分组）
- 冲突检测

### ✅ 秩序册生成
- 一键生成 Word 格式秩序册
- 包含：竞赛日程、班级名单、竞赛分组
- 使用模板文件，支持自定义格式

### ✅ 用户认证
- 安全的登录系统
- 速率限制防暴力破解
- Session 保护
- 记住我功能

## �️ 开发指南

### 项目结构

```
athletics/
├── app/
│   ├── Console/Commands/        # Artisan 命令
│   │   └── CreateAdminUser.php  # 创建管理员
│   ├── Helpers/                 # 辅助类
│   │   └── ChineseHelper.php    # 中文转换工具
│   ├── Http/Controllers/        # 控制器
│   │   ├── AuthController.php
│   │   ├── CompetitionController.php
│   │   ├── EventController.php
│   │   ├── GradeController.php
│   │   ├── AthleteController.php
│   │   ├── HeatController.php
│   │   └── ScheduleController.php
│   ├── Models/                  # Eloquent 模型
│   │   ├── Competition.php
│   │   ├── Event.php
│   │   ├── Grade.php
│   │   ├── Klass.php
│   │   ├── Athlete.php
│   │   ├── CompetitionEvent.php
│   │   ├── AthleteCompetitionEvent.php
│   │   ├── Heat.php
│   │   ├── Lane.php
│   │   ├── LaneAthlete.php
│   │   ├── Schedule.php
│   │   └── Result.php
│   └── Services/                # 业务服务
│       └── ScheduleBookService.php
├── database/
│   ├── migrations/              # 数据库迁移
│   └── seeders/                 # 种子数据
│       └── CompleteDataSeeder.php
├── resources/
│   ├── css/
│   │   └── app.css             # Tailwind CSS
│   ├── js/
│   │   └── app.js
│   └── views/                  # Blade 模板
│       ├── auth/
│       │   └── login.blade.php
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── print.blade.php
│       ├── competitions/
│       ├── events/
│       ├── grades/
│       ├── athletes/
│       ├── heats/
│       └── schedules/
├── routes/
│   └── web.php                 # 路由定义
└── public/
    ├── word_template.docx      # 秩序册模板
    └── images/
```

### 常用命令

```bash
# 创建管理员账号（交互式）
php artisan admin:create

# 创建管理员账号（命令行）
php artisan admin:create --name=管理员 --email=admin@example.com --password=12345678

# 运行迁移
php artisan migrate

# 重置数据库并填充种子数据
php artisan migrate:fresh --seed

# 生成测试数据（135个运动员）
php artisan db:seed --class=CompleteDataSeeder

# 清除缓存
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 技术栈

- **后端**: Laravel 11
- **数据库**: SQLite
- **前端**: Blade + Tailwind CSS + DaisyUI
- **构建工具**: Vite
- **依赖包**:
  - `maatwebsite/excel` - Excel 导入导出
  - `phpoffice/phpword` - Word 文档生成

## 📖 核心功能说明

### 1. 运动员自动编号

按 **年级 → 班级 → 性别（男→女）** 顺序生成编号：
```
001, 002, 003 ...
```

### 2. 径赛自动分组

- 非接力项目：按年级分组，每组最多 N 人（N = 跑道数）
- 接力项目：按班级分组，每队 4 人
- 自动打乱顺序，随机分配道次

### 3. 批量日程安排

- 选择多个分组
- 设置起始时间和间隔
- 自动计算每个分组的开始时间
- 支持冲突检测

### 4. 秩序册生成

使用 Word 模板文件（`public/word_template.docx`），支持以下占位符：

```
${competition_name}     - 运动会名称
${start_date}          - 开始日期
${end_date}            - 结束日期
${generate_date}       - 生成日期
${schedule_content}    - 竞赛日程
${class_roster}        - 班级名单
${heat_groups}         - 竞赛分组
```

## 🔐 安全特性

- ✅ 所有路由需要登录才能访问
- ✅ CSRF 保护
- ✅ 密码加密存储（bcrypt）
- ✅ 速率限制（5次失败后锁定60秒）
- ✅ Session fixation 防护
- ✅ 记住我功能（7天）

## � 注意事项

### Excel 导入格式

CSV 文件需要包含以下列（无表头）：

| 年级 | 班级 | 姓名 | 性别 |
|------|------|------|------|
| 七年级 | 1班 | 张三 | 男 |
| 七年级 | 1班 | 李四 | 女 |

### Word 模板文件

确保 `public/word_template.docx` 存在且格式正确：
- 必须是 `.docx` 格式（不支持 `.doc`）
- 包含所需的占位符变量

## 🐛 故障排除

### 1. PHPWord 报错 "Invalid or uninitialized Zip object"

**原因**: 模板文件是 `.doc` 格式  
**解决**: 用 Word 打开，另存为 `.docx` 格式

### 2. 登录后跳转到登录页

**原因**: Session 配置问题  
**解决**: 
```bash
php artisan config:clear
php artisan cache:clear
```

### 3. Vite 资源加载失败

**原因**: 前端服务未启动  
**解决**: 确保运行 `npm run dev`

### 4. Excel 导入失败

**原因**: 文件格式或列数不匹配  
**解决**: 下载模板文件，按格式填写

## 📊 数据库关系

```
Competition (运动会)
  ├── grades (年级)
  │     └── klasses (班级)
  │           └── athletes (运动员)
  └── competitionEvents (比赛项目关联)
        ├── event (项目)
        ├── athleteCompetitionEvents (报名)
        └── heats (分组)
              ├── lanes (道次)
              │     └── laneAthletes (道次运动员)
              └── schedule (日程)
                    └── results (成绩)
```

## 🎯 典型使用流程

1. **创建运动会** → 设置日期、跑道数
2. **添加年级班级** → 七年级1班、七年级2班...
3. **导入运动员** → Excel 批量导入
4. **生成编号** → 自动按规则编号
5. **添加项目** → 选择参赛项目
6. **运动员报名** → 为项目添加运动员
7. **生成分组** → 自动分组和分配道次
8. **安排日程** → 批量添加日程
9. **生成秩序册** → 一键生成 Word 文档

## � 许可证

MIT License

---

**最后更新**: 2025年10月6日  
**版本**: 1.0.0
