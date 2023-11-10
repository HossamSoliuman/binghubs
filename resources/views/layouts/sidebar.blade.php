<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
    <div class="position-sticky">
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link btn btn-secondary btn-block mb-2" href="{{ route('index') }}">
                    Home
                </a>
                <a class="nav-link btn btn-secondary btn-block mb-2" href="{{ route('tables.index') }}">
                    Tables
                </a>
                <a class="nav-link btn btn-secondary btn-block mb-2" href="{{ route('extractions.index') }}">
                    Extraction
                </a>
                <form action="{{route('logout')}}" method="post">
                    @csrf
                    <button type="submit" class="nav-link btn btn-danger btn-block mb-2" >
                        Logout
                    </button>
                    </a>
                
                </form>
            </li>

        </ul>
    </div>
</nav>
