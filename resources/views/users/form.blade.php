@extends('layouts.vertical', ['title' => 'User ' . (isset($user) ? 'Edit' : 'Add')])

@section('css')
    @vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])
    <style>
        .choices {
            margin-bottom: 0 !important;
        }
    </style>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="post">
                    @csrf
                    @method(isset($user) ? 'PUT' : 'POST')

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ isset($user) ? 'Edit User' : 'Create New User' }}</h4>
                        <div class="card-tools">
                            <a data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Back to Users"
                                href="{{ route('users.index') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
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
                            <!-- Name and Email -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label required">Name</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
                                        class="form-control @error('name') is-invalid @enderror" placeholder="Name">
                                    @error('name')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label required">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                                        class="form-control @error('email') is-invalid @enderror" placeholder="Email">
                                    @error('email')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Username and Password -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label required">Username</label>
                                    <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}"
                                        class="form-control @error('username') is-invalid @enderror" placeholder="Username">
                                    @error('username')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label required">Password</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror" placeholder="Password">
                                    @error('password')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Confirm Password and User Role -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label required">Confirm Password</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        placeholder="Confirm Password">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="role_name" class="form-label required">User Role</label>
                                    <select class="form-control filter" data-choices name="role_name"
                                        id="choices-single-default">
                                        <option value="" selected disabled>Select Role</option>
                                        @foreach (App\Models\Role::all() as $role)
                                            <option @if(old('role_name', $user?->hasRole($role->name) ?? '') == $role->name) selected @endif value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_name')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Minimum Task Per Day and Maximum Task Per Day -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="min_task_per_day" class="form-label required">Minimum Task Per Day</label>
                                    <input type="number" name="min_task_per_day" value="{{ old('min_task_per_day', $user->min_task_per_day ?? '') }}"
                                        class="form-control @error('min_task_per_day') is-invalid @enderror" placeholder="Minimum Task Per Day" min="1">
                                    @error('min_task_per_day')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="max_task_per_day" class="form-label required">Maximum Task Per Day</label>
                                    <input type="number" name="max_task_per_day" value="{{ old('max_task_per_day', $user->max_task_per_day ?? '') }}"
                                        class="form-control @error('max_task_per_day') is-invalid @enderror" placeholder="Maximum Task Per Day" min="1">
                                    @error('max_task_per_day')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Available Days and Status -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="available_days" class="form-label required">Available Days (Multi Select)</label>
                                    <select class="form-control filter" data-choices name="available_days[]" multiple
                                        id="choices-multiple-default">
                                        @php
                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                            $userDays = old('available_days', $user->available_days ?? []);
                                            if (is_string($userDays)) {
                                                $userDays = json_decode($userDays, true) ?? [];
                                            }
                                        @endphp
                                        @foreach ($days as $day)
                                            <option @if(in_array($day, $userDays)) selected @endif value="{{ $day }}">{{ $day }}</option>
                                        @endforeach
                                    </select>
                                    @error('available_days')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label required">Status</label>
                                    <select class="form-control filter" data-choices name="status"
                                        id="choices-status">
                                        <option value="" selected disabled>Select Status</option>
                                        <option @if(old('status', $user->status ?? '') == 'active') selected @endif value="active">Active</option>
                                        <option @if(old('status', $user->status ?? '') == 'inactive') selected @endif value="inactive">Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer border-top">
                        <button type="submit" class="btn btn-primary">@if (isset($user)) Update @else Create @endif</button>
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
                $(document).ready(function() {
                    // Any additional form logic can go here
                });
            }
        });
    </script>
@endsection