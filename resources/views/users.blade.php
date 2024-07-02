@extends('layouts.admin')
@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center mt-5">
            <div class="col-md-11">
                <h1>Users</h1>
                <button type="button" class="mb-3 rounded btn btn-sm btn-dark" data-toggle="modal"
                    data-target="#staticBackdrop">
                    Create a new User
                </button>

                <!-- Creating Modal -->
                <div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1"
                    role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">New User</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('users.store') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <input type="text" name="name" id="name" class="form-control"
                                            placeholder="name" required>
                                    </div>

                                    <div class="form-group">
                                        <input type="text" name="email" id="email" class="form-control"
                                            placeholder="email" required>
                                    </div>

                                    <div class="form-group">
                                        <input type="text" name="password" id="password" class="form-control"
                                            placeholder="password" required>
                                    </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn rounded btn-sm btn-light">Submit</button>
                                <button type="button" class="btn rounded btn-sm btn-dark"
                                    data-dismiss="modal">Close</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Edit User Modal -->
                <div class="modal fade" id="editModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
                    role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="editForm" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')@csrf
                                    <div class="form-group">
                                        <input type="text" name="name" id="name" class="form-control"
                                            placeholder="name" required>
                                    </div>

                                    <div class="form-group">
                                        <input type="text" name="email" id="email" class="form-control"
                                            placeholder="email" required>
                                    </div>

                                    <div class="form-group">
                                        <input type="text" name="password" id="password" class="form-control"
                                            placeholder="password" required>
                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn rounded btn-sm btn-light" id="saveChangesBtn">Save
                                    Changes</button>
                                <button type="button" class="btn rounded btn-sm btn-dark"
                                    data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th> Name</th>
                            <th> Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr data-user-id="{{ $user->id }}">
                                <td class=" user-name">{{ $user->name }}</td>
                                <td class=" user-email">{{ $user->email }}</td>
                                <td class="d-flex">
                                    <button type="button" class="btn rounded btn-sm btn-light btn-edit"
                                        data-toggle="modal" data-target="#editModal">
                                        Edit
                                    </button>
                                    <form action="{{ route('users.destroy', ['user' => $user->id]) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 rounded btn btn-sm btn-dark">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.btn-edit').on('click', function() {
                var UserName = $(this).closest("tr").find(".user-name").text();
                $('#editModal input[name="name"]').val(UserName);
                var UserEmail = $(this).closest("tr").find(".user-email").text();
                $('#editModal input[name="email"]').val(UserEmail);
                var UserPassword = $(this).closest("tr").find(".user-password").text();
                $('#editModal input[name="password"]').val(UserPassword);
                var UserId = $(this).closest('tr').data('user-id');
                $('#editForm').attr('action', '/users/' + UserId);
                $('#editModal').modal('show');
            });
            $('#saveChangesBtn').on('click', function() {
                $('#editForm').submit();
            });
        });
    </script>
@endsection
