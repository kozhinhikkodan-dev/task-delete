@extends('layouts.vertical', ['title' => 'Customer ' . (isset($customer) ? 'Edit' : 'Add')])

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
                <form action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}" method="post">
                    @csrf
                    @method(isset($customer) ? 'PUT' : 'POST')

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ isset($customer) ? 'Edit Customer' : 'Create New Customer' }}</h4>
                        <div class="card-tools">
                            <a data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Back to Customers"
                                href="{{ route('customers.index') }}" class="btn btn-sm btn-primary">
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
                                    <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}"
                                        class="form-control @error('name') is-invalid @enderror" placeholder="Customer Name">
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
                                    <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
                                        class="form-control @error('email') is-invalid @enderror" placeholder="Email">
                                    @error('email')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Phone and Address -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}"
                                        class="form-control @error('phone') is-invalid @enderror" placeholder="Phone Number">
                                    @error('phone')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" placeholder="Address" rows="3">{{ old('address', $customer->address ?? '') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- City and State -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" name="city" value="{{ old('city', $customer->city ?? '') }}"
                                        class="form-control @error('city') is-invalid @enderror" placeholder="City">
                                    @error('city')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" name="state" value="{{ old('state', $customer->state ?? '') }}"
                                        class="form-control @error('state') is-invalid @enderror" placeholder="State">
                                    @error('state')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- ZIP Code and Country -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="zip_code" class="form-label">ZIP Code</label>
                                    <input type="text" name="zip_code" value="{{ old('zip_code', $customer->zip_code ?? '') }}"
                                        class="form-control @error('zip_code') is-invalid @enderror" placeholder="ZIP Code">
                                    @error('zip_code')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" name="country" value="{{ old('country', $customer->country ?? '') }}"
                                        class="form-control @error('country') is-invalid @enderror" placeholder="Country">
                                    @error('country')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Service Start Date and Service Renew Date -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="service_start_date" class="form-label">Service Start Date</label>
                                    <input type="date" name="service_start_date" value="{{ old('service_start_date', (isset($customer) && $customer->service_start_date) ? $customer->service_start_date->format('Y-m-d') : '') }}"
                                        class="form-control @error('service_start_date') is-invalid @enderror">
                                    @error('service_start_date')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="service_renew_date" class="form-label">Service Renewal Date</label>
                                    <input type="date" name="service_renew_date" value="{{ old('service_renew_date', (isset($customer) && $customer->service_renew_date) ? $customer->service_renew_date->format('Y-m-d') : '') }}"
                                        class="form-control @error('service_renew_date') is-invalid @enderror">
                                    @error('service_renew_date')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label required">Status</label>
                                    <select class="form-control filter" data-choices name="status"
                                        id="choices-status">
                                        <option value="" selected disabled>Select Status</option>
                                        <option @if(old('status', $customer->status ?? '') == 'active') selected @endif value="active">Active</option>
                                        <option @if(old('status', $customer->status ?? '') == 'inactive') selected @endif value="inactive">Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <div class="row">
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label for="total_posters   " class="form-label">Total Posters</label>
                                <input type="number" name="total_posters" value="{{ old('total_posters', $customer->total_posters ?? 0) }}"
                                    class="form-control @error('total_posters') is-invalid @enderror">
                                @error('total_posters')
                                    <div class="invalid-feedback" style="display: block;">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                    </div>

                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label for="total_video_edits" class="form-label">Total Video Edits</label>
                                <input type="number" name="total_video_edits" id="total_video_edits"
                                       value="{{ old('total_video_edits', $customer->total_video_edits ?? 0) }}"
                                       class="form-control @error('total_video_edits') is-invalid @enderror">
                                @error('total_video_edits')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label for="total_blog_posts" class="form-label">Total Blog Posts</label>
                                <input type="number" name="total_blog_posts" id="total_blog_posts"
                                       value="{{ old('total_blog_posts', $customer->total_blog_posts ?? 0) }}"
                                       class="form-control @error('total_blog_posts') is-invalid @enderror">
                                @error('total_blog_posts')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label for="total_anchoring_video" class="form-label">Total Anchoring Video</label>
                                <input type="text" name="total_anchoring_video" id="total_anchoring_video"
                                       value="{{ old('total_anchoring_video', $customer->total_anchoring_video ?? 0) }}"
                                       class="form-control @error('total_anchoring_video') is-invalid @enderror">
                                @error('total_anchoring_video')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <h5 class="fw-bold mb-4">ASSIGN TASK</h5>

                            {{-- Posters --}}
                            <div class="row align-items-start mb-4">
                                <div class="col-lg-2"></div>
                                <div class="col-lg-3 text-primary fw-bold">POSTERS</div>
                                <div class="col-lg-7">
                                    <label for="posters_assigned" class="form-label">Assigned To</label>
                                    <select name="posters_assigned" id="posters_assigned" class="form-control">
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            @php
                                                $isSelected = old('posters_assigned', $existingAssignments['posters_assigned'] ?? '') == $user->id;
                                            @endphp
                                            <option value="{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Video Edits --}}
                            <div class="row align-items-start mb-4">
                                <div class="col-lg-2"></div>
                                <div class="col-lg-3 text-primary fw-bold">VIDEO EDITS</div>
                                <div class="col-lg-7">
                                    <label for="video_edits_assigned" class="form-label">Assigned To</label>
                                    <select name="video_edits_assigned" id="video_edits_assigned" class="form-control">
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            @php
                                                $isSelected = old('video_edits_assigned', $existingAssignments['video_edits_assigned'] ?? '') == $user->id;
                                            @endphp
                                            <option value="{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Blog Posts --}}
                            <div class="row align-items-start mb-4">
                                <div class="col-lg-2"></div>
                                <div class="col-lg-3 text-primary fw-bold">BLOG POSTS</div>
                                <div class="col-lg-7">
                                    <label for="blog_posts_assigned" class="form-label">Assigned To</label>
                                    <select name="blog_posts_assigned" id="blog_posts_assigned" class="form-control">
                                        <option value="">Select User</option>
                                            @foreach($users as $user)
                                                @php
                                                    $isSelected = old('blog_posts_assigned', $existingAssignments['blog_posts_assigned'] ?? '') == $user->id;
                                                @endphp
                                                <option value="{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Anchoring Video --}}
                            <div class="row align-items-start mb-4">
                                <div class="col-lg-2"></div>
                                <div class="col-lg-3 text-primary fw-bold">ANCHORING VIDEO</div>
                                <div class="col-lg-7">
                                    <label for="anchoring_video_assigned" class="form-label">Assigned To</label>
                                    <select name="anchoring_video_assigned" id="anchoring_video_assigned" class="form-control">
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            @php
                                                $isSelected = old('anchoring_video_assigned', $existingAssignments['anchoring_video_assigned'] ?? '') == $user->id;
                                            @endphp
                                            <option value="{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card-footer border-top">
                        <button type="submit" class="btn btn-primary">@if (isset($customer)) Update @else Create @endif</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        // Initialize choices
        const choicesInstance = new Choices('#choices-single-default', {
            searchEnabled: false,
            allowHtml: true,
            allowHTML: true,
            removeItems: false,
            removeItemButton: false,
        });
    </script>
@endsection
