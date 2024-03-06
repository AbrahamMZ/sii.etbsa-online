<?php

namespace App\Components\Tracking\Models;

use App\Components\Common\Models\Currency;
use App\Components\Common\Models\Estatus;
use App\Components\Common\Models\Message;
use App\Components\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Components\Core\Utilities\Helpers;
use App\Components\Customers\Models\Customers;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;

class TrackingProspect extends Model
{

    use LogsActivity;
    protected $table = 'tracking_prospect';
    protected $primaryKey = 'id';

    // 'currency'

    protected $fillable = [
        'title',
        'reference',
        'description_topic',
        'price',
        'currency_id',
        'exchange_value',
        'category_id',
        'registered_by',
        'prospect_id',
        'customer_id',
        'estatus_id',
        'assigned_by',
        'attended_by',
        'agency_id',
        'department_id',
        'first_contact',
        'assertiveness',
        'tracking_condition',
        'invoice',
        'date_next_tracking',
        'date_lost_sale',
        'date_won_sale',
        'date_invoice'
    ];

    protected static $logAttributes = ['estatus.title', 'assigned.name','attended.name'];

    protected $appends = ['amount'];
    protected $with = ['registered', 'assigned', 'prospect'];

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function registered()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function assigned()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function attended()
    {
        return $this->belongsTo(User::class, 'attended_by');
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function agency()
    {
        return $this->belongsTo('App\Components\Common\Models\Agency', 'agency_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Components\Common\Models\Department', 'department_id');
    }
    public function moneda()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function category()
    {
        return $this->belongsTo('App\Components\Common\Models\CatTracking', 'category_id');
    }

    public function historical()
    {
        return $this->hasMany(TrackingHistoricalProspect::class, 'tracking_id', 'id');
    }

    public function quotation()
    {
        return $this->hasMany(TrackingQuote::class, 'tracking_id', 'id');
    }

    public function getHistoricalCountAttribute()
    {
        return $this->historical()->count();
    }

    public function files()
    {
        return $this->morphMany('App\Components\File\Models\File', 'fileable');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['folio'] ?? null, function ($query, $folio) {
            $query->where(function ($query) use ($folio) {
                $query->orWhere('id', 'like', $folio);
            });
        })->when($filters['title'] ?? null, function ($query, $title) {
            $query->where(function ($query) use ($title) {
                $query->orWhere('title', 'like', '%' . $title . '%')
                    ->orWhere('reference', 'like', '%' . $title . '%');
            });
        })->when($filters['category'] ?? null, function ($query, $category) {
            $query->where(function ($query) use ($category) {
                $query->orWhere('title', 'like', '%' . $category . '%');
            });
        })->when($filters['assertiveness'] ?? null, function ($query, $assertiveness) {
            $query->where(function ($query) use ($assertiveness) {
                $query->orWhere('assertiveness', $assertiveness);
            });
        })->when($filters['first_contact'] ?? null, function ($query, $first_contact) {
            $query->where(function ($query) use ($first_contact) {
                $query->orWhere('first_contact', $first_contact);
            });
        })->when($filters['prospect'] ?? null, function ($query, $prospect) {
            $query->whereHas('prospect', function ($query) use ($prospect) {
                $query->whereIn('id', Helpers::commasToArray($prospect));
            });
        })->when($filters['sellers'] ?? null, function ($query, $sellers) {
            $query->whereHas('attended', function ($query) use ($sellers) {
                $query->whereIn('id', Helpers::commasToArray($sellers));
            });
        })->when($filters['agencies'] ?? null, function ($query, $agencies) {
            $query->whereHas('agency', function ($query) use ($agencies) {
                $query->whereIn('id', Helpers::commasToArray($agencies));
            });
        })->when($filters['departments'] ?? null, function ($query, $departments) {
            $query->whereHas('department', function ($query) use ($departments) {
                $query->whereIn('id', Helpers::commasToArray($departments));
            });
        })->when($filters['estatus'] ?? null, function ($query, $estatus) {
            if ($estatus !== "todos") {
                $query->whereHas('estatus', function ($query) use ($estatus) {
                    $query->where('key', Helpers::commasToArray($estatus));
                });
            }
        }, function ($query) {
            $query->whereHas('estatus', function ($query) {
                $query->where('key', Estatus::ESTATUS_ACTIVO);
            });
        });
    }

    public function scopeFilterByDateRange($query, $rangeDates = [], $estatus = null)
    {
        $searchBy = 'updated_at';
        if ($estatus == 'finalizado') {
            $searchBy = 'date_lost_sale';
        } else if ($estatus == 'formalizado') {
            $searchBy = 'date_won_sale';
        } else if ($estatus == 'activo') {
            $searchBy = 'date_next_tracking';
        }

        $query->when($rangeDates ?? null, function ($query, $dates) use ($searchBy) {
            $query->where(function ($query) use ($dates, $searchBy) {
                $dates = Helpers::commasToArray($dates) ?? null;
                if (count($dates) == 2) {
                    $from = date($dates[0]);
                    $to = date($dates[1].' 23:59:59');
                    $query->whereBetween($searchBy, [$from, $to]);
                }
            });
        });
    }
    public function scopeFilterByMonths($query, $months = [], $estatus = null)
    {

        $query->when($months ?? null, function ($query, $months) {
            $query->where(function ($q) use ($months) {
                return $q->orWhereIn(DB::raw('MONTH(date_lost_sale)'), $months)
                    ->OrWhereIn(DB::raw('MONTH(date_won_sale)'), $months)
                    ->OrWhereIn(DB::raw('MONTH(date_next_tracking)'), $months);
            });
        });

    }
    public function scopeFilterByYear($query, $year = null, $estatus = null)
    {

        $query->when($year ?? null, function ($query, $year) {
            $query->where(function ($q) use ($year) {
                return $q->OrWhereYear('date_lost_sale', $year)
                    ->OrWhereYear('date_won_sale', $year)
                    ->OrWhereYear('date_next_tracking', $year);
            });
        });

    }

    public function scopeFilterForManagers($query, User $user)
    {
        $query->when(
            $user->inGroup('Gerente') ?? null,
            function ($query) use ($user) {
                $query->whereIn('agency_id', $user->seller_agency->pluck('id'))
                    ->whereIn('department_id', $user->seller_type->pluck('id'))
                    ->whereIn('title', $user->seller_category->pluck('name'))
                    ->orWhere('assigned_by', $user->id)
                    ->orWhere('attended_by', $user->id);
            },
            function ($query) use ($user) {
                $query->when($user->inGroup('Vendedor') ?? null, function ($query) use ($user) {
                    $query->where('attended_by', $user->id);
                });
            }
        );
    }

    public function getAmountAttribute()
    {
        return  $this->currency_id == 1 ?  $this->price :  $this->price * $this->exchange_value;
    }
    // public function getFilesAttribute()
    // {
    //     return $this->files->map->only('id', 'name', 'file_path', 'file_type', 'created_at');
    // }
}