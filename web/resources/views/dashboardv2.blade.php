@extends('layouts.app')

@section('content')
    {{-- Statistical --}}
    <div class="flex flex-col h-[calc(screen-300px)]">
        <div class="w-full border grid grid-cols-3">
            {{-- Camera Total --}}
            <div class="border p-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-gray-500 text-sm font-medium !mb-0">Total Camera</h3>
                    <div>
                        <span class="text-3xl font-bold">{{ $stats['total'] }}</span>
                        <span class="text-gray-400 text-sm ml-2">Camera</span>
                    </div>
                    <span class="rounded-full p-2 bg-blue-50">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Camera Running --}}
            <div class="border p-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-gray-500 text-sm font-medium !mb-0">Running</h3>
                    <div>
                        <span class="text-3xl font-bold text-green-500">{{ $stats['active'] }}</span>
                        @if ($stats['active'] !== 0)
                            <span class="text-gray-400 text-sm ml-2">
                                ({{ round(($stats['active'] / $stats['total']) * 100, 1) }}%)
                            </span>
                        @else
                            <span class="text-gray-400 text-sm ml-2">0%</span>
                        @endif
                    </div>
                    <span class="rounded-full p-2 bg-green-50">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Camera Offline --}}
            <div class="border p-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-gray-500 text-sm font-medium !mb-0">Offline</h3>
                    <div>
                        <span class="text-3xl font-bold text-red-500">{{ $stats['offline'] }}</span>
                        @if ($stats['offline'] !== 0)
                            <span class="text-gray-400 text-sm ml-2">
                                ({{ round(($stats['offline'] / $stats['total']) * 100, 1) }}%)
                            </span>
                        @else
                            <span class="text-gray-400 text-sm ml-2">
                                0%
                            </span>
                        @endif
                    </div>
                    <span class="rounded-full p-2 bg-red-50">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- Cameras and Events --}}
        <div class="h-full grid grid-cols-12 flex-1 overflow-hidden border">
            {{-- Camera Show --}}
            <div class="col-span-10 h-full">
                <div id="cameraContainer" class="h-full overflow-hidden flex items-center justify-center bg-gray-100">
                    <img id="cameraFrame" src="/images/blank.png" class="w-full h-[79vh]"></img>
                </div>
            </div>

            {{-- Events --}}
            <div class="col-span-2 border h-full p-3 overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <button id="selectCameras" class="px-3 py-1 bg-blue-500 text-white rounded">
                        <i class="fa-solid fa-video mr-2"></i>Add Cameras
                    </button>
                    <h5 class="text-lg font-bold">Events</h5>
                </div>
                <ul class="space-y-3">
                    @foreach ($events as $event)
                        <li>
                            <a href="{{ url('/events?selected=' . $event['id']) }}"
                                class="block bg-white border-l-4 border-yellow-500 rounded p-3 hover:bg-gray-50 transition-colors no-underline">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-gray-700">#{{ $event['id'] }}</span>
                                    <span class="text-xs bg-red-100 text-red-800 font-medium px-2 py-1 rounded">
                                        Error {{ $event['error_code'] }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-800 mb-2">{{ $event['description'] }}</p>
                                <div class="text-xs text-gray-500 flex justify-between">
                                    <span>{{ Carbon\Carbon::parse($event['start_error_time'])->format('H:i:s') }}</span>
                                    <span>Camera ID: {{ $event['camera_id'] }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Camera List Popup -->
    <div id="cameraListPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center">
        <div class="absolute top-[25%] left-[35%] bg-white p-4 rounded-lg w-1/3">
            <!-- Grid Layout Selection -->
            <div class="mb-4 bg-gray-50 rounded">
                <h5 class="text-md font-semibold mb-3">Grid Layout</h5>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Rows</label>
                        <input type="number" id="gridRows" min="1" value="1"
                            class="w-full px-3 py-2 border rounded focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Columns</label>
                        <input type="number" id="gridColumns" min="1" value="1"
                            class="w-full px-3 py-2 border rounded focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
                <div id="gridWarning" class="hidden mt-2 text-sm text-red-500">
                    Grid size must be larger than the number of selected cameras
                </div>
            </div>
            <!-- Select Camera Selection -->
            <div class="mb-4 bg-gray-50 rounded max-h-[40vh] overflow-y-auto">
                <h5 class="text-md font-semibold mb-3">Select Camera</h5>
                <div id="cameraList" class="">
                    @foreach ($cameras as $camera)
                        <div class="camera-item flex items-center p-2 hover:bg-gray-100">
                            <input type="checkbox" class="camera-checkbox mr-3" data-camera-id="{{ $camera->id }}">
                            <span class="mr-3"><i class="fa-solid fa-video"></i></span>
                            {{ $camera->name }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-4 flex justify-between">
                <button id="applySelection" class="px-4 py-2 bg-blue-500 text-white rounded">Apply</button>
                <button id="closeCameraList" class="px-4 py-2 bg-red-500 text-white rounded">Close</button>
            </div>
        </div>
    </div>
    <script>
        window.userPreferences = @json($userPreferences);
    </script>
    <script src="js/dashboard.js"></script>
@endsection
