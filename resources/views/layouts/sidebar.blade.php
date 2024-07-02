@section('styles')
    <style>
        .nav-link-custom {
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: flex-start;
        }

        .nav-link-custom i {
            margin-top: 4px;
            margin-right: 8px;
        }

        .active-tab {
            background-color: #007bff;
            color: white;
        }
    </style>
@endsection

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky">
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link btn  btn-block mb-2 {{ request()->routeIs('index') ? 'btn-white' : 'btn-dark' }}"
                    href="{{ route('index') }}">
                    Home
                </a>
                <a class="nav-link btn btn-dark btn-block mb-2 {{ request()->routeIs('tables*') ? 'btn-white' : 'btn-dark' }}"
                    href="{{ route('tables.index') }}">
                    Tables
                </a>
                <a class="nav-link btn btn-dark btn-block mb-2 {{ request()->routeIs('extractions*') ? 'btn-white' : 'btn-dark' }}"
                    href="{{ route('extractions.index') }}">
                    Extraction
                </a>
                @if (auth()->user()->role == 'admin')
                    <a class="nav-link btn btn-dark btn-block mb-2 {{ request()->routeIs('users*') ? 'btn-white' : 'btn-dark' }}"
                        href="{{ route('users.index') }}">
                        Users
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="nav-link btn btn-dark btn-block mb-2">
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
