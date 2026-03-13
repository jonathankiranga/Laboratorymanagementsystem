<link rel="stylesheet" href="css/quickbookslook.css">
<link rel="stylesheet" href="js/quilljs/1.3.7/quill.snow.css">
<link rel="stylesheet" href="css/reducedcss.css">
<link rel="stylesheet" href="css/modalcss.css">
<link rel="stylesheet" href="css/cooltables.css">
<link rel="stylesheet" href="css/typing.css"> 
<link rel="stylesheet" href="css/parametersetup.css"> 
<link href="css/select2.min.css" rel="stylesheet" type="text/css"/>

<div class="container mt-5">
    <h3 class="text-center">Global Parameters</h3>
</div> 
    
<!-- Modal -->
<!-- File Upload Modal -->
<div id="uploadStandardsModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload"></i>Upload parameters from Excel<span id="uploadstandardName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <form id="uploadStandardsForm" enctype="multipart/form-data">
                   <input type="hidden" id="uploadStandardID" />
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">
                            <i class="fas fa-file-excel"></i> Select Excel File
                        </label>
                        <ul><li>Parameter Name</li>
                            <li>Neutrality ID (if applicable)</li>
                            <li>TDS ID (If applicable)</li>
                            <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xls,.xlsx" required>
                    </div>
                   <div id="showstatus"></div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Nested Modal for Adding a Parameter -->
    <div id="ParameterModalRecord" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="padding: 10px;">
                    <div class="modal-header">
                        <h5 class="modal-title"><span id="addstandardName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" data-bs-target="#ParameterModalRecord" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3" style="padding: 10px;">
                           <form id="ParameterForm">
                                <input type="hidden"  id="ParameterIDForm" name="ParameterID">
                                <div class="mb-3">
                                <label for="ParameterName" class="form-label">Name ( Must be unique)</label>
                                <div class="quill-editor form-control-sm" id="ParameterNameForm" name="ParameterName"></div>
                            </div>

                            <div class="d-flex flex-column mb-2">
                                <label for="neutralityID" class="form-label">Neutrality ID</label>
                                <select id="neutralityID" name="neutralityID"><option></option>

                                </select>
                            </div>

                            <div class="d-flex flex-column mb-2">
                                <label for="tdsID" class="form-label">TDS Calculator ID</label>
                                <select id="tdsID" name="tdsID"><option></option>

                                </select>
                            </div>

                             <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save
                        </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <button id="cloneParameterBtn" class="btn btn-secondary mb-3">
        <i class="fas fa-copy"></i>Clone Parameters From Existing
    </button>

    <button id="addParameterBtn" class="btn btn-secondary mb-3">
        <i class="fas fa-plus"></i>Add Global Parameter
    </button>

      <button class="btn btn-secondary btn-sm mb-3" data-id=""  data-name="" id="importParametersBtn">
        <i class="fas fa-file-excel"></i> Import Parameters
    </button>
              
   <i class="fas fa-magnifying-glass"></i><select id="standardSearch" style="width:20%;" placeholder="Search Standards..."></select>

    <div class="table-responsive ">
        <!-- Table -->
        <table class="table table-sm table-hover" id="standardsTable">
            <thead><tr>
                    <th class="col-id">ID</th>
                    <th class="col-date">Global Parameter Name</th>
                    <th class="col-description">TDS ID</th>
                    <th class="col-others">Neutrality ID</th>
                    <th class="col-others">Actions</th>
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

<!-- Parameters Management Modal -->
<div id="parametersModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cogs"></i> Manage Parameters for <span id="standardName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-bs-target="#parametersModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <button class="btn btn-success btn-sm mb-3" data-id="" data-name="" id="addParameterBtn">
                    <i class="fas fa-plus"></i> Add Items
                </button>
                <button class="btn btn-secondary btn-sm mb-3" data-id=""  data-name="" id="importParametersBtn">
                    <i class="fas fa-file-excel"></i> Import Items
                </button>                               
            </div>
        </div>
    </div>
