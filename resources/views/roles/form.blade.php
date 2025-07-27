@extends('layouts.vertical', ['title' => 'Role ' . (isset($role) ? 'Edit' : 'Add')])

@section('css')
    @vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ isset($role) ? route('roles.update', $role->id) : route('roles.store') }}" method="post">
                    @csrf
                    @method(isset($role) ? 'PUT' : 'POST')

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Roles Information</h4>
                        <div class="card-tools">
                            <a data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Back to Roles"
                                href="{{ route('roles.index') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>

                            {{-- <a href="{{ route('roles.index') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-eye me-1"></i>
                            </a> --}}
                        </div>
                    </div>

                    <div class="card-body">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input:
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif


                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="roles-name" class="form-label required">Roles Name</label>
                                    <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}"
                                        id="roles-name" class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Role name">
                                    @error('name')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-lg-6">
                                <p> Status </p>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="flexRadioDefault"
                                            id="flexRadioDefault1" checked="">
                                        <label class="form-check-label" for="flexRadioDefault1">
                                            Active
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="flexRadioDefault"
                                            id="flexRadioDefault2">
                                        <label class="form-check-label" for="flexRadioDefault2">
                                            In Active
                                        </label>
                                    </div>
                                </div>
                            </div> --}}
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Permissions</h5>

                                    <div class="form-check form-radio-success form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="permission-check-all">
                                        <label class="form-check-label" for="permission-check-all">
                                            All Permissions
                                        </label>
                                    </div>
                                </div>
                                @foreach (Spatie\Permission\Models\Permission::select('group')->distinct()->get() as $group)
                                    <div class="permission-group alert alert-info">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-0">{{ ucfirst($group->group) }}</h5>

                                            <div class="form-check form-radio-warning form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="permission-check-group-{{ $group->group }}">
                                                <label class="form-check-label"
                                                    for="permission-check-group-{{ $group->group }}">
                                                    All {{ ucfirst($group->group) }} Permissions
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            @foreach (Spatie\Permission\Models\Permission::where('group', $group->group)->get() as $permission)
                                                <div class="col-md-2 mb-2 form-check form-switch">
                                                    <input class="form-check-input permission-group-{{ $group->group }}"
                                                        type="checkbox" role="switch" @if (
                                                            isset($role) &&
                                                            $role->hasPermissionTo($permission->name)
                                                        ) checked @endif
                                                        name="permissions[]" value="{{ $permission->name }}"
                                                        id="permission-id-{{ $permission->id }}">
                                                    <label class="form-check-label"
                                                        for="flexSwitchCheckDefault">{{ $permission->name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    <div class="card-footer border-top">
                        <button type="submit" class="btn btn-primary">@if (isset($role)) Update @else Create @endif</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script-bottom')
    @vite(['resources/js/pages/app-ecommerce-product.js'])
@endsection


@section('script')
    <script>
        window.addEventListener('load', function () {
            if (window.jQuery) {
                $(document).ready(function () {
                    // Global check all
                    $('#permission-check-all').on('change', function () {
                        const checked = $(this).is(':checked');
                        $('input[name^="permissions["]:not(:disabled)').prop('checked', checked);
                        $('[id^="permission-check-group-"]:not(:disabled)').prop('checked', checked);
                    });

                    // Group check all
                    $('[id^="permission-check-group-"]').on('change', function () {
                        const group = this.id.replace('permission-check-group-', '');
                        const checked = $(this).is(':checked');
                        $(`.permission-group-${group}:not(:disabled)`).prop('checked', checked);
                        updateGlobalSwitch();
                    });

                    // When individual permission is changed
                    $('input[name^="permissions["]').on('change', function () {
                        const groupClass = $(this).attr('class').split(' ').find(c => c.startsWith('permission-group-'));
                        if (groupClass) {
                            const group = groupClass.replace('permission-group-', '');
                            updateGroupSwitch(group);
                        }
                        updateGlobalSwitch();
                    });

                    function updateGroupSwitch(group) {
                        const groupInputs = $(`.permission-group-${group}:not(:disabled)`);
                        const groupSwitch = $(`#permission-check-group-${group}`);
                        const allChecked = groupInputs.length && groupInputs.filter(':checked').length === groupInputs.length;
                        groupSwitch.prop('checked', allChecked);
                    }

                    function updateGlobalSwitch() {
                        const allInputs = $(`input[name^="permissions["]:not(:disabled)`);
                        const allChecked = allInputs.length && allInputs.filter(':checked').length === allInputs.length;
                        $('#permission-check-all').prop('checked', allChecked);
                    }

                    // Initial setup
                    $('[id^="permission-check-group-"]').each(function () {
                        const group = this.id.replace('permission-check-group-', '');
                        updateGroupSwitch(group);
                    });

                    updateGlobalSwitch();
                });
            }
        });
    </script>

@endsection