<?php

namespace App\Helpers;

class ChineseHelper
{
    /**
     * 将阿拉伯数字转换为中文数字
     */
    public static function numberToChinese($number): string
    {
        $chineseNumbers = [
            '0' => '零',
            '1' => '一',
            '2' => '二',
            '3' => '三',
            '4' => '四',
            '5' => '五',
            '6' => '六',
            '7' => '七',
            '8' => '八',
            '9' => '九',
            '10' => '十',
            '11' => '十一',
            '12' => '十二',
        ];

        return $chineseNumbers[(string)$number] ?? (string)$number;
    }

    /**
     * 将班级名称中的数字转换为中文
     * 例如: "1班" -> "一班", "2班" -> "二班"
     */
    public static function classNameToChinese(?string $className): string
    {
        if (!$className) {
            return '';
        }

        // 匹配 "数字班" 格式
        if (preg_match('/^(\d+)班$/', $className, $matches)) {
            $number = $matches[1];
            return self::numberToChinese($number) . '班';
        }

        // 如果已经是中文格式，直接返回
        return $className;
    }
}
