@extends('layouts.app')

@section('content')
    <div class="grid grid-rows-2 gap-2 h-[80vh]">
        <div class="relative h-full w-full overflow-hidden">
            <table id="datatableScroll" class="table table-striped w-full">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Error Code</th>
                        <th>Error Start Time</th>
                        <th>Error End Time</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $event)
                        <tr class="cursor-pointer event-row" data-event-id="{{ $event->id }}">
                            <td>{{ $event->id }}</td>
                            <td width="8%">{{ $event->error_code }}</td>
                            <td width="12%">{{ $event->start_error_time }}</td>
                            <td width="12%">{{ $event->end_error_time }}</td>
                            <td>{{ $event->description }}</td>
                            <td>
                                <a href="{{ url('events/' . $event->id . '/delete') }}" class="btn btn-danger mx-2"
                                    onclick="event.stopPropagation()">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="eventDetail" class="p-2 bg-white h-full w-full">
            <div class="no-event-selected text-center text-gray-500">
                Select an event to view details
            </div>

            <div class="event-detail-content hidden h-full">
                <div class="grid grid-cols-2 gap-4 h-full">
                    <div class="col-span-1 h-full">
                        <div class="relative h-full w-full bg-[#212631]">
                            <img id="detail-image" src="" alt="Picture"
                                class="absolute inset-0 w-full h-full object-contain">
                        </div>
                    </div>
                    <div class="col-span-1">
                        <h3 class="text-xl font-bold mb-4">Event Details</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="inline-flex">
                                <p class="font-semibold mr-2 mb-0">Error Code: </p>
                                <p id="detail-error-code" class="mb-0"></p>
                            </div>
                            <div class="inline-flex">
                                <p class="font-semibold mr-2 mb-0">Camera:</p>
                                <p id="detail-camera" class="mb-0"></p>
                            </div>
                            <div class="inline-flex">
                                <p class="font-semibold mr-2 mb-0">Start Time:</p>
                                <p id="detail-start-time" class="mb-0"></p>
                            </div>
                            <div class="inline-flex">
                                <p class="font-semibold mr-2 mb-0">End Time:</p>
                                <p id="detail-end-time" class="mb-0"></p>
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold mb-2 mt-4">Description:</p>
                            <div class="max-h-[200px] overflow-y-auto">
                                <p id="detail-description" class="break-words whitespace-pre-wrap"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedEventId = urlParams.get('selected');
            const calculateTableHeight = () => {
                const containerHeight = $('.grid').height();
                const rowHeight = containerHeight / 2;
                return rowHeight - 120;
            };

            const table = new DataTable("#datatableScroll", {
                paging: false,
                fixedColumns: true,
                scrollCollapse: true,
                scrollY: calculateTableHeight(),
                select: true
            });

            $('#datatableScroll tbody').on('click', 'tr', function() {
                const eventId = $(this).data('event-id');

                $('#datatableScroll tbody tr').removeClass('selected');
                $(this).addClass('selected');

                const newUrl = `${window.location.pathname}?selected=${eventId}`;
                history.pushState(null, '', newUrl);

                fetch(`/events/${eventId}`)
                    .then(response => response.json())
                    .then(event => {
                        $('.no-event-selected').addClass('hidden');
                        $('.event-detail-content').removeClass('hidden');

                        $('#detail-error-code').text(event.error_code);
                        $('#detail-start-time').text(event.start_error_time);
                        $('#detail-end-time').text(event.end_error_time);
                        $('#detail-description').text(event.description);
                        $('#detail-camera').text(event.camera['name']);
                        $('#detail-image').attr('src', event.url);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            if (selectedEventId) {
                const row = $(`#datatableScroll tbody tr[data-event-id="${selectedEventId}"]`);
                if (row.length) {
                    row.click();
                    row[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            $(window).resize(function() {
                table.draw();
                table.scrollY = calculateTableHeight();
            });
        });
    </script>
@endsection
