<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use DB;
use Illuminate\Http\Request;
use App\Models\FileUpload;

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
            'file' => 'required',
        ]);
 
       $name = time().'.'.request()->file->getClientOriginalExtension();
  
       $request->file->move(public_path('uploads'), $name);
 
       $file = new FileUpload;
       $file->name = $name;
       $file->status = 'pending';
       $file->upload_at = date('Y-m-d H:i:s');
       $file->save();
       $msg = [];
       $msg['name'] = $name;
       $msg['time'] = date('Y-m-d H:i:s');
       $msg['status'] = 'pending';
    //    event(new \App\Events\SendMessage($msg));
        SendMessage::dispatch($msg);
        // SendMessage
    //    event(new \App\Events\SendMessage('hehe'));
  
        return response()->json(['success'=>'Successfully uploaded.']);
    }
}
