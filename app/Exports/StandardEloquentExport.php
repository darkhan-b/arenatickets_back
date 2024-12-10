<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StandardEloquentExport implements FromArray, WithHeadings {

    protected $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function array(): array {
        return $this->data;
    }

    public function headings(): array {
        $firstRow = ($this->data)[0] ?? [];
        return array_keys($firstRow);
    }
}
