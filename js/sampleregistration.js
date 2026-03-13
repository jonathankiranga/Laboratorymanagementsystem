// ----------------------------
// DYNAMIC ROW HANDLING
// ----------------------------
   
$(document).on('input', 'input[name^="samples"]', function () {
    updateRegistrationSummary();
});

  // Add a new sample row (merged & extended)
function addSampleRow() {
    const rowIndex = document.querySelectorAll('#sampleRows tr.main-row').length;
    const irow = rowIndex + 1;
    document.getElementById('tablecount').value= irow;
                      
    //  gets the last rowindex
    const row = document.createElement('tr');
    row.classList.add('main-row');
    row.innerHTML = `
      <td>
        <input type="hidden" id="rowindex_${irow}" name="rowindex[]" value="${irow}" />
        <input type="text" id="StandardName_${irow}" name="StandardName[]" autocomplete="off" onkeyup="handleStandardInput(event)" class="form-control form-control-sm" />
        <input type="hidden" id="StandardID_${irow}" name="standard_id[]" />
      </td>
      <td>
        <input type="text" id="MatrixName_${irow}" name="MatrixName[]" autocomplete="off" onkeyup="handleMatrixInput(event)" class="form-control form-control-sm"
          onmouseenter="showPreview(event, document.getElementById('StandardID_${irow}').value)" onmouseleave="hidePreview(event)" />
        <input type="hidden" id="MatrixID_${irow}" name="matrix_id[]" />
      </td>
      <td>
        <input type="number" id="samples_${irow}" name="samples[]" class="form-control form-control-sm" min="1" value="1" />
      </td>
      <td>
        <input type="text" placeholder="Enter SKU units" id="standard_kit_units_${irow}" name="standard_kit_units[]" class="form-control form-control-sm"/>
      </td>
      <td>
        <input type="text" id="product_batch_no_${irow}" name="product_batch_no[]" class="form-control form-control-sm" />
      </td>
      <td>
        <input type="number" id="batch_size_${irow}" name="batch_size[]" class="form-control form-control-sm" min="1" />
      </td>
      <td>
        <input type="date" id="date_of_manufacture_${irow}" name="date_of_manufacture[]" class="form-control form-control-sm" />
      </td>
      <td>
        <input type="date" id="date_of_expiry_${irow}" name="date_of_expiry[]" class="form-control form-control-sm" />
      </td>
      <td>
        <input type="file" id="sample_image_${irow}" accept="image/jpeg,image/gif" name="sample_images[]" class="form-control-file" />
      </td>
    <td>
        <input type="text" id="sample_source_${irow}"  name="sample_source[]" class="form-control form-control-sm" />
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-danger removeRow" title="Remove row"><i class="fas fa-trash-alt"></i></button>
      </td>
    `;

    document.getElementById('sampleRows').appendChild(row);

    const paramsRow = document.createElement('tr');
    paramsRow.classList.add('params-row');
    paramsRow.id = `params_row_${irow}`;
    paramsRow.style.display = 'none';
    paramsRow.innerHTML = `
      <td colspan="11">
        <div id="params_container_${irow}"></div>
      </td>
    `;
    document.getElementById('sampleRows').appendChild(paramsRow);

    // Smooth scroll container to bottom so user sees new row
    setTimeout(function () {
      let container = document.getElementById("sampleTableContainer");
      if (container) container.scrollTop = container.scrollHeight;
    }, 50);

    reindexSampleRows();        // ensure IDs are consistent
    updateRegistrationSummary();
  };

