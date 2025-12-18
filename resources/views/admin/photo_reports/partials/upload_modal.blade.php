<div class="modal fade" id="uploadModal">
    <div class="modal-dialog modal-lg">
        <form id="uploadReportForm" action="{{ route('admin.photo-report.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Upload New Report</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Report Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                {{-- Inspection --}}
                <div class="mb-3">
                    <label class="form-label">Select Inspection</label>
                    <select name="inspection_id" class="form-select" required>
                        @foreach(\App\Models\Inspection::latest()->get() as $inspection)
                            <option value="{{ $inspection->id }}">
                                {{ $inspection->home->nickname??'' }} ({{ $inspection->home->address_line1 ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- File Upload --}}
                <div class="mb-3">
                    <label class="form-label">Upload File (Image / PDF / DOC)</label>
                    <input type="file" name="file" class="form-control">
                </div>

                <div class="text-center fw-bold">OR</div>

                {{-- External URL --}}
                <div class="mb-3">
                    <label class="form-label">Paste File URL</label>
                    <input type="url" name="file_url" class="form-control" placeholder="https://example.com/report.pdf">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Upload Report</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('uploadReportForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let form = this;
    let url = form.action;
    let formData = new FormData(form);

    toastr.clear();

    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json", // 🔥 force Laravel to return JSON
            "X-Requested-With": "XMLHttpRequest" // 🔥 prevents redirect
        },
        body: formData
    })
    .then(async response => {
        let data = await response.json();

        if (!response.ok) {
            // Validation errors
            if (response.status === 422) {
                Object.values(data.errors).forEach(err => {
                    toastr.error(err[0]);
                });
            } else {
                toastr.error(data.message || "An error occurred.");
            }
            return;
        }

        // Success
        toastr.success(data.message || "Report uploaded successfully!");

        form.reset();

        // Close modal
        let modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
        modal.hide();

        // Reload list
        setTimeout(() => location.reload(), 800);
    })
    .catch(err => console.error(err));
});
</script>
