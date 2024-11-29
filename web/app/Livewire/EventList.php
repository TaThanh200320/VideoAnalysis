<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EventList extends Component
{
    use WithPagination;

    #[Url()]
    public $search = '';
    #[Url()]
    public $sort = 'desc';

    public $page = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function setSort($sort)
    {
        $this->sort = ($sort === 'desc') ? 'desc' : 'asc';
    }

    #[On('search')]
    public function updateSearch($search)
    {
        $this->search = $search;
        $this->resetPage();
    }
    public function clearFilters()
    {
        $this->reset(['search', 'sort']);
        $this->resetPage();
    }

    #[Computed]
    public function events()
    {

        return Notification::published()
            ->with(['camera'])
            ->when($this->search, function ($query) {
                $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($this->search) . '%']);
            })
            ->orderBy('created_at', $this->sort)
            ->paginate(8);
    }

    public function render()
    {
        return view('livewire.event-list');
    }
}