<style>
/* Excel-like colors */
table.dataTable {
    border-collapse: collapse;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    font-size: 13px;
}

table.dataTable th,
table.dataTable td {
    border: 1px solid #ccc;
    padding: 6px 8px;
    text-align: center;
}

table.dataTable th {
    background-color: #f2f2f2;
    font-weight: bold;
}

table.dataTable tbody tr:nth-child(odd) {
    background-color: #ffffff;
}

table.dataTable tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.dataTables_wrapper .dt-buttons {
    margin-bottom: 10px;
}

.container h5 {
    margin-bottom: 20px;
    font-weight: bold;
}

.mb-3 {
    margin-bottom: 15px;
}

.table th, .table td {
    text-align: center;
}

.table th {
    width: 120px; /* Set fixed width for column headers */
}

.table td {
max-width: 150px; /* Set maximum width for cells */
padding: 0; /* Remove padding to make space for content */
}

.scrollable-content {
    overflow: hidden; /* Initially hide overflow */
    white-space: nowrap; /* Prevent text wrapping */
    text-overflow: ellipsis; /* Show ellipsis for truncated text */
    display: block; /* Ensure block display for the content */
    max-width: 100%; /* Full width of the table cell */
    padding: 5px; /* Add padding inside the div */
    border: 1px solid #ccc; /* Subtle border for the content */
    background-color: #f9f9f9; /* Background color */
    color: #333; /* Text color */
    box-sizing: border-box; /* Include padding and border in width/height */
    font-family: Arial, sans-serif; /* Font style */
    font-size: 14px; /* Font size */
    max-height: 30px; /* Collapsed height */
    transition: max-height 0.3s ease; /* Smooth transition for expanding */
    cursor: pointer; /* Pointer cursor to indicate focusable element */
}

.scrollable-content:focus {
    overflow: visible; /* Show all content */
    white-space: normal; /* Allow text to wrap */
    max-height: none; /* Remove height restriction */
    outline: none; /* Remove default outline for better appearance */
    background-color: #fff; /* Optional: Change background color on focus */
    border-color: #007BFF; /* Optional: Highlight border on focus */
}

/* Responsive table design */
@media (max-width: 768px) {
    .table th, .table td {
        padding: 10px;
    }
}

.table {
    table-layout: fixed;
}

.table-bordered {
    border: 1px solid #ddd;
}

.table-striped tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}

#approveModal div {
    font-family: Arial, sans-serif; font-size: 14px; line-height: 1; padding: 5;
}

#approveModal {
      z-index: 1051; /* Default Bootstrap modals are at 1050 */
}
</style>
<i class="fas fa-project-diagram"></i>Lab Result Workflow
<!-- Button to trigger modal -->
<div class="container mt-4">
    <div class="grid-container">
<div class="search-container">
    <input type="text" id="searchInput" class="form-control" placeholder="Search results..." onkeyup="searchTable()">
</div> 
<table id="testResultsTable" class="table table-bordered table-striped">
    <thead class="thead-dark">
        <tr>  
            <th>Action</th>
            <th>#</th>
            <th>Sample Batch No</th>
            <th>Sample ID</th>
            <th>Date</th>
            <th>Parameter</th>
            <th>Quantitative Result</th>
            <th>Qualitative Status</th>
            <th>Range Result</th>
            <th>SampledBy</th>
            <th>Sampling Method</th>
            <th>Sampling Date</th>
            <th>OrderNo</th>
            <th>SKU</th>
            <th>BatchNo</th>
            <th>Batch Size</th>
            <th>Manufacture Date</th>
            <th>Expiry Date</th>
            <th>External Sample ID</th>
            <th>Tested By</th>
        </tr>
    </thead>
    <tbody>
        <!-- Rows will be dynamically generated -->
    </tbody>
</table>
        
