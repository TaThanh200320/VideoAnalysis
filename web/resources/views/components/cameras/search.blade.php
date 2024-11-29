@props(['sort', 'search', 'groupId', 'positionId', 'taskId'])

{{-- Add Cam --}}
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Create</label>
    <a href="{{ route('cameras.create') }}" class="btn btn-primary w-full">Add Camera</a>
</div>

<!-- Search -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
    <input type="text" wire:model.live="search" placeholder="Search cameras..."
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
</div>

<!-- Group Filter -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Group</label>
    <select wire:model.live="groupId"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <option value="">All Groups</option>
        @foreach ($this->groups as $group)
            <option value="{{ $group->id }}">{{ $group->ten }}</option>
        @endforeach
    </select>
    {{-- <select wire:model.live="groupId" class="select2_search form-select">
        <option value="" disabled selected></option>
        @foreach ($this->groups as $group)
            <option value="{{ $group->id }}">{{ $group->ten }}</option>
        @endforeach
    </select> --}}
</div>

<!-- Position Filter -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
    <select wire:model.live="positionId"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <option value="">All Positions</option>
        @foreach ($this->positions as $position)
            <option value="{{ $position->id }}">{{ $position->ten }}</option>
        @endforeach
    </select>
</div>

<!-- Task Filter -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Task</label>
    <select wire:model.live="taskId"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <option value="">All Tasks</option>
        @foreach ($this->tasks as $task)
            <option value="{{ $task->id }}">{{ $task->ten }}</option>
        @endforeach
    </select>
</div>

<!-- Sort Option -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
    <div class="flex space-x-4">
        <button
            class="{{ $sort === 'desc' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500' }} px-3 py-2 rounded-md text-sm font-medium"
            wire:click="setSort('desc')">Newest</button>
        <button
            class="{{ $sort === 'asc' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500' }} px-3 py-2 rounded-md text-sm font-medium"
            wire:click="setSort('asc')">Oldest</button>
    </div>
</div>

<!-- Clear Filters -->
@if ($search || $groupId || $positionId || $taskId || $sort)
    <button wire:click="clearFilters()"
        class="w-full bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-2 rounded-md text-sm font-medium">
        Clear Filters
    </button>
@endif
