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
                    <br>
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
                        <input type="number" name="filter[min_age]" class="form-control" id="min_age"
                            placeholder="e.g., 18">
                    </div>
                    <div class="form-group">
                        <label for="filter[max_age]">Maximum Age:</label>
                        <input type="number" name="filter[max_age]" class="form-control" id="max_age"
                            placeholder="e.g., 65">
                    </div>

                    <!-- New filters for credit score, income_range, and gender -->
                    <div class="form-group">
                        <label>Credit:</label>
                        <div>
                            <label><input type="checkbox" name="filter[credit][]" value="under_499"> Under
                                499</label>
                            <label><input type="checkbox" name="filter[credit][]" value="500-549"> 500-549</label>
                            <label><input type="checkbox" name="filter[credit][]" value="550-599"> 550-599</label>
                            <label><input type="checkbox" name="filter[credit][]" value="600-649"> 600-649</label>
                            <label><input type="checkbox" name="filter[credit][]" value="650-699"> 650-699</label>
                            <label><input type="checkbox" name="filter[credit][]" value="700-749">
                                700-749</label>
                            <label><input type="checkbox" name="filter[credit][]" value="750-799">
                                750-799</label>
                            <label><input type="checkbox" name="filter[credit][]" value="800+"> 800+</label>
                            <label><input type="checkbox" name="filter[credit][]" value="Unknown">
                                Unknown</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Income:</label>
                        <div>
                            <label><input type="checkbox" name="filter[income_range][]" value="Under $10000"> Under
                                $10000</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$10000 - $14999">
                                $10000 - $14999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$15000 - $19999">
                                $15000 - $19999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$20000 - $24999">
                                $20000 - $24999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$25000 - $29999">
                                $25000 - $29999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$30000 - $34999">
                                $30000 - $34999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$35000 - $39999">
                                $35000 - $39999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$40000 - $44999">
                                $40000 - $44999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$45000 - $49999">
                                $45000 - $49999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$50000 - $54999">
                                $50000 - $54999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$55000 - $59999">
                                $55000 - $59999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$60000 - $64999">
                                $60000 - $64999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$65000 - $74999">
                                $65000 - $74999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$75000 - $99999">
                                $75000 - $99999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$100000 - $149999">
                                $100000 - $149999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$150000 - $174999">
                                $150000 - $174999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$175000 - $199999">
                                $175000 - $199999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$200000 - $249999">
                                $200000 - $249999</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="$250000 +"> $250000
                                +</label>
                            <label><input type="checkbox" name="filter[income_range][]" value="Unknown">
                                Unknown</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gender:</label>
                        <div>
                            <label><input type="checkbox" name="filter[gender][]" value="M"> M</label>
                            <label><input type="checkbox" name="filter[gender][]" value="F"> F</label>
                            <label><input type="checkbox" name="filter[gender][]" value="U"> U</label>
                        </div>
                    </div>

                    <!-- ... (remaining HTML code) ... -->

                    <div class="modal-footer"> <button type="button" class="btn btn-sm btn-dark"
                            id="applyFiltersBtn">Apply Filters</button>
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
