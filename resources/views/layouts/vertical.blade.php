<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.title-meta', ['title' => $title])
    @yield('css')
    @include('layouts.partials.head-css')
</head>

<body>

<div class="wrapper">

    @include("layouts.partials.topbar", ['title' => $title])
    @include('layouts.partials.main-nav')

    <div class="page-content">

        <div class="container-fluid">
            @include('common.blade.toastr')
            @yield('content')
        </div>

        @include("layouts.partials.footer")

    </div>

</div>

@include("layouts.partials.right-sidebar")
@vite(['resources/js/app.js','resources/js/layout.js'])
@include("layouts.partials.footer-scripts")

</body>

</html>
