# 运动会管理系统 - Laravel版本

这是athletics-app (Ruby on Rails)的完整Laravel复刻版本。

## 项目概述

本系统是一个功能完整的学校运动会管理平台，支持从运动员报名到赛事安排的全流程管理。

## 已完成的核心架构

### ✅ 1. 数据库结构（100%完成）

所有数据表已创建并迁移成功：

- `competitions` - 运动会基本信息
- `events` - 运动项目（径赛/田赛）
- `grades` - 年级
- `klasses` - 班级
- `athletes` - 运动员
- `competition_events` - 运动会与项目关联表
- `athlete_competition_events` - 运动员报名表
- `heats` - 分组信息
- `lanes` - 赛道
- `lane_athletes` - 赛道运动员关联
- `schedules` - 日程安排
- `results` - 比赛成绩

### ✅ 2. 模型层（100%完成）

所有12个Model已创建并配置：

1. **Competition** - 完整的关联关系和业务方法
2. **Event** - 项目类型判断（径赛/田赛/接力）
3. **Grade** - 年级管理，全局排序
4. **Klass** - 班级管理，包含full_name属性
5. **Athlete** - 运动员，包含编号生成逻辑
6. **CompetitionEvent** - 运动会项目关联
7. **AthleteCompetitionEvent** - 运动员报名
8. **Heat** - 分组，支持径赛和田赛
9. **Lane** - 赛道，支持接力项目
10. **LaneAthlete** - 赛道运动员，支持棒次
11. **Schedule** - 日程，包含冲突检测
12. **Result** - 成绩记录

### ✅ 3. 种子数据（100%完成）

已创建30个运动项目：
- 男子径赛：8项（100米、200米、400米、800米、1500米、110米栏、4×100米接力、4×400米接力）
- 女子径赛：8项（100米、200米、400米、800米、1500米、100米栏、4×100米接力、4×400米接力）
- 男子田赛：6项（跳高、跳远、三级跳远、铅球、铁饼、标枪）
- 女子田赛：6项（跳高、跳远、三级跳远、铅球、铁饼、标枪）

### ✅ 4. 路由配置（100%完成）

所有RESTful路由已配置：
- 运动会CRUD
- 年级管理
- 运动员管理（含导入导出）
- 分组管理（含自动分组）
- 日程管理（含批量创建）

### ✅ 5. 依赖包（100%完成）

- Laravel 12.x
- maatwebsite/excel - Excel导入导出
- Tailwind CSS + DaisyUI (已安装)

## 需要完成的部分

### 📝 控制器实现（30%完成）

#### ✅ 已完成
- CompetitionController - 基本CRUD

#### 🔲 待完成
1. **GradeController** - 年级管理
2. **AthleteController** - 运动员管理核心
   - index() - 运动员列表
   - create/store() - 添加运动员
   - edit/update() - 编辑运动员
   - destroy() - 删除运动员
   - **generateNumbers()** - 自动生成编号（重要）
   - **import()** - Excel批量导入（重要）
   - **downloadTemplate()** - 下载导入模板

3. **HeatController** - 分组管理核心
   - index() - 分组列表
   - show() - 分组详情
   - edit/update() - 编辑分组
   - **generateAll()** - 自动生成径赛分组（核心算法）
   - **generateFieldEvents()** - 生成田赛分组
   - 运动员调整功能

4. **ScheduleController** - 日程管理
   - index() - 日程列表
   - create/store() - 创建日程
   - edit/update() - 编辑日程
   - **bulkNew()** - 批量创建页面
   - **bulkCreate()** - 批量创建日程
   - **print()** - 打印日程表

5. **EventController** - 项目管理

### 🎨 视图层（0%完成）

需要创建所有Blade视图文件：

#### 布局文件
- `resources/views/layouts/app.blade.php` - 主布局（使用DaisyUI）

