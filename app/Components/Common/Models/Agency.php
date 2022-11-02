<?php

namespace App\Components\Common\Models;

use App\Components\Purchase\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use App\Components\User\Models\User;

class Agency extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'agencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'title', 'line_id', 'address'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tracking()
    {
        return $this->hasMany('App\Components\Tracking\Models\TrackingProspect');
    }

    public function purchaseOrder()
    {
        return $this->belongsToMany(PurchaseOrder::class, 'purchase_agency_pivot_table', 'agency_id');
    }
}