// Row removal handler
document.getElementById('sampleTable').addEventListener('click', function (e) {
  const btn = e.target.closest('.removeRow');
  if (!btn) return;

  const mainRows = document.querySelectorAll('#sampleRows tr.main-row');
  if (mainRows.length <= 1) {
    toastr.info('At least one sample row is required.');
    return;
  }

  const tr = btn.closest('tr.main-row');
  if (!tr) return;

  const rowIndexEl = tr.querySelector('input[name="rowindex[]"]');
  const rowId = rowIndexEl ? rowIndexEl.value : '';
  const paramsTr = rowId ? document.getElementById(`params_row_${rowId}`) : null;
  if (paramsTr) paramsTr.remove();
  tr.remove();

  reindexSampleRows();
  updateRegistrationSummary();
});

// ----------------------------
// PREVIEW BOX HANDLER
// ----------------------------

document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, { html: true, placement: 'auto' });
    });
});

function showPreview(event, stdId) {
    const inputId = event.target.id;
    const hiddenFieldId = inputId.replace('Name', 'ID');
    const query = document.getElementById(hiddenFieldId).value;
    const triggerEl = event.target;
    // Set temporary tooltip content
    triggerEl.setAttribute('data-bs-original-title', '<em>Loading parameters...</em>');
    // Show tooltip immediately
    let tooltip = bootstrap.Tooltip.getInstance(triggerEl);
    if (!tooltip) {
        tooltip = new bootstrap.Tooltip(triggerEl, { html: true, placement: 'auto' });
    }
  
    // Fetch parameters
    fetch('ajax/getParametersUnderMatrix.php?stdId=' + encodeURIComponent(stdId) + '&matrixid=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            let content;
            if (!Array.isArray(data) || data.length === 0) {
                content = '<span class="text-muted">No parameters found.</span>';
            } else {
                const list = data.map(p => `• ${p.ParameterName}`).join('<br>');
                content = `<strong>Parameters:</strong><br>${list}`;
            }
        // Update tooltip content
            triggerEl.setAttribute('data-bs-original-title', content);
            tooltip.show(); // refresh
        })
        .catch(err => {
            console.error(err);
            triggerEl.setAttribute('data-bs-original-title', '<span class="text-danger">Error loading parameters.</span>');
            tooltip.show();
        });
}

function hidePreview(event) {
    const tooltip = bootstrap.Tooltip.getInstance(event.target);
    if (tooltip) tooltip.hide();
}