<div class="pagination" id="pagination"></div>

    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="neutralityModal" tabindex="-1" role="dialog" aria-labelledby="neutralityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="neutralityModalLabel">Neutrality Calculator</h5>
      </div>
      <div class="modal-body">
        <!-- Load the page as an iframe -->
        <iframe src="neutralitycalculator.php" style="width: 100%; height: 500px; border: none;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="tdscalculatorModal" tabindex="-1" role="dialog" aria-labelledby="tdscalculatorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tdscalculatorModalLabel">TDS Calculator</h5>
      </div>
      <div class="modal-body">
        <!-- Load the page as an iframe -->
        <iframe src="tdscalculator.php" style="width: 100%; height: 500px; border: none;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal for Approving Test Results -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Review and Approve Test Result</h5>
                <button type="button" class="btn btn-secondary btn-sm"  id="CMDneutralityModal">
                    Open Neutrality Calculator
                  </button>
                <button type="button" class="btn btn-secondary btn-sm"  id="CMDtdscalculatorModal">
                    Open TDS Calculator
                  </button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approveForm">
                    <input type="hidden" id="resultsID" name="resultsID">
                    <input type="hidden" id="flag" name="flag">
                    <input type="hidden" id="resultType" name="resultType">
                    <div class="mb-3">
                        <label for="sampleID" class="form-label">Sample ID</label>
                        <input type="text" id="sampleID" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="parameter" class="form-label">Parameter</label>
                        <input type="text" id="parameter" class="form-control" readonly>
                    </div>
                    <div class="container mt-4">
                        <h5 class="text-center">Test Result Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="mrlResult" class="form-label">Quantitative</label>
                                    <input type="text" id="mrlResult"  name="mrlResult" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="resultStatus" class="form-label">Qualitative</label>
                                    <select id="resultStatus" name="resultStatus" class="form-control">
                                        <option value="">N/A</option>
                                        <option value="ND">Not Detected</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Detected">Detected</option>
                                        <option value="Below Limit">Below Limit</option>
                                        <option value="Detected Range">Detected Range</option>
                                        <option value="Trace">Trace</option>
                                        <option value="Above Limit">Above Limit</option>
                                        <option value="Inconclusive">Inconclusive</option>
                                        <option value="Error">Error</option>
                                        <option value="Invalid">Invalid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rangeResult" class="form-label">Range</label>
                                    <input type="text" id="rangeResult" name="rangeResult" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mw-100">
                                <label for="standtocompare" class="form-label">Standard Limits</label>
                                <input type="text" id="standtocompare" class="form-control" readonly>
                            </div>                       
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="approvalStatus" class="form-label">Approval Status</label>
                        <select id="approvalStatus" name="approvalStatus" class="form-select" required>
                            <option value="">Select</option>
                            <option value="1">Approved</option>
                            <option value="2">Reanalysis Required</option>
                            <option value="3">Error Corrected</option>
                            <option value="4">Rejected</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-success" onclick="submitApproval()">Submit Approval</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    
    
function searchTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('testResultsTable');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell && cell.textContent.toLowerCase().includes(searchInput)) {
                    found = true;
                    break;
                }
            }

            if (found) {
                rows[i].style.display = ''; // Show row
            } else {
                rows[i].style.display = 'none'; // Hide row
            }
        }
    }

function disableOtherFields(selectedId) {
    const fields = ['mrlResult','resultStatus','rangeResult'];
    document.getElementById('resultType').value = selectedId;
    
    fields.forEach(field => {
        document.getElementById(field).disabled = (field !== selectedId);
    });
}

function reloadResults(){
    const fields = ['mrlResult', 'resultStatus', 'rangeResult'];
    
    fields.forEach(field => {
        document.getElementById(field).disabled = false;
    });
}

// Attach event listeners to fields
document.getElementById('mrlResult').addEventListener('input', () => disableOtherFields('mrlResult'));
document.getElementById('resultStatus').addEventListener('input', () => disableOtherFields('resultStatus'));
document.getElementById('rangeResult').addEventListener('input', () => disableOtherFields('rangeResult'));


function initDataTable() {
    $('#testResultsTable').DataTable({
        dom: 'Bfrtip',       // Button + filter + table
        paging: true,         // Enable pagination
        pageLength: 15,       // Rows per page
        lengthMenu: [10, 15, 25, 50, 100],
        ordering: true,       // Enable column sorting
        autoWidth: false,     // Excel-like fixed width columns
        scrollX: true,        // Horizontal scroll for many columns
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5'
        ],
        columnDefs: [
            { targets: 0, orderable: false }, // Disable sorting on Action column
        ]
    });
}


