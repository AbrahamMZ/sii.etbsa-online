<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GpsImport implements ToModel, WithHeadingRow
{
    use Importable;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $gps_import = DB::table('gps_import')
            ->where('nombre', "{$row['nombre']}")
            ->first();

        // $date = new Carbon($row['creado']);
        $date = new Carbon($row['creado']);
        $date_due = $date->setYear(Carbon::now()->year);

        if (!$gps_import) {
            DB::table('gps_import')->insert([
                'nombre' => $row['nombre'],
                'sim' => $row['sim'],
                'currency' => $row['moneda'] ?? 'MXN',
                'exchange_rate' => $row['tipo_cambio'] ?? 1,
                'amount' => $row['importe'] ?? 0,
                'invoice' => $row['factura'] ?? null,
                'payment_type' => $row['tipo_pago'] ?? null,
                'creacion' => new Carbon($row['creado']),
                'fecha_carga' => Carbon::now(),
            ]
            );
            // return new Gps([
            //     'name' => $row['nombre'],
            //     'activation_date' => new Carbon($row['creado']),
            //     'due_date' => $date_due,
            //     'uploaded_by' => \Auth::user()->id,
            // ]);
        }

    }
}
