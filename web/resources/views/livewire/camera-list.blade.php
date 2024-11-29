<div class="px-4 h-full flex flex-col">
    <div class="grid grid-cols-12 gap-4 flex-1">
        <!-- Sidebar Filters - 3 cols -->
        <div class="h-[650px] col-span-2 bg-white rounded-md shadow p-4 relative">
            <button class="absolute top-1 right-2">
                <svg class="icon">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-menu"></use>
                </svg>
            </button>
            <x-cameras.search :sort="$this->sort" :search="$this->search" :groupId="$this->groupId" :positionId="$this->positionId"
                :taskId="$this->taskId" />
        </div>

        <!-- Camera Grid - 9 cols -->
        <div class="col-span-10 flex flex-col">
            <!-- Camera Grid -->
            <div class="grid grid-cols-4 gap-4">
                @foreach ($this->cameras as $camera)
                    <x-cameras.camera-item wire:key="{{ $camera->id }}" :camera="$camera" />
                @endforeach
            </div>

            <!-- Pagination - Now in a dedicated section with proper spacing -->
            <div class="py-6">
                {{ $this->cameras->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>
