<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    public static function copy_file($old_path,$new_path){
        if(self::Is_Files_Exists($old_path)){
            \Storage::disk('public')->copy($old_path, $new_path);
            return true;
        }else{
            return false;
        }
    }

    public static function delete_file($path)
    {
        if (\Storage::disk('public')->exists($path))
            return \Storage::disk('public')->delete($path);
    }

    public static function Is_Files_Exists($path)
    {
        if (\Storage::disk('public')->exists($path)){
            return true;
        }
        else{
            return false;
        }
    }

    public static function move_file($old_path,$new_path){
        if(self::Is_Files_Exists($old_path)){
            \Storage::disk('public')->move($old_path, $new_path);
            return true;
        }else{
            return false;
        }
    }

    public function getFileFromTemp($tmpFileVal,$prefix = "uploads"){

        if($tmpFileVal != ""){
            $old_path = $tmpFileVal;
            $fileName = explode('/',$tmpFileVal);
            $fileName = explode('-',$fileName[1]);
            $fileLocType = $fileName[0];
            $fileName = $fileName[1];

            if($fileLocType == "temp"){
                $fileName = $prefix.'-'.rand(3,10000).'_'.$fileName;

                $new_path = 'uploads/'.$fileName;
                $isStore = \Storage::disk('public')->move($old_path, $new_path);
            }

            if(isset($isStore) && $isStore){

                self::delete_file($tmpFileVal);
                return $new_path;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    public function getFileCopyFormUpload($tmpFileVal,$prefix = "uploads"){

        if($tmpFileVal != ""){
            $old_path = $tmpFileVal;
            $fileName = explode('/',$tmpFileVal);
            $fileName = explode('-',$fileName[1]);
            $fileLocType = $fileName[0];
            $fileName = $fileName[1];

            if($fileLocType != "temp"){
                $fileName = $prefix.'-'.rand(3,10000).'_'.$fileName;

                $new_path = 'uploads/'.$fileName;
                $isStore = self::copy_file($old_path,$new_path);
            }

            if(isset($isStore) && $isStore){

                return $new_path;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }
}
