@extends('layouts.vertical', ['title' => 'Tasks List'])

@section('card-tools-add-btn')
    <div class="d-flex align-items-center">
        @include('common.components.view-toggle', ['currentView' => 'table'])
        @unless(auth()->user()->hasRole('Staff'))
        <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
            <i class="bx bx-plus me-1"></i>Add New Task
        </a>
        @endunless
    </div>
@endsection

@section('filters')
    <div class="row">
        <div class="col-md-3">
            <select class="form-control filter" data-choices name="status" id="choices-status">
                <option value="" selected>All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-control filter" data-choices name="customer_id" id="choices-customer">
                <option value="" selected>All Customers</option>
                @foreach(\App\Models\Customer::where('status', 'active')->get() as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-control filter" data-choices name="task_type_id" id="choices-task-type">
                <option value="" selected>All Task Types</option>
                @foreach(\App\Models\TaskType::where('status', 'active')->get() as $taskType)
                    <option value="{{ $taskType->id }}">{{ $taskType->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-control filter" data-choices name="assigned_to" id="choices-assigned">
                <option value="" selected>All Users</option>
                @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endsection

@section('content')
    <x-datatable id="tasks-table" :per-page="10" title="All Tasks List" />
@endsection 