function fetchlabno(seed = null, callback) {
    let data = {
        action: 'GetTempBarcoderfNo',
        TransType: '19'
    };
    if (seed !== null) {
        data.seed = seed;
    }

    fetch('ajax/getrefferences.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            callback(data.data);   // run after we have it
        } else {
            console.error('Error:', data.message);
        }
    })
    .catch(err => console.error('Fetch Error:', err));
}
// Re-index row IDs and important attributes after add/remove
function reindexSampleRows() {
    let i = 0;
    const rows = document.querySelectorAll('#sampleRows tr');
    let currentMainRow = null;
    rows.forEach((tr) => {
      if (tr.classList.contains('main-row')) {
        i++;
        currentMainRow = tr;
        const setIf = (selector, id) => {
          const el = tr.querySelector(selector);
          if (el) el.id = id;
        };

        setIf('input[name="rowindex[]"]', `rowindex_${i}`);
        setIf('input[name="StandardName[]"]', `StandardName_${i}`);
        setIf('input[name="standard_id[]"]', `StandardID_${i}`);
        setIf('input[name="MatrixName[]"]', `MatrixName_${i}`);
        setIf('input[name="matrix_id[]"]', `MatrixID_${i}`);
        setIf('input[name="samples[]"]', `samples_${i}`);
        setIf('input[name="standard_kit_units[]"]', `standard_kit_units_${i}`);
        setIf('input[name="product_batch_no[]"]', `product_batch_no_${i}`);
        setIf('input[name="batch_size[]"]', `batch_size_${i}`);
        setIf('input[name="date_of_manufacture[]"]', `date_of_manufacture_${i}`);
        setIf('input[name="date_of_expiry[]"]', `date_of_expiry_${i}`);
        setIf('input[name="sample_images[]"]', `sample_image_${i}`);
        setIf('input[name="sample_source[]"]', `sample_source_${i}`);
    
        // keep the preview handlers in sync
        const mat = tr.querySelector('input[name="MatrixName[]"]');
        if (mat) {
          mat.setAttribute('onmouseenter', `showPreview(event, document.getElementById('StandardID_${i}') ? document.getElementById('StandardID_${i}').value : '')`);
          mat.setAttribute('onmouseleave', 'hidePreview(event)');
        }

        // Update rowindex value
        const rowIndexInput = tr.querySelector('input[name="rowindex[]"]');
        if (rowIndexInput) rowIndexInput.value = i;
      } else if (tr.classList.contains('params-row') && currentMainRow) {
        tr.id = `params_row_${i}`;
        const container = tr.querySelector('div');
        if (container) {
          container.id = `params_container_${i}`;
          // Rename checkboxes
          tr.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.name = `selected_params_${i}[]`;
          });
        }
      }
    });
    document.getElementById('tablecount').value = i;
  }

  // Update registration summary: row count & total samples
  function updateRegistrationSummary() {
    const rows = document.querySelectorAll('#sampleRows tr.main-row');
    let totalRows = rows.length;
    let totalSamples = 0;
    rows.forEach(row => {
      const samplesEl = row.querySelector('input[name="samples[]"]');
      const n = samplesEl ? parseInt(samplesEl.value, 10) || 0 : 0;
      totalSamples += n;
    });
     
      document.getElementById('tablecount').value= totalRows;
 
      fetchlabno(null, function(fromlwno) {
        console.log("Lab No From:", fromlwno);
        fetchlabno(totalSamples, function(toLwno) {
            console.log("Lab No To:", toLwno);
        const out = document.getElementById('registrationsumary');
            if (out) {
                out.innerHTML = `<strong>Rows:</strong> ${totalRows} 
                    &nbsp; | &nbsp; <strong>Total samples:</strong> ${totalSamples}
                    | &nbsp; LAB REF NO from :<strong>${fromlwno}</strong> to :<strong>${toLwno}</strong>`;
            }
        });
    });
        
      
      
  }

  // Keep the summary up-to-date when users change numeric fields
  document.getElementById('sampleRows').addEventListener('input', function (e) {
    if (e.target.matches('input[name="samples[]"], input[name="standard_kit_units[]"], input[name="batch_size[]"]')) {
      updateRegistrationSummary();
    }
  });
// ----------------------------
// AUTOCOMPLETE: MATRIX
// ----------------------------

