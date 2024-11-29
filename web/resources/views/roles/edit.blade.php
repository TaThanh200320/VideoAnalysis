@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @if ($errors->any())
                    <ul class="alert alert-warning">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h4>Edit Role: {{ $role->name }}
                            <a href="{{ route('roles.index') }}" class="btn btn-danger float-end">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.update', $role->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" value="{{ $role->name }}" class="form-control"
                                    required />
                            </div>

                            <div class="mb-3">
                                <label for="permissions">Permission</label>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Model</th>
                                            <th>View</th>
                                            <th>Create</th>
                                            <th>Update</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $models = [
                                                'role' => ['view role', 'create role', 'update role', 'delete role'],
                                                'user' => ['view user', 'create user', 'update user', 'delete user'],
                                            ];
                                        @endphp

                                        @foreach ($models as $model => $permissions)
                                            <tr>
                                                <td>{{ ucfirst($model) }}</td>
                                                @foreach ($permissions as $perm)
                                                    @php
                                                        $permId = $permissions = DB::table('permissions')
                                                            ->where('name', $perm)
                                                            ->value('id');
                                                        $isChecked = in_array($permId, $rolePermissions)
                                                            ? 'checked'
                                                            : '';
                                                    @endphp
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="perm-{{ $perm }}" name="permissions[]"
                                                                value="{{ $permId }}" {{ $isChecked }}>
                                                            <label class="form-check-label" for="perm-{{ $perm }}">
                                                                {{ ucfirst(explode(' ', $perm)[0]) }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
