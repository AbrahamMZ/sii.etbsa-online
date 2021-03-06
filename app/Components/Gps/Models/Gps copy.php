<?php

namespace App\Components\Gps\Models;

use Carbon\Carbon;
use App\Components\User\Models\User;
use Illuminate\Database\Eloquent\Model;

class Gps extends Model
{
    use GpsTrait;
    protected $table = 'gps';

    protected $fillable = [
        'name',
        'uploaded_by',
        'gps_group_id',
        'gps_chip_id',
        'currency',
        'exchange_rate',
        'amount',
        'invoice',
        'payment_type',
        'estatus',
        'installation_date',
        'renew_date',
        'cancellation_date',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'uploaded_by');
    }

    /**
     * the group the gps belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gpsGroup()
    {
        return $this->belongsTo(GpsGroup::class, 'gps_group_id');
    }

    /**
     * Get the chip record associated with the g.
     */
    public function chip()
    {
        // return $this->hasOne(GpsChips::class,'sim','gps_chip_id');
        return $this->belongsTo(GpsChips::class,'gps_chip_id');
    }

    public function historical()
    {
        return $this->hasMAny(GpsHistorical::class,'gps_id','id');
    }

    public function scopeOfName($query, $name)
    {
        if ($name === null || $name === '') {
            return false;
        }

        return $query->where('name', 'like', "%{$name}%");
    }

    public function scopeOfPayment($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }

        return $query->where('payment_type', 'LIKE', "%{$v}%");
    }
    public function scopeOfEstatus($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }

        return $query->where('estatus', 'LIKE', "%{$v}%");
    }

    public function scopeOfMonth($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }
        return $query->whereMonth('renew_date', $v);
    }

    public function scopeOfYear($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }
        return $query->whereYear('renew_date', $v);
    }
    public function scopeOfMonthInstallation($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }
        return $query->whereMonth('installation_date', $v);
    }

    public function scopeOfYearInstallation($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }
        return $query->whereYear('installation_date', $v);
    }
    public function scopeOfMonthCancelled($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }
        return $query->whereMonth('cancellation_date', $v);
    }

    public function scopeOfYearCancelled($query, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }
        return $query->whereYear('cancellation_date', $v);
    }

    public function scopeOfGpsGroups($q, $v)
    {
        if ($v === false || $v === '' || count($v) == 0 || $v[0] == '') {
            return $q;
        }

        return $q->whereHas('gpsGroup', function ($q) use ($v) {
            return $q->whereIn('id', $v);
        });
    }
    public function scopeOfGpsChips($q, $v)
    {
        if ($v === false || $v === '' || count($v) == 0 || $v[0] == '') {
            return $q;
        }

        return $q->whereHas('chip', function ($q) use ($v) {
            return $q->whereIn('sim', $v);
        });
    }


    public function scopeOfAgency($q, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }
        return $q->whereHas('gpsGroup', function ($q) use ($v) {
            return $q->where('agency', $v);
        });
    }
    public function scopeOfDepartment($q, $v)
    {
        if ($v === null || $v === '') {
            return false;
        }

        return $q->whereHas('gpsGroup', function ($q) use ($v) {
            return $q->where('department', $v);
        });
    }

    public function scopeOfAssigned($q, $v)
    {
        if ($v === null || $v == false) {
            return false;
        }

        return $q->has('chip');
    }

    public function scopeOfDeallocated($q, $v)
    {
        if ($v === null || $v == false) {
            return false;
        }

        return $q->doesntHave('chip');
    }

    public function scopeOfExpired($q, $v)
    {
        if ($v === null || $v == false) {
            return false;
        }

        return $q->whereDate('renew_date', '<=', Carbon::now());
    }
    public function scopeOfRenewed($q, $v)
    {
        if ($v === null || $v == false) {
            return false;
        }

        return $q->whereYear('renew_date', '>=', Carbon::now()->addYear()->year);
    }

    public function scopeOfCancelled($q,$v){
        if ($v === null || $v == false) {
            return false;
        }

        return $q->whereNotNull('cancellation_date');
    }
}
