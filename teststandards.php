<link  rel="stylesheet" href="css/quickbookslook.css">
<link  rel="stylesheet" href="js/quilljs/1.3.7/quill.snow.css">
<link  rel="stylesheet" href="css/reducedcss.css">
<link  rel="stylesheet" href="css/modalcss.css">
<link  rel="stylesheet" href="css/cooltables.css">
<link  rel="stylesheet" href="css/typing.css"> 
<link  rel="stylesheet" href="css/select2.min.css">
<link href="js/tom-select/tom-select.css" rel="stylesheet" type="text/css"/>
<style>
#standardsTable {
    border-collapse: collapse;     /* Remove double borders */
    width: 100%;
    table-layout: fixed;           /* Make columns evenly distributed */
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
}

/* Header styling */
#standardsTable thead th {
    background-color: #f3f6fb;     /* Light Excel-like header color */
    color: #333;
    font-weight: 600;
    text-align: left;
    border: 1px solid #d0d7de;     /* Excel-like gridline */
    padding: 6px 10px;
    white-space: nowrap;
}

/* Body cell styling */
#standardsTable tbody td {
    border: 1px solid #d0d7de;
    padding: 6px 10px;
    background-color: #fff;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Alternate row shading */
#standardsTable tbody tr:nth-child(even) {
    background-color: #f9fbfd;     /* Light blue-gray */
}

/* Hover effect like Excel highlight */
#standardsTable tbody tr:hover {
    background-color: #e6f2ff;
    cursor: pointer;
}

/* Make the table feel "editable" */
#standardsTable tbody td:focus-within {
    outline: 2px solid #4a90e2;
    background-color: #fff !important;
}

/* Scrollable long text inside cells */
.scrollable-content {
    max-height: 60px;
    overflow-y: auto;
    padding: 2px;
    border: 1px solid transparent;
}

/* Excel-like pagination buttons */
.pagination .page-link {
    color: #4a90e2;
    border-radius: 0;
    margin: 0 2px;
}
.pagination .active .page-link {
    background-color: #4a90e2;
    color: white;
    border-color: #4a90e2;
}
</style>

<div class="container mt-5">
    <h3 class="text-center">Test Standards Management</h3>
</div> 
  
<button id="uploadStandardsBtn" class="btn btn-secondary mb-3">
    <i class="fas fa-upload"></i> Upload Standards
</button>
<button id="addstandardmethodBtn" class="btn btn-secondary mb-3">
    <i class="fas fa-plus"></i> Add Standard Methods
</button>
    
<!-- File Upload Modal excle -->
<div id="uploadStandardsModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload"></i> Upload Test Standards
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-bs-target="#uploadStandardsModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadStandardsForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">
                            <i class="fas fa-file-excel"></i> Select Excel File
                        </label>
                        <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xls,.xlsx" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add New Standard Button -->
