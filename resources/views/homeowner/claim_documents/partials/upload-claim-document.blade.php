<!-- Upload Document Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="uploadReportForm" 
              action="{{ route('homeowner.claim-documents.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="modal-content">

            @csrf

            <input type="hidden" name="home_id" 
                   value="{{ request('home_id') ?? optional(auth()->user()->homes()->first())->id }}">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Upload New Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">Document Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <!-- Document Type -->
                <div class="mb-3">
                    <label class="form-label">Document Type</label>
                    <select name="doc_type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="inspection">Inspection</option>
                        <option value="insurance">Insurance</option>
                        <option value="repair">Repair</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Document Date -->
                <div class="mb-3">
                    <label class="form-label">Document Date</label>
                    <input type="date" name="date_of_document" class="form-control" required>
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                </div>

                <!-- File Upload -->
                <div class="mb-3">
                    <label class="form-label">Upload File (Image / PDF / DOC)</label>
                    <input type="file" name="file" class="form-control">
                </div>

                <div class="text-center fw-bold">OR</div>

                <!-- External URL -->
                <div class="mb-3">
                    <label class="form-label">Paste File URL</label>
                    <input type="url" name="file_url" class="form-control" placeholder="https://example.com/document.pdf">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Upload Document</button>
            </div>

        </form>
    </div>
</div>



<!-- AJAX Upload Script -->
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
            "Accept": "application/json",   // Force JSON response
            "X-Requested-With": "XMLHttpRequest" // Prevent redirects
        },
        body: formData
    })
    .then(async response => {
        let data = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                // Validation errors
                Object.values(data.errors).forEach(err => {
                    toastr.error(err[0]);
                });
            } else {
                toastr.error(data.message || "An error occurred.");
            }
            return;
        }

        // Success
        toastr.success(data.message || "Document uploaded successfully!");

        form.reset();

        // Close modal
        let modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
        modal.hide();

        // Refresh list
        setTimeout(() => location.reload(), 600);
    })
    .catch(err => console.error(err));
});
</script>
