@extends('layouts.vertical', ['title' => 'Customer List'])


@section('card-tools-add-btn')
    <div>
        <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary">
            <i class="bx bx-plus me-1"></i>Create Customer
        </a>
    </div>
@endsection

@section('filters')
    <div class="row g-3">
        <div class="col-md-2">
            <select class="form-control filter" data-choices name="status" id="choices-status">
                <option value="" selected>All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-control filter" data-choices name="city" id="choices-city">
                <option value="" selected>All Cities</option>
                @foreach(\App\Models\Customer::whereNotNull('city')->distinct()->pluck('city')->sort() as $city)
                    <option value="{{ $city }}">{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-control filter" data-choices name="state" id="choices-state">
                <option value="" selected>All States</option>
                @foreach(\App\Models\Customer::whereNotNull('state')->distinct()->pluck('state')->sort() as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-control filter" data-choices name="country" id="choices-country">
                <option value="" selected>All Countries</option>
                @foreach(\App\Models\Customer::whereNotNull('country')->distinct()->pluck('country')->sort() as $country)
                    <option value="{{ $country }}">{{ $country }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-control filter" data-choices name="service_status" id="choices-service-status">
                <option value="" selected>All Service Status</option>
                <option value="active">Active (30+ days)</option>
                <option value="expiring_soon">Expiring Soon (30 days)</option>
                <option value="expired">Expired</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#dateFilters" aria-expanded="false" aria-controls="dateFilters">
                <i class="bx bx-calendar"></i> Date Filters
            </button>
        </div>
    </div>
    
    <div class="collapse mt-3" id="dateFilters">
        <div class="card card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="mb-2">Service Start Date</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">From</label>
                            <input type="date" class="form-control filter" name="service_start_from" id="service-start-from">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">To</label>
                            <input type="date" class="form-control filter" name="service_start_to" id="service-start-to">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2">Service Renewal Date</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">From</label>
                            <input type="date" class="form-control filter" name="service_renew_from" id="service-renew-from">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">To</label>
                            <input type="date" class="form-control filter" name="service_renew_to" id="service-renew-to">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <x-datatable ajax-url="{{ route('customers.index') }}" id="customers-table" :per-page="7" title="All Customers List" />
@endsection 