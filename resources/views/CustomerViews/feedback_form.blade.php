<!-- feedback_form.blade.php -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customer.feedback.store') }}">
                @csrf
                <input type="hidden" name="package_id" id="package_id">


                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">
                        Submit Feedback For Package: <b><span id="modalPackageIdLabel"></span></b>
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select class="form-control" id="rating" name="rating" required>
                            <option value=""disabled selected>Select Rating</option>
                            @for ($i = 0; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value=""disabled selected>Select Category</option>
                            <option value="Package">Package</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Service">Service</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Enter your feedback..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>
