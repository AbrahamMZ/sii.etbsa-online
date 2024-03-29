<?php

namespace App\Http\Controllers\Admin;

use App\Components\Common\Models\Agency;
use App\Components\Common\Models\Department;
use App\Components\RRHH\Models\DirectBoss;
use App\Components\RRHH\Models\Employee;
use App\Components\RRHH\Repositories\EmployeeRepository;
use App\Components\User\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends AdminController
{
    private $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = $this->employeeRepository->list(request()->all());
        $filtersOptions = [
            "agencies" => Agency::all('id', 'title'),
            "departments" => Department::all('id', 'title')
        ];
        return $this->sendResponseOk(compact('items', 'filtersOptions'), "Emepleados Encontrados");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //resources form
        // ->whereNull('profiable_id')
        $users = DB::table('users')->get(['id', 'email', 'name']);
        $agencies = DB::table('agencies')->get(['id', 'code', 'title']);
        $departments = DB::table('departments')->get(['id', 'title']);
        $jobs = DB::table('jobs')->get(['id', 'title']);
        $direct_boss = Employee::all('id', 'name', 'second_name', 'last_name', 'second_last_name');
        return $this->sendResponseOk(
            compact(
                'agencies',
                'departments',
                'jobs',
                'direct_boss',
                'users',
            ),
            "list Resources orders ok."
        );
    }

    /**
     * Store a newly created resource in storage.sisabr
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // dd($request->get('user_email'), $request->has('user_email'), is_null($request->user_email));
        $request['created_by'] = Auth::user()->id;
        $validate = validator($request->all(), [
            'photo' => ['nullable', 'file'],
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        return DB::transaction(function () use ($request) {

            $employee = $this->employeeRepository->create(
                // [
                $request->all(),
                // 'photo_path' => $request->file('photo') ? $request->file('photo')->store('avatares/employees/'.) : null,
                // ]
            );
            if (!$employee) {
                return $this->sendResponseBadRequest("Failed create.");
            }

            $employee->update([
                'photo_path' => $request->file('photo')
                    ? $request->file('photo')->store('avatares/employees/' . $employee->id, 's3')
                    : null
            ]);
            if ($request->has('user_id')) {
                if (is_null($request->user_id)) {
                    $employee->user->profiable()->dissociate();
                    $employee->user->save();
                } else {
                    return $this->assignedUser(
                        $employee,
                        User::firstOrCreate(
                            ['email' => $request->user_id['email'] ?? $request->user_email],
                            ['name' => $request->name, 'password' => $request->name]
                        )
                    );
                }
            }

            return $this->sendResponseCreated(compact('employee'));
        });

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Components\RRHH\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        $data = [
            'id' => $employee->id,
            'name' => $employee->full_name,
            'number_employee' => $employee->number_employee ?? '',
            'agency' => $employee->agency->title ?? '',
            'department' => $employee->department->title ?? '',
            'jobs' => $employee->job->title ?? '',
            'job' => $employee->job_title ?? '',
            'phone' => $employee->phone ?? '',
            'address' => $employee->address . ' ' . $employee->colonia . ',' . $employee->code_postal,
            'estado' => $employee->township->estate->name ?? '',
            'municipio' => $employee->township->name ?? '',
            'boss' => $employee->boss->full_name ?? '',
            'user' => $employee->user->email ?? null,
            'user_id' => $employee->user->id ?? null,
            'user_email' => $employee->user->email ?? null,
            'photo' => $employee->profile_photo_url,
        ];

        return $this->sendResponseOk($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Components\RRHH\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        return $this->sendResponseOk($employee->load('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Components\RRHH\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {

        // dd(
        //     $request->all(),
        //     $request->user_id['email'] ?? $request->user_email
        // );
        $validate = validator($request->all(), [
            'photo' => ['nullable', 'file'],
        ]);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        return DB::transaction(function () use ($request, $employee) {

            $updated = $employee->update($request->all());
            if (!$updated) {
                return $this->sendResponseBadRequest("Failed to update");
            }
            if ($request->file('photo')) {
                if (Storage::disk('s3')->exists($employee->photo_path)) {
                    $delete = Storage::disk('s3')->delete($employee->photo_path);
                }
                $employee->update(['photo_path' => $request->file('photo')->store('avatares/employees/' . $employee->id, 's3')]);
            }
            if ($request->has('user_id')) {
                if (is_null($request->user_id)) {
                    $employee->user->profiable()->dissociate();
                    $employee->user->save();
                } else {

                    return $this->assignedUser(
                        $employee,
                        User::firstOrCreate(
                            ['email' => $request->user_id['email'] ?? $request->user_email],
                            ['name' => $request->name, 'password' => $request->name]
                        )
                    );
                }
            }
            return $this->sendResponseUpdated([$updated]);
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Components\RRHH\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        //
    }


    public function assignedUser(Employee $employee, User $user)
    {

        if ($employee->user) {
            $employee->user->profiable()->dissociate();
            $employee->user->save();
        }
        $user->profiable()->associate($employee);
        $user->save();
        return $this->sendResponseOk([], 'Usuario Asignado');
    }
    public function assignedBoss(DirectBoss $employee_boss, Employee $employee)
    {
        $employee->boss()->associate($employee_boss);
        $employee->save();
        return $this->sendResponseOk([], 'Jefe Directo Asignado');
    }

    public function options()
    {
        $users = DB::table('users')->whereNull('profiable_id')->get(['id', 'name', 'email']);
        return $this->sendResponseOk(
            compact(
                'users',
            ),
            "list Resources orders ok."
        );
    }
}