<!-- Modal -->
<div id="standardModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Test Standard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"  data-bs-target="#standardModal"  aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="standardForm">
                    <input type="hidden" id="StandardID" name="StandardID">
                        <div class="mb-3">
                            <label for="standardmethod" class="form-label">Standard Methods</label>
                            <input type="text" id="standardmethod"  name="standardmethod" class="iso-standard" placeholder="Enter ISO Standard...">
                       
                        </div>
                    <!-- Standard Code -->
                    <div class="mb-3">
                        <label for="Code" class="form-label">
                            <i class="fas fa-barcode"></i> 
                            Standard Code
                        </label>
                        <input type="text" class="form-control" id="Code" name="Code" placeholder="Enter standard code" required>
                    </div>

                    <!-- Standard Name -->
                    <div class="mb-3">
                        <label for="Name" class="form-label">
                            <i class="fas fa-tag"></i> 
                            Standard Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter standard name" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-info-circle"></i> 
                            Report Note
                        </label>
                        <div class="quill-editor" id="description" name="description"></div>
                    </div>

                    <!-- Applicable Regulation -->
                    <div class="mb-3">
                        <label for="ApplicableRegulation" class="form-label">
                            <i class="fas fa-balance-scale"></i> 
                            Applicable Regulation
                        </label>
                         <div class="quill-editor"  id="ApplicableRegulation" name="ApplicableRegulation" ></div>
                    </div>
                        
                    <!-- Save Button -->
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save 
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="standardcloneModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Clone Test Standard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"  data-bs-target="#standardcloneModal"  aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="standardCloneForm">
                    <input type="hidden" id="StandardCloneID" name="StandardCloneID">
                    <p id="StandardClonename"></p>
                    <div class="mb-3">
                            <label for="standardmethod" class="form-label">Standard Methods</label>
                       
                            <input type="text" id="Clonestandardmethod"  name="standardmethod" class="iso-standard" placeholder="Enter ISO Standard...">
                        </div>
                    <!-- Standard Code -->
                    <div class="mb-3">
                        <label for="Code" class="form-label">
                            <i class="fas fa-barcode"></i> 
                            Standard Code
                        </label>
                        <input type="text" class="form-control" id="CloneCode" name="Code" placeholder="Enter standard code" required>
                    </div>

                    <!-- Standard Name -->
                    <div class="mb-3">
                        <label for="Name" class="form-label">
                            <i class="fas fa-tag"></i> 
                            Standard Name
                        </label>
                        <input type="text" class="form-control" id="Clonename" name="name" placeholder="Enter standard name" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-info-circle"></i> 
                            Report Note
                        </label>
                        <div class="quill-editor" id="Clonedescription" name="description"></div>
                    </div>

                    <!-- Applicable Regulation -->
                    <div class="mb-3">
                        <label for="ApplicableRegulation" class="form-label">
                            <i class="fas fa-balance-scale"></i> 
                            Applicable Regulation
                        </label>
                         <div class="quill-editor"  id="CloneApplicableRegulation" name="ApplicableRegulation" ></div>
                    </div>
                        
                    <!-- Save Button -->
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Clone Paramters
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <!-- Table -->
    <i class="fas fa-magnifying-glass"></i>
    <select id="standardSearch" style="width: 20%;" placeholder="Search Standards..."></select>
   <table class="table table-sm table-hover" id="standardsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Standard Code</th>
                <th>Standard Name</th>
                <th>Report Note</th>
                <th>Standard Method</th>
                <th>Applicable Regulation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data will be appended here by jQuery -->
        </tbody>
    </table>
</div>
<nav>
    <ul class="pagination justify-content-center" id="pagination">
        <!-- Pagination buttons will be generated dynamically -->
    </ul>
</nav>
<script src="js/quilljs/1.3.7/quill.min.js"></script>
<script src="js/select2.min.js" type="text/javascript"></script>
<script src="js/tom-select/tom-select.complete.min.js" type="text/javascript"></script>
 <script>
     