async function handleMatrixInput(event) {
  const query = event.target.value;
  const inputId = event.target.id;
  const rowNum = inputId.split('_')[1];
  const dropdownId = 'dropdown_' + inputId;
  const hiddenFieldId = inputId.replace('Name', 'ID');
  const paramsRow = document.getElementById(`params_row_${rowNum}`);
  const existingDropdown = document.getElementById(dropdownId);

  if (!query.trim()) {
    if (paramsRow) paramsRow.style.display = '';
    document.getElementById(hiddenFieldId).value = '';
    if (existingDropdown) existingDropdown.remove();
    return;
  } else {
    if (paramsRow) paramsRow.style.display = 'none';
  }

  // Remove previous dropdowns
    if (existingDropdown) {
        existingDropdown.remove();
    }
  document.querySelectorAll('.dropdown').forEach(d => d.remove());

  const inputEl = document.getElementById(inputId);
  const dropdown = document.createElement('div');
  dropdown.id = dropdownId;
  dropdown.classList.add('dropdown');
  dropdown.style.width = '300px';
  document.body.appendChild(dropdown);

  const rect = inputEl.getBoundingClientRect();
  const scrollY = window.scrollY || document.documentElement.scrollTop;
  const scrollX = window.scrollX || document.documentElement.scrollLeft;
  dropdown.style.left = rect.left + scrollX + 'px';
  dropdown.style.top = rect.bottom + scrollY + 'px';
  dropdown.innerHTML = '';

  if (!query.trim()) {
    dropdown.style.display = 'none';
    return;
  }

  try {
    const resp = await fetch(`ajax/searchmatrixes.php?q=${encodeURIComponent(query)}`);
    const matrices = await resp.json();

    if (matrices.length) {
      dropdown.style.display = 'block';
      matrices.forEach(mtx => {
        const div = document.createElement('div');
        div.textContent = mtx.FullPath;
        div.dataset.code = mtx.ParameterID;
        div.classList.add('dropdown-item');
        div.addEventListener('click', () => {
          inputEl.value = mtx.FullPath;
          document.getElementById(hiddenFieldId).value = mtx.ParameterID;
          dropdown.style.display = 'none';
          if (paramsRow) paramsRow.style.display = 'none';
        });
        dropdown.appendChild(div);
      });
    } else {
      dropdown.style.display = 'none';
    }
  } catch (err) {
    console.error('Error fetching matrix parameters:', err);
  }
}
// ----------------------------
// AUTOCOMPLETE: STANDARD
// ----------------------------
async function handleStandardInput(event) {
  const query = event.target.value;
  const inputId = event.target.id;
  const dropdownId = 'dropdown_' + inputId;
  const hiddenFieldId = inputId.replace('Name', 'ID'); // StandardName → StandardID
  const rowNum = inputId.split('_')[1];

  // Clean up previous dropdowns
  const existingDropdown = document.getElementById(dropdownId);
    if (existingDropdown) {
        existingDropdown.remove();
    }
  document.querySelectorAll('.dropdown').forEach(d => d.remove());

  const inputEl = document.getElementById(inputId);
  const dropdown = document.createElement('div');
  dropdown.id = dropdownId;
  dropdown.classList.add('dropdown');
  dropdown.style.width = '300px';
  document.body.appendChild(dropdown);

  const rect = inputEl.getBoundingClientRect();
  const scrollY = window.scrollY || document.documentElement.scrollTop;
  const scrollX = window.scrollX || document.documentElement.scrollLeft;
  dropdown.style.left = rect.left + scrollX + 'px';
  dropdown.style.top = rect.bottom + scrollY + 'px';
  dropdown.innerHTML = '';

  if (!query.trim()) {
    dropdown.style.display = 'none';
    return;
  }

  try {
    const resp = await fetch('ajax/searchstandards.php?query=' + encodeURIComponent(query));
    const standards = await resp.json();

    if (standards.length) {
      dropdown.style.display = 'block';
      standards.forEach(std => {
        const div = document.createElement('div');
        div.textContent = std.StandardName;
        div.dataset.code = std.StandardID;
        div.addEventListener('click', () => {
          inputEl.value = std.StandardName;
          document.getElementById(hiddenFieldId).value = std.StandardID;
          dropdown.style.display = 'none';

          // Populate parameters (matrixid=0 for all under standard)
          const paramsContainer = document.getElementById(`params_container_${rowNum}`);
          if (paramsContainer) {
            fetch(`ajax/getParametersUnderMatrix.php?stdId=${std.StandardID}&matrixid=0`)
              .then(res => res.json())
              .then(data => {
                paramsContainer.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                  paramsContainer.innerHTML = '<p>No parameters available.</p>';
                } else {
                  data.forEach(p => {
                    const label = document.createElement('label');
                    label.style.display = 'inline-block';
                    label.style.marginRight = '10px';
                    const cb = document.createElement('input');
                    cb.type = 'checkbox';
                    cb.name = `selected_params_${rowNum}[]`;
                    cb.value = p.ParameterID;
                    cb.checked = true;
                    label.appendChild(cb);
                    label.appendChild(document.createTextNode(` ${p.ParameterName}`));
                    paramsContainer.appendChild(label);
                  });
                }

                // Toggle visibility based on matrix input
                const matrixInput = document.getElementById(`MatrixName_${rowNum}`);
                
                const paramsRow = document.getElementById(`params_row_${rowNum}`);
                
                if (matrixInput && matrixInput.value.trim() === '') {
                  if (paramsRow) paramsRow.style.display = '';
                } else {
                  if (paramsRow) paramsRow.style.display = 'none';
                }
              })
              .catch(err => console.error('Error fetching parameters:', err));
          }
        });
        dropdown.appendChild(div);
      });
    } else {
      dropdown.style.display = 'none';
    }
  } catch (err) {
    console.error('Error fetching standards:', err);
  }
}

