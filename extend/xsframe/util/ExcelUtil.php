<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\util;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use think\Exception;

class ExcelUtil
{

    /**
     * 从数据库导出数据到表格
     * @param string $title 首行标题内容
     * @param array $column 第二行列头标题
     * @param array $setWidth 第二行列头宽度
     * @param array $list 从数据库获取表格内容
     * @param array $keys 要获取的内容键名
     * @param array $lastRow 最后一行设置
     * @param string $filename 导出的文件名
     * @throws
     */
    public static function export(string $title, array $column, array $setWidth, array $list, array $keys, array $lastRow = [], string $filename = '')
    {
        $spreadsheet = new Spreadsheet();
        $worksheet   = $spreadsheet->getActiveSheet();
        $count       = count($column);
        // 合并首行单元格

        $count2 = 0;
        if ($count > 26) {
            $count2 = $count - 26;
            $count  = 26;
        }

        $end = chr($count + 64);

        if ($count2 > 0) {
            $end = "A" . chr($count2 + 64);
        }

        $worksheet->mergeCells(chr(65) . '1:' . $end . '1');

        $styleArray = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,],
        ];

        // 设置首行单元格内容
        $worksheet->setTitle($title);

        // $worksheet->setCellValueByColumnAndRow(1, 1, $title);
        $worksheet->setCellValue(chr(65) . '1', $title);

        // 设置单元格样式
        // 设置第一行第一个单元格样式
        $worksheet->getStyle(chr(65) . '1')->applyFromArray($styleArray)->getFont()->setSize(18);
        // 设置第二行单元格样式
        $worksheet->getStyle(chr(65) . '2:' . $end . '2')->applyFromArray($styleArray)->getFont()->setSize(12);

        // 设置列头内容
        foreach ($column as $key => $value)
            $worksheet->setCellValueByColumnAndRow($key + 1, 2, $value);
        // 设置列头格式
        foreach ($setWidth as $k => $v) {

            $column = chr($k + 65);

            if ($k > 25) {
                $column = "A" . chr($k - 25 + 65);
            }

            $worksheet->getColumnDimension($column)->setWidth(intval($v));
        }
        // 从数据库获取表格内容
        $len = count($list);
        $j   = 0;
        for ($i = 0; $i < $len; $i++) {
            $j = $i + 3; //从表格第3行开始
            foreach ($keys as $kk => $vv) {
                $worksheet->setCellValueByColumnAndRow($kk + 1, $j, $list[$i][$vv]);
            }
        }
        $total_jzInfo   = $len + 2;
        $styleArrayBody = [
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => '666666'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        // 最后一行计算值
        if (!empty($lastRow)) {
            // 合并最后一行
            $worksheet->mergeCells(chr(65) . ($len + 3) . ':' . chr(count($lastRow) + 64) . ($len + 3));
            foreach ($lastRow as $item) {
                $worksheet->setCellValueByColumnAndRow(array_keys($lastRow, $item)[0], $len + 3, $item);
            }
            $total_jzInfo = $len + 3;
        }
        // 添加所有边框/居中
        $worksheet->getStyle(chr(65) . '1:' . $end . $total_jzInfo)->applyFromArray($styleArrayBody);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition:attachment;filename={$filename}.xlsx");
        header('Cache-Control: max-age=0');//禁止缓存
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    // 读取excel文件
    public static function import($file, $row = 1)
    {
        $fileName = basename($file);
        $fileInfo = pathinfo($fileName);

        // 有Xls和Xlsx格式两种
        if ($fileInfo['extension'] == 'xlsx') {
            $objReader = IOFactory::createReader('Xlsx');
        } else {
            $objReader = IOFactory::createReader('Xls');
        }

        $objReader->setReadDataOnly(TRUE);
        $excel = $objReader->load($file);

        // $sheet              = $excel->getSheet(0);     //excel中的第一张sheet
        $sheet              = $excel->getActiveSheet();
        $highestRow         = $sheet->getHighestRow();      // 取得总行数
        $highestColumn      = $sheet->getHighestColumn();   // 取得总列数
        $highestColumnCount = Coordinate::columnIndexFromString($highestColumn);

        $values = array();

        while ($row <= $highestRow) {
            $rowValue = array();
            $col      = 0;

            while ($col < $highestColumnCount + 1) {
                $rowValue[] = (string)$sheet->getCellByColumnAndRow($col, $row)->getValue();
                ++$col;
            }

            $values[] = $rowValue;
            ++$row;
        }

        return $values;
    }

    /**
     * 从数据库导出数据到表格作为模板文件
     * @param string $title 首行标题内容
     * @param array $column 第二行列头标题
     * @param array $setWidth 第二行列头宽度
     * @param array $list 从数据库获取表格内容
     * @param array $keys 要获取的内容键名
     * @throws
     */
    public static function temp(string $title, array $column, array $setWidth, array $list, array $keys)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet   = $spreadsheet->getActiveSheet();
        $count       = count($column);

        $count2 = 0;
        if ($count > 26) {
            $count2 = $count - 26;
            $count  = 26;
        }

        $end = chr($count + 64);

        if ($count2 > 0) {
            $end = "A" . chr($count2 + 64);
        }

        $styleArray = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,],
        ];

        // 设置单元格样式
        $worksheet->getStyle(chr(65) . '1:' . $end . '1')->applyFromArray($styleArray)->getFont()->setSize(12);

        // 设置列头内容
        foreach ($column as $key => $value)
            $worksheet->setCellValueByColumnAndRow($key + 1, 1, $value);

        // 设置列头格式
        foreach ($setWidth as $k => $v)
            $worksheet->getColumnDimension(chr($k + 65))->setWidth(intval($v));
        // 从数据库获取表格内容
        $len      = count($list);
        $rowStart = 2; // 从表格第2行开始

        $j = 0;
        for ($i = 0; $i < $len; $i++) {
            $j = $i + $rowStart;
            foreach ($keys as $kk => $vv) {
                $worksheet->setCellValueByColumnAndRow($kk + 1, $j, $list[$i][$vv]);
            }
        }

        $total_jzInfo   = $len + 1;
        $styleArrayBody = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // 添加所有边框/居中
        $worksheet->getStyle(chr(65) . '1:' . $end . $total_jzInfo)->applyFromArray($styleArrayBody);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition:attachment;filename={$title}.xlsx");
        header('Cache-Control: max-age=0');//禁止缓存
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }
}