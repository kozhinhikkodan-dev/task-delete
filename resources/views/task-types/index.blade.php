@extends('layouts.vertical', ['title' => 'Task Types List'])

@section('card-tools-add-btn')
    <div>
        <a href="{{ route('task-types.create') }}" class="btn btn-sm btn-primary">
            <i class="bx bx-plus me-1"></i>Create Task Type
        </a>
    </div>
@endsection

@section('filters')
    <div class="row">
        <div class="col-md-3">
            <select class="form-control filter" data-choices name="status" id="choices-status">
                <option value="" selected>All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
@endsection

@section('content')
    <x-datatable id="task-types-table" :per-page="10" title="All Task Types List" />
@endsection 