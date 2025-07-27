@extends('layouts.vertical', ['title' => 'Roles List'])


@section('card-tools-add-btn')
    <div>
        <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary">
            <i class="bx bx-plus me-1"></i>Create Role
        </a>
    </div>
@endsection

{{-- @section('filters')
    <div class="row">
        <div class="col-md-3">
            <select class="form-control filter" data-choices name="guard_name" id="choices-single-default">
                <option value="" selected disabled>Select Guard</option>
                <option value="web">Web</option>
                <option value="api">API</option>
            </select>
        </div>
    </div>
@endsection --}}

@section('content')
    <x-datatable {{-- ajax-url="{{ route('roles.index') }}" --}} id="roles-table" :per-page="7" title="All Roles List" />
@endsection