function promptSendSampleReceivedEmail(onDecision) {
  if (typeof onDecision !== 'function') {
    return;
  }

  if (!document.getElementById('toastr-middle-center-style')) {
    const style = document.createElement('style');
    style.id = 'toastr-middle-center-style';
    style.textContent = `
      #toast-container.toast-middle-center {
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }
      #toast-container.toast-middle-center > div {
        margin: 0 0 8px 0;
      }
    `;
    document.head.appendChild(style);
  }

  const promptId = `send_email_prompt_${Date.now()}_${Math.floor(Math.random() * 10000)}`;
  const messageHtml = `
    <div id="${promptId}">
      <div>Send a "Sample Received" email to this customer after successful registration?</div>
      <div style="margin-top:8px; display:flex; gap:8px;">
        <button type="button" class="btn btn-sm btn-primary email-prompt-yes">Send Email</button>
        <button type="button" class="btn btn-sm btn-secondary email-prompt-no">Skip Email</button>
      </div>
    </div>
  `;

  let resolved = false;
  const toast = toastr.warning(messageHtml, '', {
    closeButton: true,
    tapToDismiss: false,
    timeOut: 0,
    extendedTimeOut: 0,
    preventDuplicates: true,
    positionClass: 'toast-middle-center',
    onHidden: function () {
      if (!resolved) {
        resolved = true;
        onDecision(false);
      }
    }
  });

  if (!toast || !toast.length) {
    onDecision(false);
    return;
  }

  const resolveOnce = function (choice) {
    if (resolved) return;
    resolved = true;
    toastr.clear(toast, { force: true });
    onDecision(choice);
  };

  toast.off('click.emailPrompt');
  toast.on('click.emailPrompt', '.email-prompt-yes', function (e) {
    e.preventDefault();
    e.stopPropagation();
    resolveOnce(true);
  });
  toast.on('click.emailPrompt', '.email-prompt-no', function (e) {
    e.preventDefault();
    e.stopPropagation();
    resolveOnce(false);
  });
}


