<?php

namespace App;

use Illuminate\Http\UploadedFile;

trait UploadFileTrait
{
    function upload(UploadedFile $file, $folder, $disk)
    {
        return $file->store($folder, $disk);
    }

}
