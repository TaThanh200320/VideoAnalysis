@extends('layouts.app')
@section('links')
    <link rel="stylesheet" href="/css/codemirror.css">
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
            {{-- Form --}}
            <div>
                <form action="{{ url('cameras/' . $camera->id) }}" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2">
                        {{-- Draw ROI --}}
                        <div class="shadow-md p-2">
                            <h5>Region Of Interest</h5>
                            <div class="col-span-12">
                                <input type="hidden" name="mask" id='roiPoints'>
                                @include('cameras.partials.polygon')
                            </div>
                        </div>

                        {{-- Metadata --}}
                        <div class="shadow-inner p-2 relative">
                            <h5>Metadata</h5>
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-1">
                                    <label for="id" class="form-label">Id</label>
                                    <input type="text" class="form-control" id="id" name="id"
                                        value="{{ $camera->id }}" placeholder="1">
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>

                                <div class="col-span-5">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ $camera->name }}" placeholder="MKK052 Pano 7000">
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="select2_search form-control" name="status">
                                        <option value="" selected disabled></option>
                                        <option {{ $camera->status == 0 ? 'selected' : '' }} value=0>Stop</option>
                                        <option {{ $camera->status == 1 ? 'selected' : '' }} value=1>Run</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a valid status.
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label for="model_id" class="form-label">Model</label>
                                    <select class="select2_search form-control" name="model_id">
                                        <option value="" selected disabled></option>
                                        @foreach ($models as $model)
                                            <option {{ $camera->model->id == $model->id ? 'selected' : '' }}
                                                value="{{ $model->id }}">{{ $model->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a valid model.
                                    </div>
                                </div>
                                <div class="col-span-12">
                                    <label for="stream_url" class="form-label">Url</label>
                                    <input type="text" class="form-control" id="stream_url" name="stream_url"
                                        value="{{ $camera->stream_url }}" placeholder="The camera's url">
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="col-span-12">
                                    <label for="config" class="form-label">Config</label>
                                    <textarea id="config" name="config">{{ json_encode($camera->config, JSON_PRETTY_PRINT) }}</textarea>
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                            </div>
                            <div class="absolute bottom-0 right-0">
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        window.polygonData = {!! json_encode($camera) !!};
    </script>
    <script src="/js/polygon.js"></script>
    <script src="/js/codemirror.min.js"></script>
    <script src="/js/codemirrorjs.min.js"></script>
    <script src="js/select2.min.js"></script>
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