</div>
<script src="js/select2.min.js" type="text/javascript"></script>
  <script src="js/quilljs/1.3.7/quill.min.js"></script>
 <script>
 

$(document).ready(function () {
    
    $('#standardSearch').select2({
        placeholder: 'Search paramters...',
        allowClear: true,
        minimumInputLength: 1, // begin search after at least 1 character is typed
        ajax: {
            url: 'ajax/fetchbaseelements.php', // same endpoint used in your fetchStandards function
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
                        id: standard.ParameterID,
                        // Customize the display text as needed. Here we combine the code and name.
                        text: standard.ParameterName,
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
        console.log("Selected paramter:", selected);
        // If you stored the full details in a property, e.g., standardData:
        var standard = selected.standardData || selected; // Fallback to selected if standardData not available

         const tbody = $('#standardsTable tbody');
         tbody.empty();
         tbody.append(`<tr>
                            <td>${standard.ParameterID}</td>
                            <td>${standard.ParameterName}</td>
                            <td>${standard.Tds}</td>
                            <td>${standard.Neutrality}</td>
                            <td>
                                <button class="btn btn-info btn-sm editParameter" 
                                    data-StandardID="${standard.ParameterID}" 
                                    data-ParameterName="${standard.ParameterName}" 
                                    data-NeutralityID="${standard.NeutralityID}" 
                                    data-TdsID="${standard.TdsID}">
                                    <i class="fas fa-sliders-h"></i>Manage Parameter
                                </button>
                               <button class="btn btn-danger btn-sm deleteParameter" data-id="${standard.ParameterID}"><i class="fas fa-trash-alt"></i></button>
        
                            </td>
                       </tr>`);

                const pagination = $('#pagination');
                pagination.empty();
          
    });
    
    $('#standardSearch').on('select2:clear', function(e) {
        const savedPage = localStorage.getItem('currentPageforteststandards') || 1; // Default to page 1 if no saved page
        fetchBaseItems(savedPage); // Refresh standards table
   });

    $('#cloneParameterBtn').on('click', function () {
        
         const uploadButton = $('#cloneParameterBtn');
         uploadButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
         
          const formData = $(this).serializeArray();
          $.ajax({
            url: 'ajax/cloneBaseparameter.php', // Replace with your actual server endpoint
            type: 'POST', // HTTP method to send data
            data: formData, // Serialized form data
            dataType: 'json', // Expect JSON response from the server
            success: function (response) {
                // Handle the success response
                if (response.success) {
                    generalPurposeTypeLine('Parameter copied successfully!'); // Show success message
                    fetchBaseItems();
                } else {
                    toastr.error('Error: ' + response.message); // Show error message
                }
            },
            error: function (xhr, status, error) {
                // Handle errors
                toastr.error('An error occurred: ' + error + '\nResponse: ' + xhr.responseText);
            },
            complete: function () {
                console.log('AJAX call completed.'); // Optional: log completion for debugging
                uploadButton.prop('disabled', false).html('<i class="fas fa-copy"></i> Clone Parameters From Existing'); // Re-enable button after the request completes
            }
        });
        
    });
    
    // Add Parameter
    $('#addParameterBtn').on('click', function () {
        $('#ParameterModalRecord').trigger('reset');
        $('#ParameterIDForm').val("");
        $('#addstandardName').text("");
        quillInstances['ParameterNameForm'].root.innerHTML = "";
       // Code to open a form/modal to add a new parameter       
        const editModal = new bootstrap.Modal($('#ParameterModalRecord'), { backdrop: 'static' });
        editModal.show();
    
    });
 
      // Handle Edit Button Click  edit parameters
    $(document).on('click','.editParameter', function () {
        $('#ParameterForm').trigger('reset');
        $('#ParameterIDForm').val($(this).data('standardid'));
        $('#addstandardName').text($(this).data('parametername'));
        quillInstances['ParameterNameForm'].root.innerHTML = $(this).data('parametername');
        
        $('#tdsID').val($(this).data('tdsid'));
        $('#neutralityID').val($(this).data('neutralityid'));
        
         const editModal = new bootstrap.Modal($('#ParameterModalRecord'), { backdrop: 'static' });
         editModal.show();
    });

    // Handle Save in Nested Modal
    $('#ParameterForm').submit(function (e) {
        e.preventDefault();
        
        const uploadButton = $('#ParameterForm button[type="submit"]');
        uploadButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

            const formData = $(this).serializeArray();
            $('.quill-editor').each(function () {
                const quillInstanceId = $(this).attr('id'); // Get the editor's ID
                const quill = Quill.find(this); // Retrieve the Quill instance for this container

                if (quill) {
                    const quillContent = quill.root.innerText.trim(); // Get the HTML content
                    formData.push({ name: quillInstanceId, value: quillContent }); // Add to formData
                }
            });
        
            $.ajax({
            url: 'ajax/saveBaseparameter.php', // Replace with your actual server endpoint
            type: 'POST', // HTTP method to send data
            data: formData, // Serialized form data
            dataType: 'json', // Expect JSON response from the server
            success: function (response) {
                // Handle the success response
                if (response.success) {
                    generalPurposeTypeLine('Parameter saved successfully!'); // Show success message
                    $('#ParameterModalRecord').modal('hide'); // Hide the modal
                    fetchBaseItems();
                } else {
                    toastr.error('Error: ' + response.message); // Show error message
                }
            },
            error: function (xhr, status, error) {
                // Handle errors
                toastr.error('An error occurred: ' + error + '\nResponse: ' + xhr.responseText);
            },
            complete: function () {
                console.log('AJAX call completed.'); // Optional: log completion for debugging
                uploadButton.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload'); // Re-enable button after the request completes
            }
        });
    });
                   

    
   // Ensure the modal is on top when shown
    $(document).on('shown.bs.modal', '.modal', function () {
        const zIndex = 1050 + ($('.modal:visible').length * 10);
        $(this).css('z-index', zIndex);
        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    });

    // Restore remaining modals and backdrops when a modal is closed
    $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal:visible').each(function (index) {
            const zIndex = 1050 + ((index + 1) * 10);
            $(this).css('z-index', zIndex);
        });

        $('.modal-backdrop').not('.modal-stack').each(function (index) {
            const zIndex = 1040 + ((index + 1) * 10);
            $(this).css('z-index', zIndex);
        });

        if (!$('.modal:visible').length) {
            $('.modal-backdrop').remove();
        }
    });

    $('#importParametersBtn').on('click', function () {
          const standardID = $(this).data('id');
           $('#uploadStandardID').val(standardID);
           const standardName = $(this).data('name');
           $('#uploadstandardName').text(standardName);
           // Code to handle Excel import
          $('#uploadStandardsForm').trigger('reset');
    
           const editModal = new bootstrap.Modal($('#uploadStandardsModal'));
           editModal.show();
    });

    const fetchBaseItems = (page = 1, search = '') => {
        $.ajax({
            url: 'ajax/fetchbaseelements.php',
            type: 'GET',
            data: { page, q: search },
            dataType: 'json',
            success: function (response) {
                const tbody = $('#standardsTable tbody');
                tbody.empty();
                 response.data.forEach((standard) => {
                    tbody.append(`<tr>
                            <td>${standard.ParameterID}</td>
                            <td>${standard.ParameterName}</td>
                            <td>${standard.Tds}</td>
                            <td>${standard.Neutrality}</td>
                            <td>
                                <button class="btn btn-info btn-sm editParameter" 
                                    data-StandardID="${standard.ParameterID}" 
                                    data-ParameterName="${standard.ParameterName}" 
                                    data-NeutralityID="${standard.NeutralityID}" 
                                    data-TdsID="${standard.TdsID}">
                                    <i class="fas fa-sliders-h"></i>Manage Parameter
                                </button>
                            </td>
                       </tr>`);
                });
                                // Handle pagination
                 const pagination = $('#pagination');
                 pagination.empty();

                 // Add "Previous" link
                 pagination.append(`
                     <li class="page-item ${response.current_page === 1 ? 'disabled' : ''}">
                         <a class="page-link" href="#" data-page="${response.current_page - 1}">Previous</a>
                     </li>
                 `);

                 // Add "Page X of Y"
                 pagination.append(`
                     <li class="page-item disabled">
                         <a class="page-link">Page ${response.current_page} of ${response.total_pages}</a>
                     </li>
                 `);

                 // Add "Next" link
                 pagination.append(`
                     <li class="page-item ${response.current_page === response.total_pages ? 'disabled' : ''}">
                         <a class="page-link" href="#" data-page="${response.current_page + 1}">Next</a>
                     </li>
                 `);


            },
            error: function () {
                toastr.error('Failed to fetch test standards.');
            }
            
            
        });
    };

    // Load standards on page load
    fetchBaseItems();

 // Handle pagination click
    $(document).on('click', '#pagination .page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
         localStorage.setItem('currentPageforteststandards', page); // Save current page to localStorage
        fetchBaseItems(page);
    });
          
  
   
    // Handle File Upload
    $('#uploadStandardsForm').on('submit', function (e) {
        e.preventDefault();

        const uploadButton = $('#uploadStandardsForm button[type="submit"]');
        uploadButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

        let formData = new FormData(this);
     
        // File validation (optional)
        var fileInput = $('#excelFile')[0];
        var file = fileInput.files[0];
        if (file && file.size > 5 * 1024 * 1024) { // 5MB size limit
            toastr.error('File size exceeds the maximum allowed limit of 5MB.');
            uploadButton.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
            return;
        }

        $.ajax({
            url: 'ajax/importGlobalparameters.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log(response)
                const res = JSON.parse(response);
                if (res.success) {
                    toastr.succes(res.message);
                    $('#uploadStandardsModal').modal('hide');
                    fetchBaseItems() ;
                } else {
                    toastr.error('Error: ' + res.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error('Error: ' + error + '\nStatus: ' + status + '\nResponse: ' + xhr.responseText);
            },
            complete: function() {
                uploadButton.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload'); // Re-enable button after the request completes
            }
        });
    });


    // Delete Parameter
    $(document).on('click', '.deleteParameter', function () {
        const parameterID = $(this).data('id');
        if (confirm('Are you sure you want to delete this parameter?')) {
            $.ajax({
                url: 'ajax/deleteglobalparameter.php',
                type: 'POST',
                data: { parameterID },
                dataType: 'json',
                success: function (response) {
                    toastr.succes(response.message);
                    if (response.success) {
                        fetchBaseItems();
                    }
                },
                error: function () {
                    toastr.error('Failed to delete parameter.');
                }
            });
        }
    });
    
    
});


$.ajax({
     url: 'jsonfiles/neutralityarray.php', // Replace with your actual data source
     method: 'GET',
     dataType: 'json',
     success: function(data) {
         // Assuming 'data' is an array of country names
         var countrySelect = $('#neutralityID');
         $.each(data, function(index, country) {
             countrySelect.append($('<option></option>').attr('value', country.id).text(country.ion_name));
         });
     },
     error: function(xhr, status, error) {
         toastr.error('Error fetching neutrality parameters:'+ error.message);
     }
 });


$.ajax({
     url: 'jsonfiles/tdsarray.php', // Replace with your actual data source
     method: 'GET',
     dataType: 'json',
     success: function(data) {
         // Assuming 'data' is an array of country names
         var countrySelect = $('#tdsID');
         $.each(data, function(index, country) {
             countrySelect.append($('<option></option>').attr('value', country.id).text(country.Element));
         });
     },
     error: function(xhr, status, error) {
         toastr.error('Error fetching TDS parameters:'+ error.message);
     }
 });       



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
  
   initializeQuillEditors() ;
 // Function to update the Limits field
    
// Call the function to populate the select element
</script>