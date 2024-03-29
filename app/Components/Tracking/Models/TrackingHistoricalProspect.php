<?php

namespace App\Components\Tracking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Components\User\Models\User;

class TrackingHistoricalProspect extends Model
{
    protected $table = 'tracking_historical';
    protected $primaryKey = 'id';

    protected $fillable = [
        'message', 'tracking_id', 'user_id', 'last_price',
        'last_currency', 'type_contacted', 'last_assertiveness', 'type_contacted', 'invoice',
        'date_next_tracking'
    ];
    // protected $with = ['user'];

    public function tracking()
    {
        return $this->belongsTo(TrackingProspect::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
