# 🚀 快速启动指南

## 立即测试现有功能

```bash
# 1. 启动服务器
cd /Users/neekko33/Developer/playground/athletics
php artisan serve

# 2. 新终端窗口启动前端
npm run dev

# 3. 访问
open http://localhost:8000
```

## 可以立即使用的功能

1. **运动会列表** - http://localhost:8000
   - 查看所有运动会
   - 创建新运动会
   - 编辑运动会信息
   - 删除运动会

2. **运动会详情** - 创建后点击"查看详情"
   - 统计数据仪表板
   - 年级、运动员数量统计
   - 功能导航卡片

3. **运动项目** - http://localhost:8000/events
   - 查看30个预置项目
   - 径赛和田赛分类

## 数据库状态

✅ 已迁移并填充种子数据：
- 12个表结构
- 30个运动项目

## 测试建议

1. 创建一个运动会
2. 查看详情页面
3. 尝试编辑和删除

## 下一步开发

查看 README.md 了解完整的实现指南。

重点实现：
1. AthleteController - 运动员管理
2. HeatController - 自动分组算法
3. ScheduleController - 日程管理

