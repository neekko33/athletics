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

    public static function chineseClassNameToNumber(?string $className): string
    {
        if (!$className) {
            return '';
        }

        $chineseToNumber = [
            '零' => '0',
            '一' => '1',
            '二' => '2',
            '三' => '3',
            '四' => '4',
            '五' => '5',
            '六' => '6',
            '七' => '7',
            '八' => '8',
            '九' => '9',
            '十' => '10',
            '十一' => '11',
            '十二' => '12',
        ];

        // 匹配 "中文数字班" 格式
        if (preg_match('/^([\x{4e00}-\x{9fa5}]+)班$/u', $className, $matches)) {
            $chineseNumber = $matches[1];
            return $chineseToNumber[$chineseNumber] ?? $className;
        }

        // 如果已经是数字格式，直接返回
        return $className;
    }
}
