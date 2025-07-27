@extends('layouts.vertical', ['title' => 'Change Password'])

@section('css')
   
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('password-change') }}" method="post">
                    @csrf

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Change Password</h4>
                        <div class="card-tools">
                            {{-- <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary">
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
                             <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="old_password" class="form-label required">Old Password</label>
                                    <input type="password" name="old_password"
                                        class="form-control @error('old_password') is-invalid @enderror" placeholder="Old Password">
                                    @error('old_password')
                                        <div class="invalid-feedback" style="display: block;">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
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
                            <div class="col-lg-4">
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
                        $('select[name="role_name"]').on('change', function() {
                            const selectedValue = $(this).val();
                            console.log('Selected role:', selectedValue);
                            if(selectedValue!=="{{ App\Models\Role::COMMISSION_REQUIRED_ROLE }}"){
                                $('#commission-div').slideUp();
                                $('input[name="commission"]').val('');
                            }else{
                                $('#commission-div').slideDown();
                            }
                            // Perform actions here
                        });
                    });
                }
            });
    </script>

@endsection