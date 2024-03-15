<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RequestsExport implements FromView, ShouldAutoSize
{
    protected $requests;

    public function __construct(array $requests)
    {
        $this->requests = $requests;
    }

    public function view(): View
    {
        $view = 'report.requests_report';

        return view($view, [
            'requests' => $this->requests
        ]);
    }
}
