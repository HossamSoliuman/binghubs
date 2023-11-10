<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">CSV Filters</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('files.filter', ['file' => $file->id]) }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="states">States (comma-separated):</label>
                        <input type="text" name="states" class="form-control" id="states"
                            placeholder="e.g., TX,FL">
                    </div>
                    <div class="form-group">
                        <label for="dnc">DNC:</label>
                        <select name="dnc" class="form-control" id="dnc">
                            <option value="All">All</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="min_age">Minimum Age:</label>
                        <input type="number" name="min_age" class="form-control" id="min_age" placeholder="e.g., 18">
                    </div>
                    <div class="form-group">
                        <label for="max_age">Maximum Age:</label>
                        <input type="number" name="max_age" class="form-control" id="max_age" placeholder="e.g., 65">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Apply Filters">
                </form>

            </div>
        </div>
    </div>
</div>
