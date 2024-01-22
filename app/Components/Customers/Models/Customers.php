<?php

namespace App\Components\Customers\Models;

use App\Components\Common\Models\Township;
use App\Components\Tracking\Models\Prospect;
use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;

class Customers extends Model
{
    use Metable;
    protected $metaTable = "customer_meta";
    protected $table = 'customers';
    protected $guard = ['id'];
    protected $hidden = ['metas'];
    protected $with = ['township'];
    protected $appends = ['path_documents'];

    protected $fillable = [
        'address',
        'birthday_date',
        'code_postal',
        'colonia',
        'company',
        'curp',
        'email',
        'fiscal_type',
        'full_name',
        'is_moral',
        'number_customer',
        'phone',
        'rfc',
        'customer_category_id',
        'town_id',
        'capacidad_tech',
        'rating'
    ];

    public function township()
    {
        return $this->belongsTo(Township::class, 'town_id');
    }

    public function prospects(){
        return $this->hasMany(Prospect::class, 'customer_id');
    }


    public function getPathDocumentsAttribute()
    {
        return trim(str_replace('  ', ' ',  "/clientes/{$this->id}/"));
    }

    public function scopeSearch($query, String $search)
    {
        $query->when($search ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('rfc', 'like', "%{$search}%");
            });
        });
    }
}