$(document).ready(function() {
  const username = localStorage.getItem('username');
  if (!username) {
        localStorage.clear();
        localStorage.setItem('saygoodbye', 'proof');
        window.location.href = 'index.php?loutout=yes';
        return;
    }

	     $(document).off('submit','#labform').on('submit', '#labform', function (e) {
	                e.preventDefault();
	                const form = this;

	                // Run built-in form validation first (required, date, etc.)
	                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
	                  form.reportValidity();
	                  return;
	                }

	                // Validate sample rows first
	                if (!validateSampleRows()) {
	                  return; // stop submission
	                }
	                const continueSubmission = function(shouldSendSampleReceivedEmail) {
                    // Disable submit buttons and show spinner (restore original HTML after)
                    const $submitBtns = $(form).find('button[type="submit"]');
                    const originalHtmls = [];
                    $submitBtns.each(function () { originalHtmls.push($(this).html()); });
                    $submitBtns.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

                    // Build FormData from the form (will include the file inputs)
                    let formData = new FormData(form);

                    document.querySelectorAll('#sampleRows tr.main-row').forEach((row, idx) => {
                        formData.append('rowindex[]', idx);
                        formData.append('standard_id[]', row.querySelector('[name="standard_id[]"]').value);
                        formData.append('matrix_id[]', row.querySelector('[name="matrix_id[]"]').value);
                        formData.append('samples[]', row.querySelector('[name="samples[]"]').value);
                        formData.append('kit_units[]', row.querySelector('[name="standard_kit_units[]"]').value);
                        formData.append('batch_no[]', row.querySelector('[name="product_batch_no[]"]').value);
                        formData.append('batch_size[]', row.querySelector('[name="batch_size[]"]').value);
                        formData.append('date_mfg[]', row.querySelector('[name="date_of_manufacture[]"]').value);
                        formData.append('date_exp[]', row.querySelector('[name="date_of_expiry[]"]').value);
                        formData.append('sample_source[]', row.querySelector('[name="sample_source[]"]').value);

                        // if files are present
                        const fileInput = row.querySelector('[name="sample_images[]"]');
                        if (fileInput && fileInput.files.length) {
                            formData.append('sample_images[]', fileInput.files[0]);
                        }
                    });

                    for (let i = 0; i < localStorage.length; i++) {
                        const key = localStorage.key(i);
                        const value = localStorage.getItem(key);
                        formData.append(key, value);
                    }

                    // Backend checks this flag before triggering email event
                    formData.append('send_sample_received_email', shouldSendSampleReceivedEmail ? '1' : '0');

                    // AJAX submit
                    $.ajax({
                        url: 'ajax/sampleregistrationAjax.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            try {
                                const res = typeof response === 'object' ? response : JSON.parse(response);
                                if (res.success) {
                                    const baseMessage = res.message || 'Data successfully saved.';
                                    const statusMessageMap = {
                                      SUCCESS: ' Email sent to customer.',
                                      SKIPPED: ' Email skipped by your selection.',
                                      FAILED: ' Email was not sent.'
                                    };
                                    const emailStatus = (res.email_status || '').toUpperCase();
                                    const statusMessage = statusMessageMap[emailStatus] || '';
                                    toastr.success(baseMessage + statusMessage);
                                    if (res.email_note && emailStatus !== 'SUCCESS') {
                                      toastr.info(res.email_note);
                                    }
                                    setTimeout(function () { window.location.href = "homepage.php"; }, 5000);
                                } else {
                                    toastr.error('Error: ' + (res.message || 'Unknown server error.'));
                                    $submitBtns.prop('disabled', false).each(function (i) { $(this).html(originalHtmls[i] || '<i class="fas fa-save"></i> Save'); });
                                }
                            } catch (err) {
                                toastr.error('Invalid server response. See console for details.');
                                console.error('Response parse error:', err, response);
                                $submitBtns.prop('disabled', false).each(function (i) { $(this).html(originalHtmls[i] || '<i class="fas fa-save"></i> Save'); });
                            }
                        },
                        error: function (xhr, status, error) {
                            toastr.error('An error occurred: ' + error + '\nResponse: ' + xhr.responseText);
                            $submitBtns.prop('disabled', false).each(function (i) { $(this).html(originalHtmls[i] || '<i class="fas fa-save"></i> Save'); });
                        },
                        complete: function () {
                            $submitBtns.prop('disabled', false).each(function (i) { $(this).html(originalHtmls[i] || '<i class="fas fa-save"></i> Save'); });
                            updateRegistrationSummary();
                        }
                    });
                };

                promptSendSampleReceivedEmail(continueSubmission);
       });  

     $(document).on('submit','#customerForm', function(e) {
                e.preventDefault(); // Prevent default form submission
         // Collect data from the form
                const customerData = $(this).serialize(); // Serialize form data
          // Example AJAX request to save the customer
                $.ajax({
                    url: 'ajax/saveCustomer.php', // Your server-side script to handle the save
                    type: 'POST',
                    data: customerData,
                    success: function(response) {
                        generalPurposeTypeLine('Customer added successfully!');
                        $('#modal').modal('hide'); // Hide the modal
                        // Optionally, refresh the customer list or perform other actions
                    },
                    error: function() {
                        toastr.error('Error adding customer. Please try again.');
                    }
                });
            });
            
            
             $.ajax({
             url: 'jsonfiles/Countriesarray.php', // Replace with your actual data source
             method: 'GET',
             dataType: 'json',
             success: function(data) {
                 // Assuming 'data' is an array of country names
                 var countrySelect = $('#countrySelect');
                 $.each(data, function(index, country) {
                     countrySelect.append($('<option></option>').attr('value', country).text(country));
                 });
             },
             error: function(xhr, status, error) {
                 toastr.error('Error fetching countries:'+ error.message);
             }
         });


 });    


