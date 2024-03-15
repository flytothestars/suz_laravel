<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ReportExport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    protected $rows;
    protected $type;

    public function __construct(array $rows, $type)
    {
        $this->rows = $rows;
        $this->type = $type;
    }

    public function view(): View
    {
        $view = 'report.report_1';
        file_put_contents('/var/www/html/storage/logs/export_type.txt', $this->type); //TODO Хардкод
        if($this->type == 2)
        {
            $view = 'report.report_2';
        }
        elseif($this->type == 3)
        {
            $view = 'report.consolidated_1';
        }
        elseif($this->type == 4)
        {
            $view = 'report.consolidated_2';
        }
        elseif($this->type == 5)
        {
            $view = 'report.balance_1';
        }
        elseif($this->type == 6)
        {
            $view = 'report.balance_2';
        }
    	return view($view, [
            'rows' => $this->rows
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER
        ];
    }
}
