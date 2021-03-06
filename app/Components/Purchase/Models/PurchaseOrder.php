<?php

namespace App\Components\Purchase\Models;

use Illuminate\Database\Eloquent\Model;

use App\Components\User\Models\User;
use App\Components\Common\Models\Estatus;
use App\Components\Common\Models\Agency;
use App\Components\Common\Models\CatFormaPago;
use App\Components\Common\Models\CatMetodoPago;
use App\Components\Common\Models\CatUsoCfdi;
use App\Components\Common\Models\Department;
use App\Components\Common\Models\Document;
use App\Components\Common\Models\Message;
use App\Components\Core\Utilities\Helpers;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $guarded = ['id'];

    protected $with = [
        'estatus', 'elaborated', 'updated_user', 'uso_cfdi',
        'metodo_pago', 'forma_pago', 'sucursal', 'departamento'
    ];

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function elaborated()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updated_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function uso_cfdi()
    {
        return $this->belongsTo(CatUsoCfdi::class, 'uso_cfdi_id', 'clave');
    }
    public function metodo_pago()
    {
        return $this->belongsTo(CatMetodoPago::class, 'metodo_pago_id', 'clave');
    }

    public function forma_pago()
    {
        return $this->belongsTo(CatFormaPago::class, 'forma_pago_id', 'clave');
    }

    public function sucursal()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * serializes concept attribute on the fly before saving to database
     *
     * @param $concepts
     */
    public function setConceptsAttribute($concepts)
    {
        $this->attributes['concepts'] = serialize($concepts);
    }

    /**
     * unserializes concepts attribute before spitting out from database
     *
     * @return mixed
     */
    public function getConceptsAttribute()
    {
        if (empty($this->attributes['concepts']) || is_null($this->attributes['concepts'])) {
            return [];
        }

        return unserialize($this->attributes['concepts']);
    }

    public function scopeSearch($query, String $search)
    {
        $query->when($search ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('id', 'like', $search)
                    ->orWhere('authorization_token', 'like', "%{$search}%")
                    ->orWhereHas('sucursal', function ($query) use ($search) {
                        return $query->where('agencies.title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($query) use ($search) {
                        return $query->where('suppliers.business_name', 'like', "%{$search}%");
                    })->orWhereHas('elaborated', function ($query) use ($search) {
                        return $query->where('users.name', 'like', "%{$search}%");
                    });
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['folio'] ?? null, function ($query, $folio) {
            $query->where(function ($query) use ($folio) {
                $query->orWhere('id', 'like', $folio);
            });
        })->when($filters['supplier'] ?? null, function ($query, $supplier) {
            $query->whereHas('supplier', function ($query) use ($supplier) {
                return $query->where('id', $supplier);
            });
        })->when($filters['agencie'] ?? null, function ($query, $agencie) {
            $query->whereHas('sucursal', function ($query) use ($agencie) {
                return $query->where('id', $agencie);
            });
        })->when($filters['metodo_pago'] ?? null, function ($query, $metodoPago) {
            $query->whereHas('metodo_pago', function ($query) use ($metodoPago) {
                return $query->where('clave', $metodoPago);
            });
        })->when($filters['uso_cfdi'] ?? null, function ($query, $usoCfdo) {
            $query->whereHas('uso_cfdi', function ($query) use ($usoCfdo) {
                return $query->where('clave', $usoCfdo);
            });
        })->when($filters['forma_pago'] ?? null, function ($query, $formaPago) {
            $query->whereHas('forma_pago', function ($query) use ($formaPago) {
                return $query->where('clave', $formaPago);
            });
        })->when($filters['estatus'] ?? null, function ($query, $estatus) {
            if ($estatus !== "todos") {
                $query->whereHas('estatus', function ($query) use ($estatus) {
                    $query->where('key', Helpers::commasToArray($estatus));
                });
            }
        }, function ($query) {
            $query->whereHas('estatus', function ($query) {
                $query->where('key', Estatus::ESTATUS_PENDIENTE);
            });
        });
    }

    public function scopeFilterPermission($query, User $user)
    {
        $query->when(
            ($user->inGroup('Gerente') && $user->hasPermission('compras.admin')) || $user->hasPermission('compras.all.list') || $user->isSuperUser(),
            function ($query) {
                return $query;
            },
            function ($query) use ($user) {
                $query->when($user->inGroup('Compras') ?? null, function ($query) use ($user) {
                    $query->where('created_by', $user->id);
                });
            }
        );
    }
}
