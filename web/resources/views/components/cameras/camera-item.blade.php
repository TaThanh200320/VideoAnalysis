@props(['camera'])
<div class="bg-white rounded-md shadow overflow-hidden">
    <!-- Camera Preview (placeholder) -->
    <div class="aspect-video bg-gray-100 flex items-center justify-center">
        <img src="http://localhost:15440/get_snapshot/{{ $camera->id }}" alt="Last Frame" class="w-full h-full">
    </div>

    <!-- Camera Info -->
    <div class="p-3">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $camera->ten }}</h3>

        <div class="space-y-2 text-sm text-gray-600">
            <div class="flex items-center">
                <span class="font-medium mr-2">Name:</span>
                {{ $camera->name ?? 'N/A' }}
            </div>
            <div class="flex items-center">
                <span class="font-medium mr-2">Position:</span>
                {{ $camera->position->ten ?? 'N/A' }}
            </div>
            <div class="flex items-center">
                <span class="font-medium mr-2">Status:</span>

                @switch($camera->status)
                    @case(0)
                        <span
                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-red-100 text-red-800">Inactive</span>
                    @break

                    @case(1)
                        <span
                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-green-100 text-green-800">Active</span>
                    @break

                    @default
                        <span
                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-yellow-100 text-yellow-800">Not
                            configured</span>
                @endswitch
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-2 flex items-center justify-end space-x-2">
            {{-- @can('update camera') --}}
            <a class="nav-link" href="{{ url('cameras/' . $camera->id . '/edit') }}">
                <i class="fa-solid fa-pencil"></i>
            </a>
            {{-- @endcan --}}

            {{-- @can('delete camera') --}}
            <a class="nav-link" href="{{ url('cameras/' . $camera->id . '/delete') }}">
                <svg class="!w-[24px] !h-[24px]">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-trash"></use>
                </svg>
            </a>
            {{-- @endcan --}}
        </div>
    </div>
</div>
