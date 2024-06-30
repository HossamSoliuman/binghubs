@extends('layouts.admin')
@section('content')
    <div class="row justify-content-center mt-3">
        <div class="col-md-11">
            <h1>Tables</h1>
            <button type="button" class="btn btn-sm btn-dark" data-toggle="modal" data-target="#staticBackdrop">
                Create a new table
            </button>

            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1"
                role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">New Table</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('tables.store') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="Table Name"
                                        required>
                                </div>
                                <div class="form-group">
                                    <textarea name="description" class="form-control" placeholder="Table Description" required></textarea>
                                </div>
                                <div id="fields-container">
                                    <div class="field-row">
                                        <div class="form-row mb-1">
                                            <div class="col">
                                                <input type="text" name="fields[]" class="form-control"
                                                    placeholder="Field Name" required>
                                            </div>
                                            <div class="col">
                                                <select name="field_types[]" class="form-control" required>
                                                    <option value="string">String</option>
                                                    <option value="text">Text</option>
                                                    <option value="int">Integer</option>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <select name="field_indexed[]" class="form-control" required>
                                                    <option value="default">Default</option>
                                                    <option value="indexed">Indexed</option>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <button type="button" class="btn btn-sm btn-danger remove-field"> <i
                                                        class="bi bi-x-lg"></i> </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-dark" id="add-field"><i class="bi bi-plus-square"></i>  Add Field</button>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-sm btn-dark">Submit</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Description</th>
                        <th>Number of records</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tables as $table)
                        <tr>
                            <td>{{ $table->name }}</td>
                            <td>{{ $table->description }}</td>
                            <td>
                                {{ $table->record_count }}
                            </td>

                            <td class="d-flex">
                                <form action="{{ route('tables.duplicates', ['table' => $table->id]) }}" method="get"
                                    class="mr3">
                                    <input class="btn btn-primary mr-2" type="submit" value="Remove Duplicates">
                                </form>
                                <form action="{{ route('tables.destroy', ['table' => $table->id]) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <script>
        document.getElementById('add-field').addEventListener('click', function() {
            const fieldsContainer = document.getElementById('fields-container');
            const fieldRow = document.querySelector('.field-row').cloneNode(true);
            fieldsContainer.appendChild(fieldRow);
        });

        document.addEventListener('click', function(event) {
            if (event.target && event.target.classList.contains('remove-field')) {
                event.target.closest('.field-row').remove();
            }
        });
    </script>
@endsection
