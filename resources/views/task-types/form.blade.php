@extends('layouts.vertical', ['title' => 'Task Type ' . (isset($taskType) ? 'Edit' : 'Add')])

@section('css')
    @vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ isset($taskType) ? route('task-types.update', $taskType->id) : route('task-types.store') }}" method="post">
                    @csrf
                    @method(isset($taskType) ? 'PUT' : 'POST')

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Task Type Information</h4>
                        <div class="card-tools">
                            <a data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Back to Task Types"
                                href="{{ route('task-types.index') }}" class="btn btn-sm btn-primary">
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
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="task-type-name" class="form-label required">Task Type</label>
                                    <input type="text" name="name" value="{{ old('name', $taskType->name ?? '') }}"
                                        id="task-type-name" class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Enter task type name">
                                    @error('name')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="task-type-description" class="form-label">Description</label>
                                    <textarea name="description" id="task-type-description" rows="3"
                                        class="form-control @error('description') is-invalid @enderror"
                                        placeholder="Enter description">{{ old('description', $taskType->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="task-type-status" class="form-label required">Status</label>
                                    <select class="form-control filter" data-choices name="status" id="task-type-status">
                                        <option value="" selected disabled>Select Status</option>
                                        @foreach (App\Models\TaskType::getStatusOptions() as $key => $label)
                                            <option @if(old('status', $taskType?->status ?? 'active') == $key) selected @endif value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields with default values -->
                        <input type="hidden" name="base_rate" value="0">
                        <input type="hidden" name="estimated_time_minutes" value="60">
                        <input type="hidden" name="priority" value="medium">

                    </div>
                    <div class="card-footer border-top">
                        <button type="submit" class="btn btn-primary">
                            @if (isset($taskType)) Update @else Create @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script-bottom')
    @vite(['resources/js/pages/app-ecommerce-product.js'])
@endsection 