#### Competition视图
- `index.blade.php` - 运动会列表
- `create.blade.php` - 创建运动会
- `edit.blade.php` - 编辑运动会
- `show.blade.php` - 运动会详情（仪表板）

#### Grade视图
- `index.blade.php` - 年级管理

#### Athlete视图
- `index.blade.php` - 运动员列表
- `create.blade.php` - 添加运动员
- `edit.blade.php` - 编辑运动员

#### Heat视图
- `index.blade.php` - 分组列表
- `show.blade.php` - 分组详情
- `edit.blade.php` - 编辑分组

#### Schedule视图
- `index.blade.php` - 日程列表
- `create.blade.php` - 创建日程
- `edit.blade.php` - 编辑日程
- `bulk-new.blade.php` - 批量创建
- `print.blade.php` - 打印版

## 核心算法实现要点

### 1. 运动员编号生成算法
```php
// 按年级 → 班级 → 性别(男→女)排序后生成001开始的编号
$athletes = $competition->grades()
    ->with('klasses.athletes')
    ->orderBy('order')
    ->get()
    ->flatMap(function($grade) {
        return $grade->klasses->flatMap(function($klass) {
            return $klass->athletes->sortBy(function($athlete) {
                return $athlete->gender === '男' ? 0 : 1;
            });
        });
    });

$athletes->each(function($athlete, $index) {
    $athlete->update(['number' => sprintf('%03d', $index + 1)]);
});
```

### 2. 径赛自动分组算法
```php
// 非接力项目：按年级分组，每组最多track_lanes人，随机打乱
// 接力项目：按年级→班级分组，每队4人
```

### 3. Excel导入实现
```php
use Maatwebsite\Excel\Facades\Excel;

// 读取Excel文件
$data = Excel::toArray(new AthletesImport, $request->file('file'));

// 逐行处理：
// - 查找/创建年级
// - 查找/创建班级
// - 创建运动员
// - 关联报名项目
```

## 快速开始

### 1. 安装依赖
```bash
composer install
npm install
```

### 2. 配置数据库
```bash
cp .env.example .env
php artisan key:generate
```

编辑 `.env` 文件，配置数据库连接

### 3. 运行迁移和种子
```bash
php artisan migrate
php artisan db:seed
```

### 4. 启动开发服务器
```bash
php artisan serve
npm run dev
```

访问 http://localhost:8000

## 下一步实现建议

### 优先级1（核心功能）
1. 完成AthleteController的import()和generateNumbers()
2. 完成HeatController的generateAll()
3. 创建Competition的show视图（仪表板）

### 优先级2（基础页面）
1. 完成所有CRUD视图
2. 实现运动员列表和编辑
3. 实现分组查看

### 优先级3（高级功能）
1. 日程管理
2. 批量操作
3. 打印功能

## 原项目对照

- 原项目（Rails）：`/Users/neekko33/Developer/playground/athletics-app`
- 本项目（Laravel）：`/Users/neekko33/Developer/playground/athletics`

## 技术栈对比

| 功能 | Rails版本 | Laravel版本 |
|------|----------|------------|
| 框架 | Rails 8.0 | Laravel 12.x |
| 数据库 | SQLite3 | SQLite/MySQL |
| 前端 | Tailwind + DaisyUI | Tailwind + DaisyUI |
| Excel | Roo gem | maatwebsite/excel |
| 模板 | ERB | Blade |

## 开发进度

- [x] 数据库设计和迁移
- [x] 模型层实现
- [x] 种子数据
- [x] 路由配置
- [x] CompetitionController基础实现
- [ ] 其他控制器实现
- [ ] 视图层实现
- [ ] Excel导入导出
- [ ] 自动分组算法
- [ ] 日程管理

## 联系方式

如有问题，请查看原Rails项目的实现代码作为参考。

---

**项目状态**：基础架构已完成，核心业务逻辑待实现  
**预计完成时间**：需要额外8-12小时完成所有控制器和视图  
**当前可运行**：✅ 数据库已就绪，可以开始实现业务逻辑
