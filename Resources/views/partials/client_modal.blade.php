<div class="modal" style="padding-top: 10rem;" tabindex="-1" role="dialog" data-backdrop="false" id="connect-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ __('Connect Client') }}</h4>
            </div>
            <form id="client-contact-form">
                <div class="modal-body">
                    <div class="client-search">
                        <div id="new-client-button" class="new-client-button hide"></div>
                        <div class="global-search">
                            <div class="global-search-input">
                                <input id="search-input" type="text" class="form-control border-0 search-input"
                                    placeholder="🔍  Search...">
                            </div>
                            <div id="global-search-results" class="global-search-container" style="display: none;">
                                <div class="global-search-results">
                                    <div id="client-results" class="search-result-category"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="new-client-contact" class="new-client-contact hide">
                        <h3>Create Client Contact</h3>
                        <div class="form-group mb-2">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" required />
                        </div>
                        <div class="form-group mb-2">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" required />
                        </div>
                        <div class="form-group mb-2">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" required />
                        </div>
                        <div class="form-group mb-2">
                            <label for="phone">Phone</label>
                            <input type="tel" class="form-control" id="phone" />
                        </div>
                        <div class="form-group mb-2">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" id="address" />
                        </div>
                        <div class="form-group mb-2">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" required />
                        </div>
                        <div class="form-group mb-2">
                            <label for="type">Type</label>
                            <input type="text" class="form-control" id="type" />
                        </div>
                        <div class="form-check form-switch mb-2 d-flex justify-content-start align-items-center">
                            <input class="form-check-input" type="checkbox" id="main" />
                            <label class="form-check-label ms-2" for="main">Main contact</label>
                        </div>
                        <div class="form-check form-switch mb-2 d-flex justify-content-start align-items-center">
                            <input class="form-check-input" type="checkbox" id="billing" />
                            <label class="form-check-label ms-2" for="billing">Billing contact</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancel-button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="add-client-contact-button" class="btn btn-primary hide" data-loading-text="Adding">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .new-client-button {
        padding-bottom: 10px;
    }

    .search-input.focus-visible .global-search-results {
        display: block;
    }

    .global-search-container {
        overflow: auto;
        position: relative;
        min-height: 150px;
    }

    .global-search-results {
        background-color: white;
        position: absolute;
        width: 100%;
        z-index: 1;
        padding: 10px;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
        border: 2px solid whitesmoke;
    }

    .global-search-results .search-result-item:hover {
        background-color: whitesmoke;
        border-radius: 5px;
    }

    .global-search-results .search-result-item.selected-result {
        background-color: lightgray;
        border-radius: 5px;
    }

    .search-item {
        cursor: pointer;
        padding: 5px;
    }

    .modal-body {
        min-height: 5px;
    }

    #conv-layout-customer {
        z-index: 1000;
    }
</style>