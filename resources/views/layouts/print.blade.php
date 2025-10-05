<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>比赛日程</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "SimSun", "宋体", serif;
            font-size: 14px;
            line-height: 1.6;
            padding: 20px;
            max-width: 210mm;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: 8px;
        }
        
        .date-section {
            page-break-inside: avoid;
            margin-bottom: 40px;
        }
        
        .date-header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        
        .event-type-section {
            margin-bottom: 30px;
        }
        
        .event-type-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
            letter-spacing: 20px;
            padding-left: 20px;
        }
        
        .schedule-item {
            margin-bottom: 6px;
            padding-left: 20px;
            line-height: 2;
            font-family: "Courier New", "SimSun", monospace;
        }
        
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        
        .no-print button {
            padding: 10px 30px;
            font-size: 16px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .no-print button:hover {
            background-color: #357abd;
        }
        
        /* 班级名单样式 */
        .page-break {
            page-break-before: always;
        }
        
        .grade-section {
            margin-bottom: 30px;
        }
        
        .grade-section h2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .class-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .class-section h3 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .class-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .athletes-list {
            line-height: 2;
            text-align: justify;
        }
        
        .athlete-item {
            display: inline-block;
            width: 19%;
            margin-right: 1%;
            white-space: nowrap;
        }
        
        /* 竞赛分组样式 */
        .competition-group-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .competition-group-section h2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .event-detail {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .event-detail-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .heat-detail {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .heat-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-left: 20px;
        }
        
        .heat-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            margin-left: 20px;
            width: calc(100% - 40px);
        }
        
        .heat-table th,
        .heat-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            font-size: 13px;
        }
        
        .heat-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .heat-table .label-cell {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 60px;
        }
        
        @media print {
            body {
                padding: 10mm;
            }
            
            .no-print {
                display: none;
            }
            
            .date-section {
                page-break-after: always;
            }
            
            .date-section:last-child {
                page-break-after: auto;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .competition-group-section {
                page-break-inside: avoid;
            }
            
            .event-detail {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">打印日程表</button>
        <button onclick="window.close()" style="background-color: #666; margin-left: 10px;">关闭</button>
    </div>
    
    @yield('content')
    
    <div class="no-print" style="margin-top: 30px;">
        <button onclick="window.print()">打印日程表</button>
    </div>
</body>
</html>
