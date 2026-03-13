<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Registration</title>
    <link href="css/sampleregistration.css" rel="stylesheet">
    <link href="css/typing.css" rel="stylesheet"> 
    <script src="js/fetchTransactionData.js"  type="text/javascript"></script>
</head>
<body>
<div class="card">
    <div class="card-header">
</div>
  <div class="card-body" style="max-height: 500px; overflow-y: auto;">
        <form id="labform" enctype="multipart/form-data">
            <div class="table-responsive">
                <table class="table-sm">
                    <tbody>
                        <tr>
                            <td><label>DATE:</label></td>
                            <td>
                                <input tabindex="1" type="date" name="date" class="form-control-sm" size="11" maxlength="11" autofocus="autofocus" autocapitalize="none" autocorrect="off" spellcheck="false">
                            </td>
                        </tr>
                        <tr>
                            <td><label>Batch Ref:</label></td>
                            <td>
                                <input type="text" name="documentno" id="documentno" class="form-control-sm" size="10" readonly="readonly">
                            </td>
                        </tr>
                        <tr>
                            <td><label>Client Account:</label></td>
                            <td>
                                <input tabindex="4" type="text" name="CustomerName" id="CustomerName" class="form-control-sm" value="" placeholder="Search a customer name" autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false"  onkeyup="handleCustomerNameInput(event)">
                                <input type="hidden" name="CustomerID" id="CustomerID">
                                <input type="hidden" name="tablecount" id="tablecount">
                     
                            </td>
                        </tr>
                        <tr>
                            <td><label>Sampled By:</label></td>
                            <td>
                                <input type="text" name="sampledby" class="form-control-sm" value="" size="20" autocapitalize="none" autocorrect="off" spellcheck="false">
                            </td>
                        </tr>
                        <tr>
                            <td><label>Sampling Method:</label></td>
                            <td>
                                <input type="text" name="SamplingMethod" class="form-control-sm" value="" size="20" autocapitalize="none" autocorrect="off" spellcheck="false">
                            </td>
                        </tr>
                        <tr>
                            <td><label>Sampling Date:</label></td>
                            <td>
                                <input type="date" name="samplingdate" class="form-control-sm" size="11" maxlength="10" autocapitalize="none" autocorrect="off" spellcheck="false">
                            </td>
                        </tr>
                        <tr>
                            <td><label>Customer LPO No:</label></td>
                            <td>
                                <input type="text" name="Orderno" class="form-control-sm" value="" size="20" autocapitalize="none" autocorrect="off" spellcheck="false">
                            </td>
                        </tr>
                        
                        <tr><td colspan="2">
                                        <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addSampleRow()">
                                          <i class="fa fa-plus"></i> Add Sample
                                        </button>
                                           <button class="btn btn-outline-primary btn-sm mb-3" type="button" id="new_customer">
                                            <i class="fa fa-plus"></i> Create New Customer
                                        </button>
                                       <div id="sampleTableContainer" style="max-height:300px; overflow-y:auto;">
                                         <table class="table table-bordered table-hover table-sm align-middle" id="sampleTable">
                                          <thead class="table-light">
                                            <tr>
                                              <th style="min-width: 200px;">Standard Name</th>
                                                <th style="min-width: 150px;">Matrix Package</th>
                                                <th style="min-width: 100px;">Number of Samples</th>
                                                <th style="min-width: 150px;">Standard Kit Units</th>
                                                <th style="min-width: 150px;">Product Batch No</th>
                                                <th style="min-width: 120px;">Batch Size</th>
                                                <th style="min-width: 160px;">Date of Manufacture</th>
                                                <th style="min-width: 160px;">Date of Expiry</th>
                                                <th style="min-width: 250px;">Picture of Sample</th>
                                                <th style="min-width: 150px;">Sample Source</th>
                                                <th style="min-width: 80px;">Action</th>
                                            </tr>
                                          </thead>
                                          <tbody id="sampleRows"></tbody>
                                        </table>
                                       </div>
                                        <br>
                                        <hr>
                                        <p><div id="registrationsumary"></div></p>
                                         
                                        
                                    </td>
                        </tr>
                       
                       <tr><td colspan="2">
                                <div id="samplehtml"></div>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
            <hr>
            <div>
                <button type="submit" id="mypostid" name="post" class="btn btn-primary"><i class="fas fa-save"></i>🚀 SAVE</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal HTML -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <form id="customerForm">
                    <table class="table table-sm"><tbody>
                   <tr><td valign="top">
                   <table>
                       <tbody>
                           <tr><td>Name</td><td><input type="text" name="customer" maxlength="50" required="required"></td></tr>
                           <tr><td>Address</td><td><input type="text" name="company" maxlength="50"></td></tr>
                           <tr><td>Address 2</td><td><input type="text" name="postcode" maxlength="100"></td></tr>
                           <tr><td>city</td><td><input type="text" name="city" maxlength="50"></td></tr>
                           <tr><td>Country</td><td><select name="country" id="countrySelect"></select></td></tr>
                           <tr><td>Telephone No</td><td><input type="text" name="phone" maxlength="15"></td></tr>
                           <tr><td>Alt Contact</td><td><input type="text" name="altcontact" maxlength="100"></td></tr>
                           <tr><td>email</td><td><input type="text" name="email" maxlength="100" pattern="[a-z0-9!#$%&amp;'*+/=?^_{|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)*"></td></tr>
                       </tbody></table>
                       </td><td valign="top">
                        <table><tbody>
                           <tr><td>Discount Rate</td><td><input type="text" class="integer" name="creditlimit" maxlength="2"></td></tr>
                           <tr><td>Customer Currency:</td><td><select name="curr_cod" required="required"><option value="EUR">Euro</option><option value="GBP">Pounds</option><option selected="selected" value="KES">Kenyan Shillings</option><option value="USD">US Dollars</option></select></td></tr>
                           <tr><td>Block Account</td><td><select name="inactive"><option value="0">No</option><option value="1">Yes</option></select></td></tr>
                           <tr><td>PIN</td><td><input type="text" name="middlen" maxlength="15"></td></tr>
                       </tbody></table></td>
                   </tr>
                  </tbody>
                </table>
                     <button type="submit" class="btn btn-primary">Save Customer</button>
              </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
fetchTransactionData('GetTempLabrefNo','10');

      // Event listener for the "Add New Customer" button
        $('#new_customer').click(function() {
            const modal = new bootstrap.Modal(document.getElementById('modal'), { backdrop: 'static', keyboard: false});
            modal.show(); // Show the modal
        });


</script>
<script src="js/sampleregistration.js?v=<?php echo filemtime(__DIR__ . '/js/sampleregistration.js'); ?>" type="text/javascript"></script>
     
</body>
</html>
