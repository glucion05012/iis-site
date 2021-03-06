
		<div class="container-fluid">
			<div class="row">
				<!-- DATATABLES Card -->
				<div class="col-xl-12 col-lg-12">
					<div class="trans-layout card shadow mb-4">
						<!-- Card Header - Dropdown -->
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">

							<h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-inbox"></i> INBOX TRANSACTIONS</h6>
							<!--  -->
							<a class="btn btn-outline-dark" href="#" data-toggle='modal' data-target='#add_transaction_confirm' title='ADD'> <i class="fa fa-plus"> </i>  Add Transaction </a>

						</div>

						<!-- Card Body -->
							<div class="card-body">

								<div class="row" >
									<div class="table-responsive">
										<table id="inbox_table" class="table table-striped table-hover" style="width: 100%" cellspacing="0">
											<thead>
												<tr>
													<th>no</th>
													<th style="width: 90px;"> IIS No. </th>
													<th>mro</th>
													<th>Company Name</th>
													<th>EMB ID</th>
													<th>Subject</th>
													<th>Transaction Type</th>
													<th>m_rcv</th>
													<th>rcv</th>
													<th>Status</th>
													<th>Action Taken</th>
													<th>Time/Date Forwarded</th>
													<th>Sender</th>
													<!-- <th>Time/Date Received</th> -->
													<th>Remarks</th>
													<th>mcntr</th>
													<th>main_multi_cntr</th>
													<th>multi_cntr</th>
													<th style="width: 130px">Action</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>

							</div>
						<!-- Card Body -->

					</div>

				</div>
			</div>
		</div>

	</div>
	<?php
		$swal_arr = $this->session->flashdata('swal_arr');
		if(!empty($swal_arr)) {
			echo "<script>
				swal({
					title: '".$swal_arr['title']."',
					html: '".$swal_arr['text']."',
					type: '".$swal_arr['type']."',
					allowOutsideClick: false,
					customClass: 'swal-wide',
					confirmButtonClass: 'btn-success',
					confirmButtonText: '".'<i class="fa fa-thumbs-up"></i> Great!'."',
					onOpen: () => swal.getConfirmButton().focus()
				})
			</script>";
		}
	?>
		<script>

			$(document).ready(function() {

			    var table = $('#inbox_table').DataTable({
							order: [[11, "desc"]],
							language: {
						    infoFiltered: "",
								processing: "<img src='<?php echo base_url('assets/images/loader/embloader.gif'); ?>' alt='load_logo' style='width:50px; height:50px;' />&nbsp;&nbsp;<img src='<?php echo base_url('assets/images/loader/prcloader.gif'); ?>' alt='load_prc' style='width:120px; height:50px;' />",
						  },
			        serverSide: true,
			        processing: true,
			        responsive: true,
			        deferRender: true,
							// "scrollY": 500,
        			"scrollX": true,
			        ajax: {
			            "url": "<?php echo base_url('Dms/Datatables/inbox'); ?>",
			            "type": 'POST',
									"data": {'user_token': '<?php echo $user_token; ?>' },
			        },
			        'columnDefs': [
					    {
					        'targets': 1,
					        'createdCell':  function (td, cellData, rowData, row, col) {
					           $(td).attr('id', 'iisno');
					        }
					    }
				  	],
			        columns: [
								{ "data": "trans_no", "searchable": false, "visible": false},
								{ "data": "token" },
								{ "data": "multiprc", "searchable": false, "visible": false},
								{ "data": "company_name" },
								{ "data": "emb_id",
									"render": function(data, type, row, meta) {
										var embid = data.split('-');
										return "<p title='"+data+"'>"+embid[0]+"-...-"+embid[2]+"</p>";
									}
								},
								{ "data": "subject"},
								{ "data": "type_description"},
								{ "data": "m_receive", "searchable": false, "visible": false},
								{ "data": "receive", "searchable": false, "visible": false},
								{ "data": "status_description"},
								{ "data": "action_taken"},
								{ "data": "date_forwarded"},
								{ "data": "sender_name",
									"render": function(data, type, row, meta) {
                    return data.toLowerCase().replace(/\b[a-z]/g, function(letter) { return letter.toUpperCase(); });
									}
								},
								// { "data": "date_received"},
								{ "data": "remarks"},
								{ "data": "m_cntr", "searchable": false, "visible": false },
								{ "data": "main_multi_cntr", "searchable": false, "visible": false },
								{ "data": "multi_cntr", "searchable": false, "visible": false },
								{
									"sortable": false,
									"render": function(data, type, row, meta) {
										if(row['multiprc'] > 0) {
											data = "<button type='button' id='viewbtn' class='btn btn-info btn-sm waves-effect waves-light' data-toggle='modal'  data-target='.viewTransactionModal'>(M)View</button>&nbsp;";
										}
										else {
											data = "<button type='button' id='viewbtn' class='btn btn-info btn-sm waves-effect waves-light' data-toggle='modal'  data-target='.viewTransactionModal'>View</button>&nbsp;";
										}

										if(row['m_cntr'] > 1) // multiple receiver routing
										{
											data += "<button type='button' id='m_rcvmodal_btn' class='btn btn-primary btn-sm waves-effect waves-light' data-toggle='modal'  data-target='.multprcToUserModal'>(M)Rcv/Prc</button>&nbsp;";
										}
										else // normal route flow
										{
											if(row['receive'] != 0 || (row['m_receive'] && row['m_receive'] != 0)) {
												if(row['type_description'] == 'TRAVEL ORDER' || row['type_description'] == 'SWEET REPORT'){
													if(row['type_description'] == 'TRAVEL ORDER'){
														data += "<button type='button' class='btn btn-success btn-sm waves-effect waves-light' data-toggle='modal' data-target='#process_travelorder' onclick='process_travel("+row['trans_no']+");'>Process</button>&nbsp;";
													}
													if(row['type_description'] == 'SWEET REPORT'){
														if(row['action_taken'] == 'Pls. for approval (NOV Letter)'){
															data += "<a href='<?= base_url("Swm/Sweet/index?searchnov="); ?>"+row['token']+"' class='btn btn-success btn-sm waves-effect waves-light' style='color:#FFF;'>Process</a>&nbsp;";
														}else{
															data += "<a href='<?= base_url("Swm/Sweet/index?search="); ?>"+row['token']+"' class='btn btn-success btn-sm waves-effect waves-light' style='color:#FFF;'>Process</a>&nbsp;";
														}
													}
												}else{
													if((row['m_receive'] && row['m_receive'] != 0)) {
														data += "<button type='button' id='m_prcbtn' class='btn btn-success btn-sm waves-effect waves-light' data-toggle='modal'  data-target='#process'>Process</button>&nbsp;";
													}
													else {
														data += "<button type='button' id='prcbtn' class='btn btn-success btn-sm waves-effect waves-light' data-toggle='modal'  data-target='#process'>Process</button>&nbsp;";
													}

												}
											}
											else {
												if((row['m_receive'] && row['m_receive'] != 1)) {
													data += "<button type='button' id='m_rcvbtn' class='inboxrcv-btn btn btn-primary btn-sm waves-effect waves-light' data-toggle='modal'  data-target='#receive'>Receive</button>&nbsp;";
												}
												else {
													if(row['type_description'] == 'SWEET REPORT'){
														if(row['action_taken'] == 'Pls. for approval (NOV Letter)'){
															data += "<a href='<?= base_url("Swm/Sweet/index?searchnov="); ?>"+row['token']+"' class='btn btn-success btn-sm waves-effect waves-light' style='color:#FFF;'>Process</a>&nbsp;";
														}else{
															data += "<a href='<?= base_url("Swm/Sweet/index?search="); ?>"+row['token']+"' class='btn btn-success btn-sm waves-effect waves-light' style='color:#FFF;'>Process</a>&nbsp;";
														}
													}else{
														data += "<button type='button' id='rcvbtn' class='inboxrcv-btn btn btn-primary btn-sm waves-effect waves-light' data-toggle='modal'  data-target='#receive'>Receive</button>&nbsp;";
													}
												}

											}
										}

										return data;
									}
								}
			        ]
			    });


			    $('#inbox_table tbody').on( 'click', '#viewbtn', function () {
			        var data = table.row( $(this).parents('tr') ).data();
							Dms.view_transaction( data['trans_no'], data['multiprc'] );
			    } );

			    $('#inbox_table tbody').on( 'click', '#prcbtn', function () {
			        var data = table.row( $(this).parents('tr') ).data();
							$.ajax({
					       url: Dms.base_url + 'Dms/set_trans_session',
					       method: 'POST',
					       data: { trans_no : data['trans_no'] },
								 success: function() {
									 window.location.href = Dms.base_url + 'Dms/route_transaction';
								 }
					    });
				 } );

				 $('#inbox_table tbody').on( 'click', '#m_prcbtn', function () {
						 var data = table.row( $(this).parents('tr') ).data();
						 $.ajax({
								url: Dms.base_url + 'Dms/m_set_trans_session',
								method: 'POST',
								data: { trans_no : data['trans_no'], main_multi_cntr : data['main_multi_cntr'], multi_cntr : data['multi_cntr'] },
								success: function() {
									window.location.href = Dms.base_url + 'Dms/route_transaction';
								}
						 });
				} );

				$('#inbox_table tbody').on( 'click', '#rcvbtn', function () {
						var data = table.row( $(this).parents('tr') ).data();
						var rcv = Dms.receive_transaction( $(this), data['trans_no'], data['multiprc'] );
			        if(rcv == 1) {
								setTimeout(function(){
						        table.ajax.reload(null, false);
						    }, 700);
							}
				 } );

				 $('#inbox_table tbody').on( 'click', '#m_rcvbtn', function () {
 						var data = table.row( $(this).parents('tr') ).data();
 						var rcv = Dms.m_receive_transaction( $(this), data['trans_no'], data['main_multi_cntr'], data['multi_cntr'] );

						if(rcv == 1) {
							setTimeout(function(){
									table.ajax.reload(null, false);
							}, 700);
						}
 				 } );

				$('#inbox_table tbody').on( 'click', '#m_rcvmodal_btn', function () {
						var data = table.row( $(this).parents('tr') ).data();
						$.ajax({
							url: Dms.base_url + 'Data/multiprc_to_user',
							method: 'POST',
							dataType: 'json',
							data: { trans_no : data['trans_no'] },
							success: function(data) {
							var xhtml = '';

							for(var x = 0; x < data.length; x++) {

								var xbtn = '<button type="button" id="m_rcvbtn2" class="btn btn-primary btn-sm waves-effect waves-light" >Receive & Process</button>&nbsp';

								if(data[x].receive != 0) {
									xbtn = "<button type='button' id='m_prcbtn2' class='btn btn-success btn-sm waves-effect waves-light'>(M)Process</button>&nbsp;";
								}

								xhtml +='<tr>'+
								'<td class="trans_no" style="display:none;">'+data[x].trans_no+'</td>'+
								'<td>'+data[x].token+'</td>'+
								'<td style="display:none;">'+data[x].multiprc+'</td>'+
								'<td class="main_multi_cntr" style="display:none;">'+data[x].main_multi_cntr+'</td>'+
								'<td class="multi_cntr" style="display:none;">'+data[x].multi_cntr+'</td>'+
								'<td style="display:none;">'+data[x].company_name+'</td>'+
								'<td style="display:none;">'+data[x].emb_id+'</td>'+
								'<td style="display:none;">'+data[x].subject+'</td>'+
								'<td style="display:none;">'+data[x].type_description+'</td>'+
								'<td style="display:none;">'+data[x].receive+'</td>'+
								'<td>'+data[x].status_description+'</td>'+
								'<td style="display:none;">'+data[x].action_taken+'</td>'+
								// '<td>'+data[x].date_forwarded+'</td>'+
								'<td>'+data[x].sender_name+'</td>'+
								// '<td style="display:none;">'+data[x].date_received+'</td>'+
								'<td>'+data[x].remarks+'</td>'+
								'<td>'+xbtn+'</td>'+
								'</tr>';
							}
							$('#mprctouser_table').html(xhtml);
							}
						});
				 } );

				 $('#mprctouser_table').on( 'click', '#m_rcvbtn2', function () {
						var trans_no = $(this).closest("tr").find(".trans_no").text();
						var main_multi_cntr = $(this).closest("tr").find(".main_multi_cntr").text();
						var multi_cntr = $(this).closest("tr").find(".multi_cntr").text();

						var rcv = Dms.m_receive_transaction( $(this), trans_no, main_multi_cntr, multi_cntr );

						if(rcv == 1) {
							$(this).closest("td").html("<button type='button' id='m_prcbtn2' class='btn btn-success btn-sm waves-effect waves-light'>(M)Process</button>&nbsp;");
						}
				 } );

				 $('#mprctouser_table').on( 'click', '#m_prcbtn2', function () {
						var trans_no = $(this).closest("tr").find(".trans_no").text();
						var main_multi_cntr = $(this).closest("tr").find(".main_multi_cntr").text();
						var multi_cntr = $(this).closest("tr").find(".multi_cntr").text();
					 $.ajax({
							url: Dms.base_url + 'Dms/m_set_trans_session',
							method: 'POST',
							data: { trans_no : trans_no, main_multi_cntr : main_multi_cntr, multi_cntr : multi_cntr },
							success: function() {
								window.location.href = Dms.base_url + 'Dms/route_transaction';
							}
					 });
				} );

			} );
			</script>
