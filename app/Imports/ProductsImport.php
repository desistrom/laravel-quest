<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToArray;
use DB;
use App\Events\SendMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ProductsImport implements WithStartRow, WithCustomCsvSettings, ToArray
{
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function startRow(): int
    {
        return 2;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'input_encoding' => 'UTF-8'
        ];
    }

    public function array(array $rows)
    {
         Validator::make($rows, [
             '*.0' => 'required'
         ])->validate();
         DB::table('file_uploads')
                        ->where('id', $this->data['id'])
                        ->update(['status' => 'processing']);
        $msg = [];
        $msg['id'] = $this->data['id'];
        $msg['name'] = $this->data['name'];
        $msg['time'] = date('Y-m-d H:i:s');
        $msg['status'] = 'processing';
        SendMessage::dispatch($msg);
        foreach ($rows as $key => $row) {
            $data = explode(',', $row[0]);
            foreach ($data as $key => $value) {
                if (empty($value)) {
                    DB::table('file_uploads')
                        ->where('id', $this->data['id'])
                        ->update(['status' => 'failed']);
                    $msg = [];
                    $msg['id'] = $this->data['id'];
                    $msg['name'] = $this->data['name'];
                    $msg['time'] = date('Y-m-d H:i:s');
                    $msg['status'] = 'failed';
                    SendMessage::dispatch($msg);
                    return 'gagal';
                }
            }
            if(Cache::has('file_'.$this->data['name'].'_'.$data[0])) {
                Redis::del('file_'.$this->data['name'].'_'.$data[0]);
            }
            Cache::add('file_'.$this->data['name'].'_'.$data[0], $row);
        }
        $result = Redis::scan(0, 'match', '*file_'.$this->data['name'].'*');
        print_r($result);
        DB::table('file_uploads')
            ->where('id', $this->data['id'])
            ->update(['status' => 'completed']);
        $msg = [];
        $msg['id'] = $this->data['id'];
        $msg['name'] = $this->data['name'];
        $msg['time'] = date('Y-m-d H:i:s');
        $msg['status'] = 'completed';
        SendMessage::dispatch($msg);
    }
    
} 