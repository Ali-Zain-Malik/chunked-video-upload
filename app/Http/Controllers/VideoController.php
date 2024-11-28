<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function upload(Request $request)
    {
        $chunk = $request->file("video_chunk");
        $index = $request->input("chunk_index");
        $total_chunks = $request->input("total_chunks");
        $file_name = $request->input("file_name");
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_name = time() . "_" . Str::random(10) . "." . $extension;
        $temp_dir = storage_path("app/temp/" . md5($file_name));

        try
        {
            if(!$temp_dir)
            {
                mkdir($temp_dir, 0777, true);
            }
    
            $chunk->move($temp_dir, $index);
    
            // If it is the last chunk
            if($index + 1 == $total_chunks)
            {
                $uploads = storage_path("app/public/uploads");
                if(!is_dir($uploads))
                {
                    mkdir($uploads, 0777, true);
                }
    
                $actual_path = $uploads . DIRECTORY_SEPARATOR . $new_name;
                $output_file = fopen($actual_path, "wb");
                for($i = 0; $i < $total_chunks; $i++)
                {
                    $chunk_path = $temp_dir . DIRECTORY_SEPARATOR . $i;
                    $chunk_file = fopen($chunk_path, "rb");
                    while($buffer = fread($chunk_file, 1024))
                    {
                        fwrite($output_file, $buffer);
                    }
    
                    fclose($chunk_file);
                    @unlink($chunk_path);
                }
    
                fclose($output_file);
                rmdir($temp_dir);
    
                $file = new File();
                $file->fill([
                    "name" => $file_name,
                    "extension" => $extension,
                    "file_url" => $actual_path,
                ]);
                $file->save();
                
                return response()->json([
                    "message" => "Chunk uploaded",
                ], 200);
            }
        }
        catch(\Exception $ex)
        {
            return response()->json([
                "message" => $ex->getMessage()
            ], 500);
        }

    }
}
