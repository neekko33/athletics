# 🐛 赛道UI显示修复

## 问题描述

**现象**：
移除运动员后，虽然运动员数据已经消失，但赛道仍然显示为蓝色背景，而不是空赛道的灰色背景。

**原因**：
视图判断逻辑不正确，只判断了 `$lane` 是否存在，而没有判断 `$lane->laneAthletes` 是否为空。

## 问题代码（修复前）

```php
@php
    $lane = $heat->lanes->firstWhere('lane_number', $i);
@endphp
<div class="p-3 rounded border text-center {{ $lane ? 'bg-primary text-primary-content' : 'bg-base-200' }}">
    <div class="text-xs font-bold">赛道 {{ $i }}</div>
    @if($lane && $lane->laneAthletes->isNotEmpty())
        <div class="text-sm mt-1 truncate" title="{{ $lane->laneAthletes->first()->athlete->name }}">
            {{ $lane->laneAthletes->first()->athlete->name }}
        </div>
    @else
        <div class="text-xs text-gray-500 mt-1">空</div>
    @endif
</div>
```

**问题点**：
- 判断背景色时只用了 `$lane ?`，即使lane没有运动员也会显示蓝色
- 但显示内容时用了 `$lane && $lane->laneAthletes->isNotEmpty()`

**结果**：
- 当lane存在但无运动员时 → 蓝色背景 + "空"文字 ❌

## 修复代码（修复后）

```php
@php
    $lane = $heat->lanes->firstWhere('lane_number', $i);
    $hasAthletes = $lane && $lane->laneAthletes->isNotEmpty();
@endphp
<div class="p-3 rounded border text-center {{ $hasAthletes ? 'bg-primary text-primary-content' : 'bg-base-200 text-gray-600' }}">
    <div class="text-xs font-bold">{{ $isFieldEvent ? '位置' : '赛道' }} {{ $i }}</div>
    @if($hasAthletes)
        @if($isRelay)
            {{-- 接力项目：显示人数 --}}
            <div class="text-sm mt-1">
                {{ $lane->laneAthletes->count() }}/4人
            </div>
        @else
            {{-- 非接力：显示运动员姓名 --}}
            <div class="text-sm mt-1 truncate" title="{{ $lane->laneAthletes->first()->athlete->name }}">
                {{ $lane->laneAthletes->first()->athlete->name }}
            </div>
        @endif
    @else
        <div class="text-xs mt-1">空</div>
    @endif
</div>
```

**改进点**：
1. ✅ 新增变量 `$hasAthletes` 统一判断逻辑
2. ✅ 背景色基于 `$hasAthletes` 判断：有运动员=蓝色，无运动员=灰色
3. ✅ 接力项目特殊处理：显示人数 "X/4人"
4. ✅ 非接力项目：显示运动员姓名
5. ✅ 空赛道文字颜色从 `text-gray-500` 改为 `text-gray-600`（更清晰）

## 验证后端逻辑

确认后端在移除运动员时会正确删除空赛道：

```php
// HeatController.php - removeAthleteFromHeat()
$laneAthlete->delete();

// 如果赛道没有其他运动员了，删除该赛道
if ($lane->laneAthletes()->count() === 0) {
    $lane->delete();  // ✅ 会删除空赛道
}
```

**问题**：虽然后端会删除空的Lane记录，但在某些情况下（如接力项目移除部分运动员），Lane可能仍然存在但运动员变少了。

## 测试场景

### 场景1：普通径赛项目（100米）
1. 初始状态：第1赛道有运动员 → 蓝色背景 + 姓名 ✅
2. 移除运动员后：
   - 后端删除lane记录
   - 前端显示灰色背景 + "空" ✅

### 场景2：接力项目（4×100米）
1. 初始状态：第1赛道有4名运动员 → 蓝色背景 + "4/4人" ✅
2. 移除1名运动员：
   - 后端**不删除**lane记录（因为还有3人）
   - 前端仍显示蓝色背景 + "3/4人" ✅
3. 移除所有4名运动员：
   - 后端删除lane记录
   - 前端显示灰色背景 + "空" ✅

### 场景3：田赛项目（跳远）
1. 初始状态：第1位置有运动员 → 蓝色背景 + 姓名 ✅
2. 移除运动员后：
   - 后端删除lane记录
   - 前端显示灰色背景 + "空" ✅

## UI状态对比

| 状态 | 修复前 | 修复后 |
|------|--------|--------|
| 有运动员 | 🔵 蓝色 + 姓名 | 🔵 蓝色 + 姓名 |
| 无运动员但lane存在 | 🔵 蓝色 + "空" ❌ | ⚪ 灰色 + "空" ✅ |
| 无运动员且lane不存在 | ⚪ 灰色 + "空" | ⚪ 灰色 + "空" |
| 接力项目部分人 | 🔵 蓝色 + 姓名 | 🔵 蓝色 + "X/4人" ✅ |

## 额外优化

### 1. 接力项目显示优化
修复前：只显示第一个运动员姓名（不够清晰）
修复后：显示人数统计 "X/4人"（更直观）

### 2. 文字颜色优化
- 有运动员：白色文字（`text-primary-content`）
- 无运动员：灰色文字（`text-gray-600`）

### 3. 术语统一
- 径赛项目：显示"赛道"
- 田赛项目：显示"位置"

## 文件位置

**修改文件**：
`resources/views/heats/edit.blade.php` (第67-89行)

**修改时间**：
2025年10月5日

**影响范围**：
✅ 分组编辑页面 - 赛道可视化部分
✅ 所有项目类型（径赛、田赛、接力）

---

## 总结

这次修复解决了一个视觉反馈不一致的问题。通过统一判断逻辑 `$hasAthletes`，确保了：

1. **视觉与数据一致**：有运动员=蓝色，无运动员=灰色
2. **接力项目特殊处理**：显示人数而非单个姓名
3. **代码可读性提升**：逻辑更清晰，易于维护

**用户体验提升**：
- ✅ 移除运动员后，空赛道立即变为灰色
- ✅ 接力项目可以看到赛道人数统计
- ✅ 视觉反馈更加准确和及时
