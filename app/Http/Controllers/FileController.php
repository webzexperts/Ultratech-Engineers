<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\File;

class FileController extends Controller
{
    /**
     * Deleting Temp Uploads
     */
    public function removeTempUpload(Request $request,File $File){

        
        if(is_array($request->docs)){
            $tmpFiles = $request->docs;
        }else{
            $tmpFiles = explode(',',$request->docs);
        }
    
        if(isset($request->docs) && !empty($tmpFiles)){
            $res = array();
            try{

                foreach($tmpFiles as $mTKey => $tmpFileVal){

                    $fileName = explode('/',$tmpFileVal); 
                    $fileName = explode('-',$fileName[1]); 
                    $fileLocType = $fileName[0]; 
                    
                    if($fileLocType == "temp"){
                        $res[$mTKey] = $File->delete_file($tmpFileVal);
                    }
                
                }
                return response()->json([
                    'response_data' => $res,
                    'response_code' => 1,
                    'response_message' => 'Temp uploads are removed'
                ]);
            }catch(\Exception $e){
                report($e);
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Error occured in deleting file',
                    'original_error' => $e->getMessage()
                ]);
            }
        }else{
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Please Provide Temp files'
            ]);
        }
    }
    
    /**
     * Copy files
     */
    public function copyFiles(Request $request,File $File){
        $responseData = array();
        $filesUrl = array();

        if(isset($request->docs) && !empty($request->docs)){
            try{

                foreach($request->docs as $mFKey => $fileVal){

                    $fileName = explode('.',$fileVal); 
                    $fileNameArr = explode('_',$fileName[0]);  
                    $timeStamp = time();
                    $fileNameArr[count($fileNameArr) - 1] = $timeStamp;
                    
                    $newName = implode('_',$fileNameArr); 
                    $newName =  $newName.'.'.$fileName[1];  

                    if($File->copy_file($fileVal,$newName)){
                        $responseData[$mFKey]  = $newName; 
                        $filesUrl[$mFKey] = asset('storage').'/'.$newName;
                    }

                }
                return response()->json([
                    'response_code' => 1,
                    'files' => $responseData,
                    'files_url' => $filesUrl
                ]);
            }catch(\Exception $e){
                report($e);
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Error occured in copying files',
                    'original_error' => $e->getMessage()
                ]);
            }
        }else{
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Please Provide Files'
            ]);
        }
    }

    /**
     * Upload files to temp folder
     */
    public function upload(Request $request){
        $responseData = array();
        $filesUrl = array();
     
        if(isset($request->docs) && !empty($request->docs)){
            try{

                foreach($request->docs as $mFKey => $fileVal){

                    $fileName = $fileVal->getClientOriginalName();
                    $ext = $fileVal->getClientOriginalExtension();
                    $fileName = explode('.',$fileName);   
                    $fileName = str_replace('-','_',str_replace(' ','_',$fileName[0]));
                    $timeStamp = time();
                    $fileName = 'temp-'.$fileName.'_'.$timeStamp.'.'.$ext;
    
                    $imagePath = $fileVal->storeAs('temp_media', $fileName,'public');
                  
                    $responseData[$mFKey]  = $imagePath; 
                    $filesUrl[$mFKey] = asset('storage').'/'.$imagePath;
                
                }
                return response()->json([
                    'response_code' => 1,
                    'files' => $responseData,
                    'files_url' => $filesUrl
                ]);
            }catch(\Exception $e){
                report($e);
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Error occured in uploading file',
                    'original_error' => $e->getMessage()
                ]);
            }
        }else{
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Please Upload File'
            ]);
        }
        
    }
}
