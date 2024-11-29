@extends('layouts.app')

@section('content')
    {{-- Statistical --}}
    <div class="w-full h-[60px] border grid grid-cols-3 bg">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    {{-- Cameras and Events --}}
    <div class="grid grid-cols-12 h-[80vh]">
        {{-- Camera Show --}}
        <div class="col-span-10 h-full">
            <div id="gridContainer" class="grid h-full">
            </div>
        </div>

        {{-- Events --}}
        <div class="col-span-2 border h-full p-3 overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <!-- Custom layout -->
                <div class="relative">
                    <i id="toggleLayout" class="fa-solid fa-table cursor-pointer"></i>
                    <div id="layoutOptions" class="hidden absolute bg-[#212631] w-[250px] h-[300px] py-3 px-6">
                        <h6 class="text-white">Standard Window Division</h6>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-white p-1 cursor-pointer layout-option" data-layout="1x1">
                                <div class="border border-black">1x1</div>
                            </div>
                            <div class="bg-white p-1 cursor-pointer layout-option" data-layout="2x2">
                                <div class="border border-black">2x2</div>
                            </div>
                            <div class="bg-white p-1 cursor-pointer layout-option" data-layout="2x3">
                                <div class="border border-black">2x3</div>
                            </div>
                            <div class="bg-white p-1 cursor-pointer layout-option" data-layout="3x4">
                                <div class="border border-black">3x4</div>
                            </div>
                            <div class="bg-white p-1 cursor-pointer layout-option" data-layout="4x4">
                                <div class="border border-black">4x4</div>
                            </div>
                        </div>
                    </div>
                </div>
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

    <!-- Camera List Popup -->
    <div id="cameraListPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center">
        <div class="absolute top-[25%] left-[35%] bg-white p-4 rounded-lg w-1/3 max-h-[50vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Select a Camera</h2>
            <div id="cameraList" class="max-h-[60vh] overflow-y-auto">
                @foreach ($cameras as $camera)
                    <div class="camera-item cursor-pointer p-2 hover:bg-gray-200" data-camera-id="{{ $camera->id }}">
                        <span class="mr-3"><i class="fa-solid fa-video"></i></span>
                        {{ $camera->name }}
                    </div>
                @endforeach
            </div>
            <button id="closeCameraList" class="mt-4 px-4 py-2 bg-red-500 text-white rounded">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleLayout = document.getElementById('toggleLayout');
            const layoutOptions = document.getElementById('layoutOptions');
            const gridContainer = document.getElementById('gridContainer');
            const cameraListPopup = document.getElementById('cameraListPopup');
            const closeCameraList = document.getElementById('closeCameraList');
            let currentLayout = '2x2';
            let selectedCell;

            function updateGridLayout() {
                const [rows, cols] = currentLayout.split('x');
                gridContainer.className = `grid h-full grid-rows-${rows} grid-cols-${cols}`;
                gridContainer.innerHTML = '';
                for (let i = 0; i < cols * rows; i++) {
                    const cell = document.createElement('div');
                    cell.className = 'border cursor-pointer flex items-center justify-center';
                    cell.innerHTML = '<i class="fa-solid fa-plus text-4xl text-gray-300"></i>';
                    cell.addEventListener('click', function() {
                        selectedCell = cell;
                        cameraListPopup.classList.remove('hidden');
                    });
                    gridContainer.appendChild(cell);
                }
            }

            gridContainer.addEventListener('click', function(e) {
                if (e.target.closest('.close-stream')) {
                    const cell = e.target.closest('.close-stream').parentElement.parentElement;
                    cell.innerHTML = '<i class="fa-solid fa-plus text-4xl text-gray-300"></i>';
                }
            });

            toggleLayout.addEventListener('click', function() {
                layoutOptions.classList.toggle('hidden');
            });

            document.querySelectorAll('.layout-option').forEach(option => {
                option.addEventListener('click', function() {
                    currentLayout = this.dataset.layout;
                    updateGridLayout();
                    layoutOptions.classList.add('hidden');
                });
            });

            closeCameraList.addEventListener('click', function() {
                cameraListPopup.classList.add('hidden');
            });

            document.querySelectorAll('.camera-item').forEach(camera => {
                camera.addEventListener('click', function() {
                    const cameraId = this.dataset.cameraId;
                    const streamUrl = `http://localhost:15440/recognized_stream/${cameraId}`;
                    if (selectedCell) {
                        selectedCell.innerHTML = `
                            <div class="relative w-full h-full overflow-hidden">
                                <img src="${streamUrl}" alt="Camera Stream" class="absolute inset-0 w-full h-full">
                            </div>
                        `;
                    }
                    cameraListPopup.classList.add('hidden');
                });
            });

            updateGridLayout();
        });
    </script>
@endsection
