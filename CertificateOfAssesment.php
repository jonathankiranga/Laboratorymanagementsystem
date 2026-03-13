<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Generate PDF</title>
<script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/all.min.js"></script>
<style>
.bank-statement-container {
    overflow-x: auto; /* Enable horizontal scrolling */
    background: white; /* White background for the table */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    margin-bottom: 20px; /* Space below the table */
}

.bank-statement {
    width: 100%;
    border-collapse: collapse;
}

.bank-statement thead {
    background-color: #3498db; /* Header background color */
    color: white;
}

.bank-statement th, .bank-statement td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
    white-space: nowrap; /* Prevent text wrapping */
}

.bank-statement th {
    font-weight: bold;
}

.bank-statement tr:nth-child(even) {
    background-color: #f2f2f2; /* Zebra striping */
}

.bank-statement tr:hover {
    background-color: #e0f7fa; /* Highlight on hover */
}

.pagination {
    display: flex;
    justify-content: center; /* Center pagination links */
    margin: 20px 0;
}

.pagination a {
    padding: 10px 15px;
    text-decoration: none;
    color: #3498db;
    border: 1px solid #ddd;
    border-radius: 5px; /* Rounded corners */
    margin: 0 5px;
    transition: background-color 0.3s; /* Smooth hover effect */
}

.pagination a:hover {
    background-color: #3498db; /* Change background on hover */
    color: white; /* Change text color on hover */
}

.pagination strong {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #e0e0e0; /* Highlight current page */
    color: #555; /* Darker color for current page */
}

#progress-container {
    display: none; /* Initially hidden */
    position: fixed; /* Fixed position on the screen */
    top: 50%; /* Center vertically */
    left: 50%; /* Center horizontally */
    transform: translate(-50%, -50%); /* Adjust position to truly center */
    background: rgba(255, 255, 255, 0.9); /* Optional: semi-transparent background */
    padding: 20px; /* Add some padding */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    text-align: center; /* Center-align text inside */
    z-index: 1000; /* Ensure it appears above other elements */
}

#progress-bar {
    width: 100%; /* Full width of the container */
    height: 20px; /* Adjust height as needed */
    border: 1px solid #ccc; /* Optional: border */
    border-radius: 5px; /* Rounded corners */
}
    
</style>
</head>
<body>

<div class="container">
    <input type="text" id="searchQuery" placeholder="Search Sample ID or Parameter Name">
    <button id="searchButton"><i class="fa-solid fa-magnifying-glass"></i></button><div class="btn btn-icon action-item" id="displayactiverow"></div>
    <h2>Approved Samples</h2>

    <div id="progress-container" style="display: none;">
       <label for="progress-bar">Data Transfer Progress:</label>
       <progress id="progress-bar" value="0" max="100"></progress>
   </div>
         
        
        <table class="table table-hover" id="data-table">
            <thead>
                <tr>
                    <th>Date of Registration</th>
                    <th>Batch No</th>
                    <th>Sample ID</th>
                    <th>Customer Name</th>
                    <th>Sample Source</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows will be dynamically populated -->
            </tbody>
        </table>
        
        <!-- Pagination Placeholder -->
    <nav aria-label="Table pagination">
        <ul class="pagination" id="pagination">
            <!-- Pagination buttons will be dynamically added here -->
        </ul>
    </nav>
    </div>

