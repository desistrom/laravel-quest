<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use DB;
use Illuminate\Http\Request;
use App\Models\FileUpload;
use App\Jobs\ProcessCsv;

class ProgressBarController extends Controller
{
    public function index()
    {
        $file = DB::select('select * from file_uploads');
        return view('uploadfile', ['dataFile' => $file ]);
    }

    public function t()
    {
        // broadcast(new \App\Events\SendMessage('hehe'));
        return 'test';
    }
 
    public function uploadToServer(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv',
        ]);
 
        $name = request()->file->getClientOriginalName();
        $fileExist = DB::table('file_uploads')
                ->where('name', '=', $name)
                ->first();
        // print_r($fileExist->id);
        if ($fileExist) {
            DB::table('file_uploads')
            ->where('id', $fileExist->id)
            ->update(['upload_at' => date('Y-m-d H:i:s')]);
            $id = $fileExist->id;
            print_r('aaaaaaaaaaaa');
        } else {
            $file = new FileUpload;
            $file->name = $name;
            $file->status = 'pending';
            $file->upload_at = date('Y-m-d H:i:s');
            $file->save();
            $id = $file->id;
            print_r('bbbbbbbbbbbbb');
        }

        $request->file->move(public_path('uploads'), $name);
        $msg = [];
        $msg['id'] = $id;
        $msg['name'] = $name;
        $msg['time'] = date('Y-m-d H:i:s');
        $msg['status'] = 'pending';
        SendMessage::dispatch($msg);
        ProcessCsv::dispatch(['id' =>$id, 'name' => $name]);
        // Excel::import(new ProductsImport(['id' =>$id, 'name' => $name]), public_path('uploads').'/'.$name, null, \Maatwebsite\Excel\Excel::CSV);
        return response()->json(['success'=>'Successfully uploaded.']);
    }

    public function validate_csv($filename)
    {
        $location = 'uploads';
        $filepath = public_path($location . "/" . $filename);
        // Reading file
        $file = fopen($filepath, "r");
        $importData_arr = array(); // Read through the file and store the contents as an array
        $i = 0;
        //Read the contents of the uploaded file 
        while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
            $num = count($filedata);
            // Skip first row (Remove below comment if you want to skip the first row)
            if ($i == 0) {
            $i++;
            continue;
            }
            for ($c = 0; $c < $num; $c++) {
            $importData_arr[$i][] = $filedata[$c];
            }
            $i++;
        }
        fclose($file);
    }
}
