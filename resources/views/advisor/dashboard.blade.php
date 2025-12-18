@extends('layouts.advisor')
@section('title', 'Dashboard')
@section('content')

<div class="container my-4">

    <div class="card mb-4">
        <div class="card-header">
            <h3>New Inspection Opportunities</h3>
        </div>
        <div class="card-body">
            <table id="opportunities-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>City</th>
                        <th>Homeowner</th>
                        <th width="150px">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>My Assigned Inspections</h3>
        </div>
        <div class="card-body">
            <table id="assigned-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>Homeowner</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')

<script>
$(function() {
    $('#opportunities-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('advisor.dashboard.opportunities-data') }}',
        columns: [
            { data: 'home.address_line1', name: 'home.address_line1' },
            { data: 'city', name: 'city' },
            { data: 'homeowner.name', name: 'homeowner.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#assigned-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('advisor.dashboard.assigned-data') }}',
        columns: [
            { data: 'home.address_line1', name: 'home.address_line1' },
            { data: 'homeowner.name', name: 'homeowner.name' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush
