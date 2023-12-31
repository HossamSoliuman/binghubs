@extends('layouts.admin')
@section('content')
    <div class="row justify-content-center mt-4">
        <div class="col-md-11">
            <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#exampleModal">
                New Extract
            </button>
            {{-- <form action="{{ route('extractions.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="submit" value="submit">
            </form> --}}
            @include('extractions.filter')
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Extraction from type</th>
                        <th scope="col">Extraction from file</th>
                        <th scope="col">Output File</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($extractions as $extraction)
                        <tr>
                            <td>{{ $extraction->extracted_from_type }}</td>
                            <td>{{ basename($extraction->extracted_from) }}</td>
                            <td>{{ basename($extraction->extraction_result) }}</td>

                            <td class="d-flex">
                                <a href="{{ url($extraction->extraction_result) }}" download
                                    class="btn btn-info mr-1">Download</a>
                                <form action="{{ route('extractions.destroy', ['extraction' => $extraction->id]) }}" method="post">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-danger ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // JavaScript to show/hide the appropriate dropdown or file upload input
        const extractFromDropdown = document.getElementById('extract_from');
        const tableDropdown = document.getElementById('table-dropdown');
        const fileDropdown = document.getElementById('file-dropdown');
        const uploadFile = document.getElementById('upload-file');

        extractFromDropdown.addEventListener('change', function() {
            const selectedValue = extractFromDropdown.value;

            if (selectedValue === 'table') {
                tableDropdown.style.display = 'block';
                fileDropdown.style.display = 'none';
                uploadFile.style.display = 'none';
            } else if (selectedValue === 'existing_file') {
                tableDropdown.style.display = 'none';
                fileDropdown.style.display = 'block';
                uploadFile.style.display = 'none';
            } else if (selectedValue === 'uploaded_file') {
                tableDropdown.style.display = 'none';
                fileDropdown.style.display = 'none';
                uploadFile.style.display = 'block';
            }
        });
    </script>
@endsection
