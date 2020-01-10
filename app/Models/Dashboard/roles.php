<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;
use App\Models\Dashboard\permisions;

class roles extends Model
{
    protected $hidden = [
        'pivot'
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\Dashboard\admin_types', "type_id");
    }

    public function permisions()
    {
        return $this->belongsToMany('App\Models\Dashboard\permisions', "roles_permisions", "role_id", "permision_id");
    }

    public function createPageVM()
    {
        return [
            "types"=>admin_types::all(),
            "Permisions"=>permisions::all()
        ];
    }

    public function EditePageVM()
    {
        return [
            "Permisions"=>permisions::where('type_id', $this->type_id)->get(),
            "Role"=>$this,
            "Roles_Permisions"=> array_map(function ($p) {
                return $p['id'];
            }, $this->permisions->toArray())
        ];
    }

    public function Create($data, $type_id=1)
    {
        $this->type_id = $type_id;
        $this->name=$data->name;
        $this->save();
        $this->permisions()->sync($data->permisions);
    }

    public function Edit($data)
    {
        $this->name=$data->name;
        $this->save();
        $this->permisions()->sync($data->permisions);
    }
}
