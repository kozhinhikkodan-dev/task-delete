@extends('layouts.vertical', ['title' => 'Customer Details'])

@section('content')

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <div class="avatar-xxl mx-auto mb-3">
                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-48">
                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                        </div>
                    </div>
                    <h4 class="mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted mb-0">{{ $customer->email }}</p>
                    <div class="mt-3">
                        @if($customer->status == 'active')
                            <span class="badge bg-success-subtle text-success px-3 py-2">Active</span>
                        @elseif($customer->status == 'inactive')
                            <span class="badge bg-danger-subtle text-danger px-3 py-2">Inactive</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning px-3 py-2">{{ ucfirst($customer->status) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-calendar text-primary me-2"></i>Service Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Service Start Date</label>
                            <p class="text-muted mb-0">
                                {{ $customer->service_start_date ? $customer->service_start_date->format('M d, Y') : 'Not set' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-0">
                            <label class="form-label fw-medium text-dark">Service Renewal Date</label>
                            <p class="text-muted mb-0">
                                {{ $customer->service_renew_date ? $customer->service_renew_date->format('M d, Y') : 'Not set' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-cog text-primary me-2"></i>Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('update', $customer)
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i> Edit Customer
                        </a>
                    @endcan
                    
                    @can('delete', $customer)
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this customer?')">
                                <i class="bx bx-trash me-1"></i> Delete Customer
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-user text-primary me-2"></i>Customer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Full Name</label>
                            <p class="text-muted mb-0">{{ $customer->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Email Address</label>
                            <p class="text-muted mb-0">
                                <a href="mailto:{{ $customer->email }}" class="text-primary">{{ $customer->email }}</a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Phone Number</label>
                            <p class="text-muted mb-0">
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="text-primary">{{ $customer->phone }}</a>
                                @else
                                    Not provided
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Status</label>
                            <p class="text-muted mb-0">{{ ucfirst($customer->status) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-map text-primary me-2"></i>Address Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Address</label>
                            <p class="text-muted mb-0">{{ $customer->address ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">City</label>
                            <p class="text-muted mb-0">{{ $customer->city ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">State</label>
                            <p class="text-muted mb-0">{{ $customer->state ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Zip Code</label>
                            <p class="text-muted mb-0">{{ $customer->zip_code ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Country</label>
                            <p class="text-muted mb-0">{{ $customer->country ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-time text-primary me-2"></i>Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Created At</label>
                            <p class="text-muted mb-0">{{ $customer->created_at->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark">Last Updated</label>
                            <p class="text-muted mb-0">{{ $customer->updated_at->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 