<?php

namespace App\Exports;

use Illuminate\Http\Response;

class ExcelSpreadsheet
{
    public static function download(string $filename, array $headings, iterable $rows): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            .'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Data"><Table>';

        $xml .= self::row($headings);

        foreach ($rows as $row) {
            $xml .= self::row($row);
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private static function row(array $row): string
    {
        $xml = '<Row>';

        foreach ($row as $value) {
            $type = is_int($value) || is_float($value) ? 'Number' : 'String';
            $value = self::escape((string) ($value ?? ''));
            $xml .= '<Cell><Data ss:Type="'.$type.'">'.$value.'</Data></Cell>';
        }

        return $xml.'</Row>';
    }

    private static function escape(string $value): string
    {
        $value = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
