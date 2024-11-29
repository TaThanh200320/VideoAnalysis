@extends('layouts.app')
@section('links')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
@endsection

@section('content')
    <div class="h-[80%]">
        <div class="h-full">
            @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <div>
                <form action="{{ route('cameras.store') }}" method="POST">

                    @csrf
                    <div class="col-span-9">
                        <h5>Metadata</h5>
                        <div class="grid grid-cols-10 gap-3">
                            <div class="col-span-2">
                                <label for="id" class="form-label">ID</label>
                                <input type="number" class="form-control" id="id" name="id" value=""
                                    placeholder="1" required>
                                <div class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value=""
                                    placeholder="MKK052 Pano 7000" required>
                                <div class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label for="stream_url" class="form-label">Url</label>
                                <input type="text" class="form-control" id="stream_url" name="stream_url" value=""
                                    placeholder="The camera's url" required>
                                <div class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label for="model_id" class="form-label">Model</label>
                                <select required class="select2_search form-control" name="model_id">
                                    <option value="" selected disabled></option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Please select a valid model.
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label for="status" class="form-label">Status</label>
                                <select required class="select2_search form-control" name="status">
                                    <option selected value={{ 0 }}>Inactive</option>
                                    <option value={{ 1 }}>Active</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a valid status.
                                </div>
                            </div>
                            <div class="col-span-10">
                                <label for="config" class="form-label">Config</label>
                                <textarea id="config" name="config">{{ old('config') }}</textarea>
                                <div class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md w-full flex items-end justify-end my-3">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="js/select2.min.js"></script>
    <script src="/js/codemirror.min.js"></script>
    <script src="/js/codemirrorjs.min.js"></script>
    <script>
        var editor = CodeMirror.fromTextArea(document.getElementById("config"), {
            lineNumbers: true,
            mode: "javascript",
            theme: "default",
            viewportMargin: Infinity
        });

        $(document).ready(function() {
            $('.select2_search').select2({
                placeholder: "Select an option",
                allowClear: true
            });
        });

        $('form').submit(function() {
            $('#config').val(editor.getValue());
        });
    </script>
@endsection