$(document).ready(function() {
    
$('#standardSearch').select2({
    placeholder: 'Search Standards...',
    allowClear: true,
    minimumInputLength: 1, // begin search after at least 1 character is typed
    ajax: {
        url: 'ajax/fetchteststandards.php', // same endpoint used in your fetchStandards function
        dataType: 'json',
        delay: 250, // debounce time in milliseconds
        data: function (params) {
            // Parameters sent to the server
            return {
                q: params.term,           // search term
                page: params.page || 1      // current page number
            };
        },
        processResults: function (data, params) {
            // Parse the results into the format expected by Select2.
            // Here, each result must have at least "id" and "text".
            params.page = params.page || 1;
            var results = data.data.map(function(standard) {
                return {
                    id: standard.StandardID,
                    // Customize the display text as needed. Here we combine the code and name.
                    text: standard.StandardCode + ' - ' + standard.StandardName,
                    // Optionally include the whole object for later use
                    standardData: standard
                };
            });

            return {
                results: results,
                // Indicate whether there are more results available for pagination.
                pagination: {
                    more: params.page < data.total_pages
                }
            };
        },
        cache: true
    }
});

// Optional: handle the event when a user selects a result.
$('#standardSearch').on('select2:select', function (e) {
    // e.params.data is a single object, not an array.
    var selected = e.params.data;
    console.log("Selected Standard:", selected);
    // If you stored the full details in a property, e.g., standardData:
    var standard = selected.standardData || selected; // Fallback to selected if standardData not available

     const tbody = $('#standardsTable tbody');
     tbody.empty();
        tbody.append(`
            <tr>
                <td>${standard.StandardID}</td>
                <td>${standard.StandardCode}</td>
                <td>${standard.StandardName}</td>
                <td>${standard.Description}</td>
                <td>${standard.standard_method}</td>
                <td>${standard.ApplicableRegulation}</td>
                <td>
                    <button class="btn btn-warning btn-sm cloneStandard"   
                    data-id="${standard.StandardID}"  data-name="${standard.StandardName}"><i class="fas fa-copy"></i></button>
                    <button class="btn btn-warning btn-sm editStandard" data-id="${standard.StandardID}"  data-code="${standard.StandardCode}" data-name="${standard.StandardName}" data-description="${standard.Description}"  data-ApplicableRegulation="${standard.ApplicableRegulation}" data-methodname="${standard.sm}"  ><i class="fas fa-edit"></i></button>
                    <button class="btn btn-danger btn-sm deleteStandard" data-id="${standard.StandardID}"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>
        `);

    const pagination = $('#pagination');
    pagination.empty();

});

$('#standardSearch').on('select2:clear', function(e) {
    const savedPage = localStorage.getItem('currentPageforteststandards') || 1; // Default to page 1 if no saved page
    fetchStandards(savedPage); // Refresh standards table
 });


new TomSelect("#standardmethod", {
    create: function(input, callback) {
        // Regex for ISO formats (ISO 9001:2015, ISO/IEC 27001:2022)
        const isoRegex = /^(ISO(?:\/[A-Z]+)?)\s?\d{3,5}(?::\d{4})?$/i;

        if (!isoRegex.test(input)) {
            generalPurposeTypeLine("❌ Invalid ISO format. Example: ISO 9001:2015, ISO/IEC 27001:2022");
            return false; // Block saving
        }

        // ✅ Valid format -> send to backend to CREATE
        $.ajax({
            url: "ajax/save_iso_standard.php",
            type: "POST",
            dataType: "json",
            data: { standard_method: input },
            success: function(response) {
                if (response.success) {
                    // Server should return new primary key $standardmethod
                    callback({
                        value: response.id, // primary key
                        text: response.standard_method
                    });
                } else {
                    generalPurposeTypeLine("⚠️ " + response.message);
                    return false;
                }
            },
            error: function() {
                generalPurposeTypeLine("⚠️ Error saving standard");
                return false;
            }
        });
    },
    valueField: "id",              // <-- primary key from DB
    labelField: "standard_method", // <-- readable text
    searchField: "standard_method",
    maxItems: 1, // One selection only

    load: function(query, callback) {
        if (!query.length) return callback();
        $.ajax({
            url: "ajax/gettstandardmethods.php",
            type: "GET",
            dataType: "json",
            data: { q: query },
            success: function(res) {
                callback(res); // expecting [{id:1, standard_method:'ISO 9001:2015'}, ...]
            },
            error: function() {
                callback();
            }
        });
    }
});

new TomSelect("#Clonestandardmethod", {
    create: function(input, callback) {
        // Regex for ISO formats (ISO 9001:2015, ISO/IEC 27001:2022)
        const isoRegex = /^(ISO(?:\/[A-Z]+)?)\s?\d{3,5}(?::\d{4})?$/i;

        if (!isoRegex.test(input)) {
            generalPurposeTypeLine("❌ Invalid ISO format. Example: ISO 9001:2015, ISO/IEC 27001:2022");
            return false; // Block saving
        }

        // ✅ Valid format -> send to backend to CREATE
        $.ajax({
            url: "ajax/save_iso_standard.php",
            type: "POST",
            dataType: "json",
            data: { standard_method: input },
            success: function(response) {
                if (response.success) {
                    // Server should return new primary key $standardmethod
                    callback({
                        value: response.id, // primary key
                        text: response.standard_method
                    });
                } else {
                    generalPurposeTypeLine(response.message);
                    return false;
                }
            },
            error: function() {
                generalPurposeTypeLine("Error saving standard");
                return false;
            }
        });
    },
    valueField: "id",              // <-- primary key from DB
    labelField: "standard_method", // <-- readable text
    searchField: "standard_method",
    maxItems: 1, // One selection only

    load: function(query, callback) {
        if (!query.length) return callback();
        $.ajax({
            url: "ajax/gettstandardmethods.php",
            type: "GET",
            dataType: "json",
            data: { q: query },
            success: function(res) {
                callback(res); // expecting [{id:1, standard_method:'ISO 9001:2015'}, ...]
            },
            error: function() {
                callback();
            }
        });
    }
});

// Open modal for adding a new standard
// Open modal to edit a standard
$(document).on('click', '.cloneStandard', function () {
  $('#StandardCloneID').val($(this).data('id'));
   
  const name = $(this).data('name') || '';
  const standardName = `Clone All Parameters from ${name}`;
  $('#StandardClonename').text(standardName);

  new bootstrap.Modal(document.getElementById('standardcloneModal')).show();
});

$(document).on('blur', '#standardmethod', function () {
    let val = $(this).val().trim();
    if (val === "") return;

  // Normalize input
      val = val.toUpperCase().replace(/^ISO\s*/, "ISO ");
  // Allow ISO with flexible spacing, dash, colon, etc.
    let match = val.match(/^ISO\s*([0-9]{1,5})\s*[:\-]?\s*([0-9]{4})$/);

    if (match) {
        // Format as ISO XXXX:YYYY
        val = `ISO ${match[1]}:${match[2]}`;
        $(this).val(val);

        // 🚀 Auto-send to backend
        $.ajax({
            url: "ajax/save_iso_standard.php",
            type: "POST",
            data: { standard_method: val },
            success: function (response) {
                console.log("✅ ISO saved:", response);
            },
            error: function (xhr) {
                console.error("❌ Error saving ISO:", xhr.responseText);
            }
        });
    } else {
        generalPurposeTypeLine("⚠ Invalid ISO format. Example: ISO 9001:2015");
        $(this).focus();
    }
});

$(document).on('blur', '#Clonestandardmethod', function () {
    let val = $(this).val().trim();
    if (val === "") return;
// Normalize input
      val = val.toUpperCase().replace(/^ISO\s*/, "ISO ");
// Allow ISO with flexible spacing, dash, colon, etc.
    let match = val.match(/^ISO\s*([0-9]{1,5})\s*[:\-]?\s*([0-9]{4})$/);

    if (match) {
        // Format as ISO XXXX:YYYY
        val = `ISO ${match[1]}:${match[2]}`;
        $(this).val(val);
    // 🚀 Auto-send to backend
        $.ajax({
            url: "ajax/save_iso_standard.php",
            type: "POST",
            data: { standard_method: val },
            success: function (response) {
                console.log("✅ ISO saved:", response);
            },
            error: function (xhr) {
                console.error("❌ Error saving ISO:", xhr.responseText);
            }
        });
    } else {
        generalPurposeTypeLine("⚠ Invalid ISO format. Example: ISO 9001:2015");
        $(this).focus();
    }
});

// Show Upload Modal
$('#uploadStandardsBtn').on('click', function () {
   new bootstrap.Modal(document.getElementById('uploadStandardsModal')).show();
});

$('#addstandardmethodBtn').on('click', function () {
  new bootstrap.Modal(document.getElementById('addstandardmethods')).show();
});


function fetchStandards(page = 1, search = '') {
    $.ajax({
        url: 'ajax/fetchteststandards.php',
        type: 'GET',
        data: { page, q: search },
        dataType: 'json',
        success: function (response) {
            const tbody = $('#standardsTable tbody');
            tbody.empty();

            // Iterate over response.data (an array)
            response.data.forEach((standard) => {
                tbody.append(`
                    <tr>
                        <td>${standard.StandardID}</td>
                        <td>${standard.StandardCode}</td>
                        <td>${standard.StandardName}</td>
                        <td>${standard.Description}</td>
                        <td>${standard.standard_method}</td>
                        <td>${standard.ApplicableRegulation}</td>
                        <td>
                           <button class="btn btn-warning btn-sm cloneStandard" data-id="${standard.StandardID}" data-name="${standard.StandardName}"><i class="fas fa-copy"></i></button>
                           <button class="btn btn-warning btn-sm editStandard" data-id="${standard.StandardID}" data-code="${standard.StandardCode}" 
                            data-name="${standard.StandardName}" data-description="${standard.Description}"  data-smid="${standard.sm}"
                            data-applicableregulation="${standard.ApplicableRegulation}" data-methodname="${standard.standard_method}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm deleteStandard" data-id="${standard.StandardID}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Update pagination
            const pagination = $('#pagination');
            pagination.empty();
            pagination.append(`
                <li class="page-item ${response.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${response.current_page - 1}">Previous</a>
                </li>
            `);
            for (let i = 1; i <= response.total_pages; i++) {
                pagination.append(`
                    <li class="page-item ${i === response.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }
            pagination.append(`
                <li class="page-item ${response.current_page === response.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${response.current_page + 1}">Next</a>
                </li>
            `);
        },
        error: function () {
            console.error('Failed to fetch test standards.');
        }
    });
};

// Load standards on page load
fetchStandards(1);

 // Handle pagination click
$(document).on('click', '#pagination .page-link', function (e) {
    e.preventDefault();
    const page = $(this).data('page');
       localStorage.setItem('currentPageforteststandards', page); // Save current page to localStorage
    fetchStandards(page);
});

// Delete a standard
$(document).on('click', '.deleteStandard', function () {
    const standardID = $(this).data('id');
    if (confirm('Are you sure you want to delete this standard?')) {
        $.ajax({
            url: 'ajax/deleteteststandard.php', // Create a separate script for deletion
            type: 'POST',
            data: { StandardID: standardID },
            dataType: 'json',
            success: function (response) {
                generalPurposeTypeLine(response.message);
                if (response.success) {
                    fetchStandards();
                }
            }
        });
    }
});

// Open modal to edit a standard
$(document).on('click', '.editStandard', function () {
    $('#StandardID').val($(this).data('id'));
    $('#Code').val($(this).data('code'));
    $('#name').val($(this).data('name'));
    $('#standardmethod').val($(this).data('methodname'));

    // Access the Quill instances by ID
    const descriptionQuill = quillInstances['description'];
    const regulationQuill = quillInstances['ApplicableRegulation'];

    // Set data in the Quill editors
    if (descriptionQuill) {
        descriptionQuill.root.innerHTML = $(this).data('description') || '';
    }

    if (regulationQuill) {
        regulationQuill.root.innerHTML = $(this).data('applicableregulation') || '';
    }

    new bootstrap.Modal(document.getElementById('standardModal')).show();
  
});

// Open modal for adding a new standard
$('#addNewStandard').click(function () {
        $('#standardForm')[0].reset();
        $('#StandardID').val('');
        // Clear Quill editor contents
        const descriptionQuill = quillInstances['description'];
        const regulationQuill = quillInstances['ApplicableRegulation'];

        if (descriptionQuill) {
            descriptionQuill.root.innerHTML = ''; // Clear content
        }

        if (regulationQuill) {
            regulationQuill.root.innerHTML = ''; // Clear content
        }
        
       new bootstrap.Modal(document.getElementById('standardModal')).show();
  
    });

$('#standardForm').on('submit', function (e) {
    e.preventDefault();
    
       
    const uploadButton = $('#standardCloneForm button[type="submit"]');
    uploadButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

   // Get the Quill editor contents
    const descriptionQuill = quillInstances['description'];
    const regulationQuill = quillInstances['ApplicableRegulation'];

    const descriptionValue = descriptionQuill ? descriptionQuill.root.innerHTML : '';
    const regulationValue = regulationQuill ? regulationQuill.root.innerHTML : '';

    // Prepare form data
    const formData = $(this).serializeArray(); // Get other form data as an array

    // Add Quill editor contents to the form data
    formData.push({ name: 'description', value: descriptionValue });
    formData.push({ name: 'applicableregulation', value: regulationValue });

    // Convert the form data into a format suitable for AJAX
    const ajaxData = {};
    formData.forEach(item => {
        ajaxData[item.name] = item.value;
    });

    // Send the AJAX request
    $.ajax({
        url: 'ajax/teststandardshandler.php',
        type: 'POST',
        data: ajaxData, // Send the data as an object
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                
                var modalEl = document.getElementById('standardModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                
                 generalPurposeTypeLine(response.message);
                uploadButton.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
   
                const savedPage = localStorage.getItem('currentPageforteststandards') || 1; // Default to page 1 if no saved page
                fetchStandards(savedPage);
            }
        },
        error: function () {
            generalPurposeTypeLine('An error occurred while processing the request.');
        }
    });
});

       
$('#standardCloneForm').on('submit', function (e) {
    e.preventDefault();
   // Get the Quill editor contents
    const uploadButton = $('#standardCloneForm button[type="submit"]');
    uploadButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

    const descriptionQuill = quillInstances['Clonedescription'];
    const regulationQuill  = quillInstances['CloneApplicableRegulation'];

    const descriptionValue = descriptionQuill ? descriptionQuill.root.innerHTML : '';
    const regulationValue = regulationQuill ? regulationQuill.root.innerHTML : '';
   // Prepare form data
    const formData = $(this).serializeArray(); // Get other form data as an array
    // Add Quill editor contents to the form data
    formData.push({ name: 'description', value: descriptionValue });
    formData.push({ name: 'applicableregulation', value: regulationValue });
   // Convert the form data into a format suitable for AJAX
    const ajaxData = {};
    formData.forEach(item => {
        ajaxData[item.name] = item.value;
    });
   // Send the AJAX request
    $.ajax({
        url: 'ajax/teststandardshandlerandclone.php',
        type: 'POST',
        data: ajaxData, // Send the data as an object
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                
                var modalEl = document.getElementById('standardcloneModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                
                 generalPurposeTypeLine(response.message);
                 
                uploadButton.prop('disabled', false).html('<i class="fas fa-save"></i> Clone Paramters');
                
                const savedPage = localStorage.getItem('currentPageforteststandards') || 1; // Default to page 1 if no saved page
                fetchStandards(savedPage);
            }
        },
        error: function () {
            generalPurposeTypeLine('An error occurred while processing the request.');
        }
    });
});

 // Handle File Upload excel
$('#uploadStandardsForm').on('submit', function (e) {
    e.preventDefault();

const uploadButton = $('#uploadStandardsForm button[type="submit"]');
    uploadButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

    let formData = new FormData(this);

    $.ajax({
        url: 'ajax/upload_standards_handler.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            const res = JSON.parse(response);
            if (res.success) {
                generalPurposeTypeLine(res.message);
                $('#uploadStandardsModal').modal('hide');
                uploadButton.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
               const savedPage = localStorage.getItem('currentPageforteststandards') || 1; // Default to page 1 if no saved page
               fetchStandards(savedPage); // Refresh standards table
            } else {
                generalPurposeTypeLine('Error: ' + res.message);
            }
        },
        error: function () {
            generalPurposeTypeLine('An error occurred while uploading the file.');
        }
    });
});

    
});

function populatestandardmethod() {
       $.ajax({
            url: 'ajax/gettstandardmethods.php', // Create a separate PHP script to fetch all standards
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                const roleSelect = $('#standardmethod');
                roleSelect.empty();
                console.log('response.data',response)
                const options=response;
                options.forEach(role => {
                    roleSelect.append(`<option value="${role.id}">${role.text}</option>`);
                });
                roleSelect.trigger('change');
            },
            error: function () {
                generalPurposeTypeLine('Failed to fetch standard methods');
            }
          });
}

function initializeQuillEditors() {
  
    const quillEditors = document.querySelectorAll('.quill-editor');
       quillEditors.forEach((editor) => {
        const quill = new Quill(editor, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    [{'script': 'sub'}, {'script': 'super'}], // Subscript and superscript
                    [{'list': 'ordered'}, {'list': 'bullet'}], // Lists
                ],
            },
        });
       // Store the Quill instance using the editor's ID
        quillInstances[editor.id] = quill;
    });
}

initializeQuillEditors();

</script>