@extends('layouts.vertical', ['title' => 'User List'])


@section('card-tools-add-btn')
    <div>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
            <i class="bx bx-plus me-1"></i>Create User
        </a>
    </div>
@endsection

@section('filters')
    <div class="row">
        <div class="col-md-3">
            <select class="form-control filter" data-choices name="role" id="choices-single-default">
                <option value="" selected disabled>Select Role</option>
                @foreach (App\Models\Role::all() as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endsection

@section('content')
    <x-datatable {{-- ajax-url="{{ route('roles.index') }}" --}} id="users-table" :per-page="7" title="All Users List" />
@endsection