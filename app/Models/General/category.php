<?php

namespace App\Models\General;

use App\Models\BaseModel;
use App\Http\Resources\categoryResource;

class category extends BaseModel
{
    public function store($request)
    {
        $this->setData($request);
        $this->save();
    }

    public function edit($request)
    {
        $this->setData($request);
        $this->save();
    }

    public function setData($request)
    {
        $this->icon = $this->storeFile('icons', 'icon');
        $this->arabicname = $request->arabicname;
        $this->englishname = $request->englishname;
        $this->type = $request->type;
    }

    public function getListPage()
    {
        return categoryResource::collection($this->all());
    }

    public static function getdriverCategories()
    {
        return categoryResource::collection(self::where('type', 'drivers')->get());
    }

    public static function getresturantCategories()
    {
        return categoryResource::collection(self::where('type', 'shops')->get());
    }

    public function remove()
    {
        $this->deletFile('icon');
        $this->delete();
    }
}
