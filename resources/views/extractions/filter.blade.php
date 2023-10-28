<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Filters</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('extractions.store') }}" method="post" enctype="multipart/form-data"
                    id="filterForm">
                    @csrf
                    <div class="mb-4">
                        <label for="extract_from">Select Source:</label>
                        <select class="form-control" name="extract_from_type" id="extract_from">
                            <option value="table" selected>Table</option>
                            <option value="existing_file">Existing File</option>
                            <option value="uploaded_file">Upload File</option>
                        </select>
                    </div>

                    <div class="form-group" id="tableDropdown">
                        <label for="table">Select Table:</label>
                        <select class="form-control" name="table">
                            @foreach ($tables as $table)
                                <option value="{{ $table->name }}">{{ $table->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: none" class="form-group" id="existingFileDropdown">
                        <label for="existing_file">Select File:</label>
                        <select class="form-control" name="existing_file">
                            @foreach ($files as $file)
                                <option value="{{ basename($file->output_file) }}">
                                    {{ basename($file->output_file) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: none" class="form-group" id="uploadFileDropdown">
                        <label for="file">Upload File:</label>

                        <div class="mb-4">
                            <p class="dropzone-text">Drag & drop your CSV file here or click to browse</p>
                            <input name="file" type="file" class="dropzone" id="file" required>
                        </div>
                    </div>

                    <h3>Filters</h3>
                    <div class="form-group">
                        <label for="states">States (comma-separated):</label>
                        <input type="text" name="filter[states]" class="form-control" id="states"
                            placeholder="e.g., TX,FL">
                    </div>
                    <div class="form-group">
                        <label for="dnc">DNC:</label>
                        <select class="form-control" name="filter[dnc]" id="dnc">
                            <option value="All">All</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter[min_age]">Minimum Age:</label>
                        <input type="number" name="filter[min_age]" class="form-control" id="min_age" placeholder="e.g., 18">
                    </div>
                    <div class="form-group">
                        <label for="filter[max_age]">Maximum Age:</label>
                        <input type="number" name="filter[max_age]" class="form-control" id="max_age" placeholder="e.g., 65">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="applyFiltersBtn">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to show/hide dropdowns based on the selected value
    document.getElementById("extract_from").addEventListener("change", function() {
        var selectedValue = this.value;

        // Show/hide the appropriate dropdowns based on the selected value
        document.getElementById("tableDropdown").style.display = selectedValue === "table" ? "block" : "none";
        document.getElementById("existingFileDropdown").style.display = selectedValue === "existing_file" ?
            "block" : "none";
        document.getElementById("uploadFileDropdown").style.display = selectedValue === "uploaded_file" ?
            "block" : "none";
    });

    // Function to submit the form when the "Apply Filters" button is clicked
    document.getElementById("applyFiltersBtn").addEventListener("click", function() {
        document.getElementById("filterForm").submit();
    });
</script>