async function handleCustomerNameInput(event) {
// Get the value of the input field
  const customerName = event.target.value;
  const inputId      = event.target.id;
  const inputElement = document.getElementById(inputId);
  const dropdownId   = 'dropdown_' + inputId;
  const existingDropdown = document.getElementById(dropdownId);
    if (existingDropdown) {
        existingDropdown.remove();
    }
        const existingDropdowns = document.querySelectorAll('.dropdown');
    existingDropdowns.forEach(dropdown => dropdown.remove());

        const dropdown = document.createElement('div');
        dropdown.id = dropdownId;
        dropdown.classList.add('dropdown');
        dropdown.style.position = 'absolute';
        dropdown.style.backgroundColor = '#fff';
        dropdown.style.border = '1px solid #ccc';
        dropdown.style.width = '200px';
        dropdown.style.zIndex = 1000;
        document.body.appendChild(dropdown);

     // Add it to the DOM

        const rect = inputElement.getBoundingClientRect();
        const scrollY = window.scrollY || document.documentElement.scrollTop;
        const scrollX = window.scrollX || document.documentElement.scrollLeft;
        dropdown.style.left = rect.left + scrollX + 'px';
        dropdown.style.top = rect.bottom + scrollY + 'px';
 // You can now use customerName to perform further actions
console.log(customerName);
// Example: If you need to make an asynchronous call
try {
    // Example: Fetch data from an API
    let response = await fetch('ajax/searchcustomers.php?query=' + encodeURIComponent(customerName));
        const customers = await response.json();

     dropdown.innerHTML = '';

     if (customers.length > 0) {
        dropdown.style.display = 'block';

        customers.forEach(customer => {
            const div = document.createElement('div');
            div.textContent = customer.name;
            div.dataset.code = customer.code;
            div.dataset.name = customer.name;
            div.dataset.currency = customer.currency;
            div.dataset.salespersoncode= customer.salespersoncode;

            div.addEventListener('click', function() {
                 document.getElementById('CustomerID').value = this.dataset.code;
                 document.getElementById('CustomerName').value = this.dataset.name;
                 dropdown.style.display = 'none';
            });

            dropdown.appendChild(div);
        });
    } else {
        dropdown.style.display = 'none';
    }
} catch (error) {
    console.error('Error fetching parameters:', error.message);
}

}

