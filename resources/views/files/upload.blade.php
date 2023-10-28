@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center mt-5">
            @if (session('success'))
                <div id="alert" class="alert alert-success alert-dismissible fixed-top fade show mx-auto" role="alert"
                    style="max-width: 500px;">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <script>
                    setTimeout(function() {
                        $('#alert').alert('close');
                    }, 2000);
                </script>
            @endif
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">Upload CSV File</div>
                    <div class="card-body">
                        <form action="{{ url('/match-csv') }}" method="POST" enctype="multipart/form-data"
                            id="upload-form">
                            @csrf

                            <div class="mb-3">
                                <label for="table" class="form-label">Select Database Table</label>
                                <select name="table" class="form-select" id="table" required>
                                    @foreach ($tables as $table)
                                        <option value="{{ $table->name }}">{{ $table->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <p class="dropzone-text">Drag & drop your CSV file here or click to browse</p>
                                <input name="file" type="file" class="dropzone" id="file" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Match CSV</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header">Upload CSV File</div>
                    <div class="card-body">
                        <form action="{{ url('/upload') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                            @csrf

                            <div class="mb-3">
                                <label for="table" class="form-label">Select Database Table</label>
                                <select name="table" class="form-select" id="table" required>
                                    @foreach ($tables as $table)
                                        <option value="{{ $table->name }}">{{ $table->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <p class="dropzone-text">Drag & drop your CSV file here or click to browse</p>
                                <input name="file" type="file" class="dropzone" id="file" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Upload into database</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->

        <div class="row justify-content-center mt-4">
            <div class="col-md-11">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Input File</th>
                            <th scope="col">Output File</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($files as $file)
                            <tr>
                                <td>{{ basename($file->input_file) }}</td>
                                <td>{{ basename($file->output_file) }}</td>

                                <td class="d-flex">
                                    <a href="{{ url($file->output_file) }}" download class="btn btn-info mr-1">Download</a>
                                    {{-- <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#exampleModal">
                                    Extract --}}
                                </button>
                                    <form action="{{ route('files.destroy', ['file' => $file->id]) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger ml-2">Delete</button>
                                    </form>

                                 
                                </td>
                            </tr>
                            {{-- @include('files.filter') --}}
                        @endforeach
                    </tbody>
                </table>
                {{ $files->links() }}
            </div>
        </div>
    </div>
    </div>
@endsection
