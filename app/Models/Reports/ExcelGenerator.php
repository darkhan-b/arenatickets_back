<?php

namespace App\Models\Reports;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelGenerator {

    public $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function generateFromCollection() {
        $writer = WriterEntityFactory::createXLSXWriter();
        $filename = uniqid().'_'.Config::get('clinic_id', 0).'.xlsx';
        $writer->openToFile(storage_path('app/excel/'.$filename));
        if(count($this->data) > 0) {
            $first = $this->data[0];
            $class_name = get_class($first);
//            $selectable = isset($class_name::$data_table_select) ? explode(',',preg_replace('/\s+/', '', $class_name::$data_table_select)) : null;
            $original_keys = array_keys($first->getAttributes());
//            if(isset($class_name::$data_table_excel_fields)) {
//                $selectable = $class_name::$data_table_excel_fields;
//                $original_keys = $class_name::$data_table_excel_fields;
//            }
            $keys = array_map('trans', $original_keys);
            $writer->addRow(WriterEntityFactory::createRowFromArray($keys));
            foreach($this->data as $d) {
//                if(isset($class_name::$data_table_excel_resource)) {
//                    $resource = $class_name::$data_table_excel_resource;
//                    $d = new $resource($d);
//                }
                $arr = $d->toArray();
//                if($selectable) {
//                    $tmp_arr = [];
//                    foreach($original_keys as $key) {
//                        if(in_array($key, $selectable)) $tmp_arr[$key] = $arr[$key] ?? '';
//                    }
//                    $arr = $tmp_arr;
//                }
                $arrcleaned = array_map(function($value) {
                    if($value === NULL) { $value = ''; }
                    if(is_array($value)) { $value = ''; }
                    return $value;
                }, $arr);
                $arrcleaned = array_values($arrcleaned);
                $writer->addRow(WriterEntityFactory::createRowFromArray($arrcleaned));
            }
        }
        $writer->close();
        return $filename;
    }


    public function generateFromManualArray() {
        $writer = WriterEntityFactory::createXLSXWriter();
        $filename = uniqid().'.xlsx';
        $writer->openToFile(storage_path('app/excel/'.$filename));
        foreach($this->data as $d) {
            $writer->addRow(WriterEntityFactory::createRowFromArray($d));
        }
        $writer->close();
        return $filename;
    }

    public static function cleanOldExcelFiles() {
        $files = Storage::files('public/reports');
        foreach($files as $file) {
            $timestamp = Storage::lastModified($file);
            if ($timestamp < (time() - 5*60)) {
                Storage::delete($file);
            }
        }
    }

}