async function handlePricingInput(query,inputId) {
              console.log('inputId:', inputId);
           // Extract `labid` from `inputId`
            const rowid       = inputId.split('_'); // Expecting id format: PRC_<labid>
            const labid       = rowid[1]; // This will give the portion after the underscore
            const sampid      = document.getElementById('SAM_' + labid); // Fetch element with id SAM_<labid>
            const flexElement = document.getElementById("FLEX_"+labid);
            const sampidValue = sampid ? sampid.value : null; // Get its value, or null if not found
            const numberOfSamples = $('#numberofsamples').val(); // Assuming jQuery for this part
            //<input type="hidden" name="sampletype[LW005126]" id="SAM_LW005126" value="0111">
            try {
                 const data = {
                    sampleID: sampidValue, // Replace with actual value
                    Id: labid,      // Replace with actual value
                    PricingType: query , // Replace with actual value
                    numberOfSamples:numberOfSamples
                };

                console.log('pricings:', data);

                fetch('ajax/getLaboratoryStandardsAjax.php', {
                    method: 'POST', 
                    headers: {
                        'Content-Type': 'application/json', // Set content type to JSON
                    },
                    body: JSON.stringify(data) // Send data as JSON string
                })
                .then(response => response.text())  // Assuming the PHP script returns HTML content
                .then(result => {
                    // Handle the response, which will be the result from GetTests
                    console.log('flexElement:',result); // You can process the result here
                     if (flexElement) {
                        flexElement.innerHTML = ''; // Clear the content
                        flexElement.innerHTML = result; // Append the new content
                    } else {
                        console.error('Element with ID "FLEX_' + labid + '" not found.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });

            } catch (error) {
                console.error('Error fetching parameters:', error.message);
            }

}

function showtooltip(event){
    const customerName = event.target.value;
    if(customerName!='') generalPurposeTypeLine(customerName);
}

// Validate dynamic sample rows before submit
function validateSampleRows() {
  const rows = document.querySelectorAll('#sampleRows tr.main-row');
  if (rows.length === 0) {
    toastr.error('Please add at least one sample row.');
    return false;
  }

  let valid = true;
  let firstInvalidEl = null;

  rows.forEach((row, idx) => {
    const std = row.querySelector('input[name="StandardName[]"]');
    const samples = row.querySelector('input[name="samples[]"]');
    const kit = row.querySelector('input[name="standard_kit_units[]"]');
    const productBatch = row.querySelector('input[name="product_batch_no[]"]');
    const batchSize = row.querySelector('input[name="batch_size[]"]');
    const dom = row.querySelector('input[name="date_of_manufacture[]"]');
    const doe = row.querySelector('input[name="date_of_expiry[]"]');

    // helper to mark invalid
    const markInvalid = (el) => {
      if (!el) return;
      el.classList.add('is-invalid');
      if (!firstInvalidEl) firstInvalidEl = el;
      valid = false;
    };
    const markValid = (el) => {
      if (!el) return;
      el.classList.remove('is-invalid');
    };

    // required checks
    if (!std || !std.value.trim()) markInvalid(std); else markValid(std);
    if (!samples || !samples.value || parseInt(samples.value, 10) < 1) markInvalid(samples); else markValid(samples);
    if (!kit || !kit.value.trim()) markInvalid(kit); else markValid(kit);
    if (!productBatch || !productBatch.value.trim()) markInvalid(productBatch); else markValid(productBatch);
    if (!batchSize || !batchSize.value || parseInt(batchSize.value, 10) < 1) markInvalid(batchSize); else markValid(batchSize);

    // date logic: if both present, expiry must be > manufacture
    if (dom && doe && dom.value && doe.value) {
      const domDate = new Date(dom.value);
      const doeDate = new Date(doe.value);
      if (doeDate <= domDate) {
        markInvalid(doe);
        markInvalid(dom);
      } else {
        markValid(dom);
        markValid(doe);
      }
    } else {
      // require both DOM and DOE (if you prefer optional, remove these)
      if (!dom || !dom.value) markInvalid(dom); else markValid(dom);
      if (!doe || !doe.value) markInvalid(doe); else markValid(doe);
    }
  });

  if (!valid) {
    if (firstInvalidEl && typeof firstInvalidEl.scrollIntoView === 'function') {
      firstInvalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    toastr.error('Please fix the highlighted fields (all fields required, expiry must be after manufacture).');
  }
  return valid;
}

document.addEventListener("click", function(e) {
      if (e.target.tagName === "TD") {
          document.querySelectorAll("#sampleTable td").forEach(td => td.classList.remove("active"));
          e.target.classList.add("active");
      }
  });
