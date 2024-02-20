<?php

namespace App\Components\Tracking\Models;

use App\Components\Common\Models\Estate;
use App\Components\Core\Utilities\Helpers;
use App\Components\Customers\Models\Customers;
use App\Components\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Components\Common\Models\Township;
use Kodeine\Metable\Metable;

class Prospect extends Model
{
    use Metable;

    protected $metaTable = "prospect_meta";
    protected $table = 'prospect';
    protected $primaryKey = 'id';
    protected $with = ['township'];

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'address',
        'town',
        'is_moral',
        'rfc',
        'company',
        'customer_id',
        'estate_id',
        'township_id',
        'segmentacion',
        'rating',
        'tactica_jd',
        'capacidad_tech',
        'registered_by',
    ];
    protected $appends = ['tracking_count'];


    /**
     * the owner of the prospect
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracking()
    {
        return $this->hasMany(TrackingProspect::class, 'prospect_id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

    public function township()
    {
        return $this->belongsTo(Township::class, 'township_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function getTrackingCountAttribute()
    {
        return $this->tracking()->whereHas('estatus', function ($q) {
            return $q->where('key', 'activo');
        })->count();
    }

    public function scopeSearch($query, string $search)
    {
        $query->when($search ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            })->orWhere('prospect_meta.value', 'like', "%{$search}%");
            ;
        });
    }

    public function scopeFilter($query, array $filters)
    {

        // $query->meta();


        $query->when($filters['full_name'] ?? null, function ($query, $full_name) {
            $query->where('full_name', 'like', '%' . $full_name . '%');
        })->when($filters['phone'] ?? null, function ($query, $phone) {
            $query->where('phone', 'like', '%' . $phone . '%');
        })->when($filters['cultivos'] ?? null, function ($query, $cultivos) {
            foreach (Helpers::commasToArray($cultivos) as $cultivo) {
                $query->where('prospect_meta.value', 'like', "%{$cultivo}%");
            }
        })->when($filters['tactica_jd'] ?? null, function ($query, $tactica_jd) {
            foreach (Helpers::commasToArray($tactica_jd) as $tactica) {
                $query->where('tactica_jd', 'like', "%{$tactica}%");
            }
        })->when($filters['segmentacion'] ?? null, function ($query, $segmentacion) {
            foreach (Helpers::commasToArray($segmentacion) as $segmento) {
                $query->where('segmentacion', 'like', "%{$segmento}%");
            }
        })->when($filters['capacidad_tech'] ?? null, function ($query, $capacidad_tech) {
            foreach (Helpers::commasToArray($capacidad_tech) as $capacidad) {
                $query->where('capacidad_tech', 'like', "%{$capacidad}%");
            }
        })->when($filters['rating'] ?? null, function ($query, $rating) {
            foreach (Helpers::commasToArray($rating) as $rate) {
                $query->where('rating', 'like', "%{$rate}%");
            }
        });

    }

    public function scopeMeta($query, $alias = null)
    {
        $alias = (empty($alias)) ? $this->getMetaTable() : $alias;
        return $query->leftJoin($this->getMetaTable() . ' AS ' . $alias, $this->getQualifiedKeyName(), '=', $alias . '.' . $this->getMetaKeyName())->select($this->getTable() . '.*');
    }

    public function scopeWhereMeta($query, $key, $value, $alias = null)
    {
        $alias = (empty($alias)) ? $this->getMetaTable() : $alias;
        return $query->leftJoin($this->getMetaTable() . ' AS ' . $alias, $this->getQualifiedKeyName(), '=', $alias . '.' . $this->getMetaKeyName())->where('key', '=', $key)->where('value', '=', $value)->select($this->getTable() . '.*');
    }
}
