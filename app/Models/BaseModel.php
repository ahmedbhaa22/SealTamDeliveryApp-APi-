<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;

class BaseModel extends Model
{
    public function storeFile($foldername, $fieldname)
    {
        if (request()->hasFile($fieldname)) {
            $file = request()->$fieldname;
            $path= $file->store($foldername);
            Storage::delete($this->$fieldname);
            return $path;
        }
        return $this->$fieldname;
    }
    public function deletFile($fieldname)
    {
        Storage::delete($this->$fieldname);
    }
}