function fetchTestResults(flag) {
     const tbody = document.querySelector('#testResultsTable tbody');
     //approve_test_result_2.php
    fetch(`ajax/get_test_results.php?statusID=${flag}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                data.results.forEach((result, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td>
                            <button class="btn btn-primary btn-sm" onclick="openApprovalModal(${result.resultsID}, ${flag})">
                                 ${getButtonLabel(flag)}
                            </button>
                        </td>
                        <td>${index + 1}</td>         
                        <td>${result.DocumentNo}</td>                        
                        <td>${result.SampleID}</td>
                        <td>${new Date(result.Date.split(' ')[0]).toISOString().split('T')[0]}</td>
                        <td><div class="scrollable-content" tabindex="0">${result.StandardName+':'+result.ParameterName}</div></td>
                        <td>${result.MRL_Result || 'N/A'}</td>
                        <td>${result.ResultStatus || 'N/A'}</td>
                        <td>${result.RangeResult || 'N/A'}</td>
                        <td>${result.SampledBy}</td>
                        <td>${result.SamplingMethod}</td>
                        <td>${new Date(result.SamplingDate.split(' ')[0]).toISOString().split('T')[0]}</td>
                        <td>${result.OrderNo}</td>
                        <td>${result.SKU}</td>
                        <td>${result.BatchNo}</td>
                        <td>${result.BatchSize}</td>
                       <td>${new Date(result.ManufactureDate.split(' ')[0]).toISOString().split('T')[0]}</td>
                       <td>${new Date(result.ExpDate.split(' ')[0]).toISOString().split('T')[0]}</td>
                       <td>${result.ExternalSample}</td>
                        <td>${result.User_name}</td> `;
                    tbody.appendChild(row);
                });
                
                
                // Destroy previous instance if exists to avoid duplication
                if ($.fn.DataTable.isDataTable('#testResultsTable')) {
                    $('#testResultsTable').DataTable().destroy();
                }
                initDataTable();
            } else {
                 tbody.innerHTML = '';
                toastr.error('No results to display.');
            }
        })
        .catch(error => console.error('Error fetching test results:', error));
}


function getButtonLabel(flag) {
    let label = 'Approve'; // Default label
    switch (flag) {
        case 2:
            label = 'Review'; // If flag is 2, change the label to 'Review'
            break;
        default:
            label = 'Approve'; // Default to 'Approve'
            break;
    }
    return label;
}

function openApprovalModal(resultsID,flag) {
    reloadResults();
    fetch(`ajax/get_test_resultByID.php?resultsID=${resultsID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const result = data.result;
                document.getElementById('flag').value = flag;  // Store flag value
                document.getElementById('resultsID').value = result.resultsID;
                document.getElementById('sampleID').value = result.SampleID;
                document.getElementById('parameter').value = result.StandardName+':'+result.ParameterName;
                document.getElementById('mrlResult').value = result.MRL_Result || 'N/A';
                setOption(result.ResultStatus || 'N/A') ;
                document.getElementById('rangeResult').value = result.RangeResult || 'N/A';
                document.getElementById('standtocompare').value = 'Limits:' +(result.Limits || 'N/A') + '  units of measure:' + (result.UnitOfMeasure || 'N/A') ;
                 
                const approveModal = new bootstrap.Modal($('#approveModal'), { backdrop: 'static', keyboard: false });
                 approveModal.show();
            } else {
                toastr.error('Failed to load test result details.');
            }
        })
        .catch(error => console.error('Error loading test result details:', error));
}

function submitApproval() {
    const flag = document.getElementById('flag') ; 
    const formData = new FormData(document.getElementById('approveForm'));
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i); // Get the key
        const value = localStorage.getItem(key); // Get the corresponding value
        formData.append(key, value); // Append the key-value pair to formData
    }

    const approve_test_result=`approve_test_result_${flag.value}.php`;
 
    fetch(`ajax/${approve_test_result}`, {
        method:'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
             toastr.success(data.message);
            
            const approveModal = new bootstrap.Modal($('#approveModal'), { backdrop: 'static' , keyboard: false});
                 approveModal.hide();
                 
            fetchTestResults(flag.value); // Reload the table
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => console.error('Error submitting approval:', error));
}
  


function setOption(resultValue) {
    const selectElement = document.getElementById('resultStatus');
    // Check if resultValue exists in the select options
    const optionExists = Array.from(selectElement.options).some(option => option.value === resultValue);
    // Set the option if it exists, otherwise set it to 'N/A'
    selectElement.value = optionExists ? resultValue : '';
}

fetchTestResults(2);


$('#CMDneutralityModal').click(function () {
   const neutralityModal = new bootstrap.Modal($('#neutralityModal'), { backdrop: 'static', keyboard: false });
   neutralityModal.show();
});

$('#CMDtdscalculatorModal').click(function () {
    const tdscalculatorModal = new bootstrap.Modal($('#tdscalculatorModal'), { backdrop: 'static', keyboard: false });
    tdscalculatorModal.show();
});



</script>