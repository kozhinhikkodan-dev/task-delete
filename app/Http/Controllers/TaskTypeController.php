<?php

namespace App\Http\Controllers;

use App\DataTables\TaskTypesDataTable;
use App\Http\Requests\TaskTypeRequest;
use App\Http\Traits\DataTable;
use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskTypeController extends Controller
{
    use DataTable;

    public function __construct()
    {
        $this->authorizeResource(TaskType::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TaskTypesDataTable $dataTable)
    {
        return $this->renderDataTable($request, $dataTable, 'task-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('task-types.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskTypeRequest $request)
    {
        DB::transaction(function () use ($request) {
            TaskType::create($request->getData());
        });

        return redirect()->route('task-types.index')->with('success', 'Task type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskType $taskType)
    {
        return view('task-types.show', compact('taskType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskType $taskType)
    {
        return view('task-types.form', compact('taskType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskTypeRequest $request, TaskType $taskType)
    {
        DB::transaction(function () use ($request, $taskType) {
            $taskType->update($request->getData());
        });

        return redirect()->route('task-types.index')->with('success', 'Task type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskType $taskType)
    {
        DB::transaction(function () use ($taskType) {
            $taskType->delete();
        });

        return redirect()->route('task-types.index')->with('success', 'Task type deleted successfully.');
    }
}
