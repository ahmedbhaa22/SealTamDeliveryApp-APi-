<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Models\General\category;
use App\Http\Controllers\Shared\BaseController;
use App\Http\Resources\categoryResource;

class CategoryController extends BaseController
{
    public $validationRule=
                [
                    'arabicname'	=>'required|string|regex:/\p{Arabic}/u',
                    'englishname'   =>'required|string',
                    'arabicname'    =>'required|string',
                    'type'          =>'required'
                ];


    public function GetListPage()
    {
        return $this->Response(true, (new Category())->getListPage());
    }

    public function store(Request $request)
    {
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }
        (new category())->store($request);

        return $this->Response(true, null);
    }

    public function edit(Request $request, $id)
    {
        $validationResult = $this->executeValidation();
        if ($validationResult) {
            return $validationResult;
        }
        $category = category::find($id);
        if ($category == null) {
            return $this->Response(false, null, "messages.Globale.InvalidId");
        }

        $category->edit($request);

        return $this->Response(true, null);
    }

    public function getEditPage($id)
    {
        return $this->Response(true, new categoryResource(category::find($id)));
    }

    
    public function delete($id)
    {
        category::find($id)->remove();
        return $this->Response(true, null);
    }
}
