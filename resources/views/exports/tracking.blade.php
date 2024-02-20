<table>
    <thead>
        <tr>
            <th>FOLIO</th>
            <th>Nombre Prospecto</th>
            <th>Compañia</th>
            <th>Tel. Prospecto</th>
            <th>Email. Prospecto</th>
            <th>Domicilio</th>
            <th>Municipio</th>
            <th>Estado</th>
            <th>Categoria</th>
            <th>Referencia</th>
            <th>Precio</th>
            <th>T. Moneda</th>
            <th>Agencia</th>
            <th>Departamento</th>
            <th>Certeza</th>
            <th>Vendedor Asignado</th>
            <th>Registrado por</th>
            <th>Estatus</th>
            <th>Medio Contacto</th>
            <th>Ultimo Cambio</th>
            <th>Fecha Registro</th>
            <th>Fecha Factura</th>
            <th>Folio Factura</th>
            <th>Importe Factura</th>
            <th>Utimo Comentario</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tracking as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->prospect->full_name }}</td>
                <td>{{ $item->prospect->company ?? '' }}</td>
                <td>{{ $item->prospect->phone }}</td>
                <td>{{ $item->prospect->email ?? '' }}</td>
                <td>{{ $item->prospect->town ?? '' }}</td>
                <td>{{ $item->prospect->township->name ?? '' }}</td>
                <td>{{ $item->prospect->township->estate->name ?? '' }}</td>
                <td>{{ $item->title ?? 'S/A' }}</td>
                <td>{{ $item->reference ?? 'S/A' }}</td>
                <td>{{ '$' . $item->price ?? 'S/A' }}</td>
                <td>{{ $item->currency ?? 'MXN' }}</td>
                <td>{{ $item->agency->title ?? 'S/A' }}</td>
                <td>{{ $item->department->title ?? 'S/A' }}</td>
                <td>{{ $item->assertiveness * 100 ?? 'S/A' }}%</td>
                <td>{{ $item->attended->name ?? 'S/A' }}</td>
                <td>{{ $item->registered->name ?? 'S/A' }}</td>
                <td>{{ $item->estatus->title ?? 'S/A' }}</td>
                <td>{{ $item->first_contact ?? 'S/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y g:i:s') ?? 'S/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y g:i:s') ?? 'S/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->date_won_sale)->format('d/m/Y') ?? 'S/A' }}</td>
                <td>{{ $item->invoice ?? '' }}</td>
                <td>{{ '$' . $item->price ?? 'S/A' }} {{ $item->currency ?? 'MXN' }}</td>
                <td>{{ $item->historical->last()->message ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
