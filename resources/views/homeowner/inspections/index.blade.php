@extends('layouts.homeowner')

@section('title', 'My Inspections')

@section('content')
<style>
    .status-badge {
    display: inline-block;
    padding: 0.25rem 0.6rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 0.375rem;
    color: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    text-transform: capitalize;
}
.status-scheduled {
    background: #0dcaf0;
}

.status-pending_schedule {
    background: #ffc107;
    color: #212529;
}

.status-completed {
    background: #198754;
}

.status-default {
    background: #6c757d;
}
.btn-action {
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    text-transform: uppercase;
    transition: background-color 0.3s ease;
}

</style>
<div class="container">

    <h1 class="mb-4">My Inspections</h1>
    <p>Here is a list of your past and upcoming inspections.</p>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
        <a href="{{route("homeowner.inspections.schedule",-1)}}" class="btn btn-primary mb-3">Create </a>
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover table-bordered" id="inspections-table">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Property Address</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {

    $('#inspections-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('homeowner.inspections.data') }}",
        columns: [
            { data: 'trigger_type', name: 'trigger_type' },
            { data: 'property_address', name: 'property_address' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, 'asc']],
        language: {
            search: "Filter: ",
            processing: "Loading...",
            paginate: {
                previous: "<",
                next: ">"
            }
        },
        dom: '<"d-flex justify-content-between mb-2"lf>t<"d-flex justify-content-between mt-2"ip>'
    });

});
</script>
@endpush
