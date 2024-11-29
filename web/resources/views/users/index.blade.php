@extends('layouts.app')

@section('content')
    <div>
        <a href="{{ url('users/create') }}" class="btn btn-primary float-start mt-1">Add User</a>
    </div>

    <table id="datatable" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if (!empty($user->getRoleNames()))
                            @foreach ($user->getRoleNames() as $rolename)
                                <label class="badge bg-primary mx-1">{{ $rolename }}</label>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        {{-- @can('update user') --}}
                        <a href="{{ url('users/' . $user->id . '/edit') }}" class="btn btn-success">Edit</a>
                        {{-- @endcan --}}

                        {{-- @can('delete user') --}}
                        <a href="{{ url('users/' . $user->id . '/delete') }}" class="btn btn-danger mx-2">Delete</a>
                        {{-- @endcan --}}
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
@endsection
