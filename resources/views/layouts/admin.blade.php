@extends('layouts.app')
@section('app-content')
    <div class="container-fluid">
        <div class="row">
            @include('layouts.sidebar')
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="min-height: 100vh;">
                @yield('content')
            </main>
        </div>
    </div>
@endsection