<div class="modal fade" id="casModal" tabindex="-1" aria-labelledby="casModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="casModalLabel">Certificate of Analysis Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                 <input type="hidden" name="optionprint" id="optionprint">
                <p>Select the type of Certificate of Analysis (COA):</p>
                <div class="list-group">
                    <button class="list-group-item list-group-item-action" id="casNoPic">
                        Choose Report Type<i class="fas fa-file-alt"></i>COA without Picture
                    </button>
                    <button class="list-group-item list-group-item-action" id="casWithPic">
                        Choose Report Type<i class="fas fa-image"></i>COA with Picture
                    </button>
                    
                    <button class="list-group-item list-group-item-action" id="casByBatch">
                        Choose Report Type<i class="fas fa-layer-group"></i>COA without Picture and Color Intepretation
                    </button>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button"  class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    
    $(document).ready(function() {
        fetchData(1,'');
    });

   // Fetch data function
    function fetchData(page, query) {
        $.ajax({
            url: 'ajax/getlabreportsApprovedAjax.php',
            method: 'GET',
            data: {
                page: page,
                query: query
            },
            dataType: 'json',
            success: function (response) {
                if(response.sucess){
                    displayResults(response.samples);
                    setupPagination(response.total_pages, page);
                  }else{
                       setupPagination(1, page);
                       toastr.error(response.message || "Failed to get results.");
                  }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data: ", error);
            }
        });
    }

    // Display results function
    function displayResults(samples) {
        let tableBody = $("#data-table tbody");
        tableBody.empty();
        samples.forEach(sample => {
            tableBody.append(`
                <tr data-DocumentNo="${sample.DocumentNo}" data-resultsid="${sample.resultsID}">
                    <td>${formatDate(sample.Date)}</td>
                    <td>${sample.DocumentNo}</td>
                    <td>${sample.ResultSampleID}</td>
                    <td>${sample.customer}</td>
                    <td>${sample.ExternalSample || ''}</td>
                    <td><button class="printpdf" data-sampleid="${sample.TestID}">
                    <i class="fa-solid fa-file-pdf"></i></button></td>
                </tr>
            `);
        });
    }

    // Setup pagination
    function setupPagination(totalPages, currentPage) {
        $('#pagination').empty();
        for (let page = 1; page <= totalPages; page++) {
            $('#pagination').append(`<a href="#" class="page-link" data-page="${page}">${page}</a> `);
        }

        $('.page-link').on('click', function (e) {
            e.preventDefault();
            currentPage = $(this).data('page');
            fetchData(currentPage, $('#searchQuery').val());
        });
    }
    
    
    document.getElementById("searchButton").addEventListener("click", function () {
         const searchQuery = $("#searchQuery").val();
        fetchData(1,searchQuery);
    });
    
    document.getElementById("casNoPic").addEventListener("click", function () {
         const sampleID = $("#optionprint").val();
         const modalElement = document.getElementById('casModal');
         const modal = bootstrap.Modal.getInstance(modalElement);
 // Hide the modal
          modal.hide();
         getreportwithoption(sampleID,1);
    });

    document.getElementById("casWithPic").addEventListener("click", function () {
        const sampleID = $("#optionprint").val();
        const modalElement = document.getElementById('casModal');
    const modal = bootstrap.Modal.getInstance(modalElement);

    // Hide the modal
    modal.hide();
        getreportwithoption(sampleID,2);
    });

    document.getElementById("casByBatch").addEventListener("click", function () {
           const sampleID = $("#optionprint").val();
           const modalElement = document.getElementById('casModal');
           const modal = bootstrap.Modal.getInstance(modalElement);
           modal.hide();
           getreportwithoption(sampleID,3);
    }); 

    $("#data-table").on("click", ".printpdf", function () {
        const activeRow = $(this); // Use 'this' to get the clicked button
        const sampleID = activeRow.data("sampleid");
        $("#optionprint").val(sampleID);
        const modal = new bootstrap.Modal(document.getElementById('casModal'), { backdrop: 'static', keyboard: false });
        modal.show(); // Show the modal
    }); // <- Added closing parenthesis here
  
    function getreportwithoption(sampleID,reportoption){
    // Show the progress bar
    $("#progress-container").show();
    $("#progress-bar").val(0);

    $.ajax({
        url: `functions/certificateofanalysis${reportoption}.php`, // Adjust the path as necessary
        method: 'POST',
        data: {
            sampleID: sampleID,
            reportoption: reportoption
        },
        xhr: function () {
            const xhr = new XMLHttpRequest();

            // Attach progress event listener
            xhr.upload.addEventListener("progress", function (e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    $("#progress-bar").val(percentComplete);
                }
            });

            xhr.addEventListener("progress", function (e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    $("#progress-bar").val(percentComplete);
                }
            });

            return xhr;
        },
        xhrFields: {
            responseType: 'blob' // Important for handling binary data
        },
        success: function (data) {
            // Create a blob URL for the PDF
            const blob = new Blob([data], { type: 'application/pdf' });
            const url = URL.createObjectURL(blob);

            // Open the PDF in a new tab
            window.open(url);

            // Hide the progress bar
            $("#progress-container").hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error generating PDF:', textStatus, errorThrown);

            // Hide the progress bar
            $("#progress-container").hide();
        }
    });
}

        
</script>

</body>
</html>
