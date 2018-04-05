<?php
/**
 * Project
 *
 * @author Leeshan Subramunier <leeshan@astralgroup.co.za>
 * @version 1.0
 * @package Project
 */

# =========================================================================
# SETTINGS
# =========================================================================

# Start Session
ini_set("session.save_handler", "files");
session_start();

# Include Required Scripts
include_once ("../include.php");

# =========================================================================
# FUNCTIONS
# =========================================================================

function get_percentage_class($percentage){
	if($percentage < 10){
		$class 														= "danger";
	}
	elseif($percentage < 70){
		$class 														= "info";
	}
	else{
		$class 														= "success";	
	}

	return $class;
}
function get_agent_stats(){
	# Global Variables
	global $cur_page, $_db, $validator;
	
	# Get Data 
	$agent_id  															= $validator->validate($_GET['agent'], "String");
	
	# Local Variables
	$date 																= date("Y-m-d");
	$agent  															= new Agent($agent_id);
	$class 																= "";
	$no_sales 															= "";
	$no_leads 															= "";
	//Get Agents Sales
	$sale_query 														= "SELECT 
																					COUNT(`id`)
																		   FROM 
																		   			`sales`
																		   WHERE 
																		   			`agent_id` = '{$agent_id}'
																		   AND 
																		   			`sale_date` BETWEEN '{$date} 00:00:00' AND '{$date} 23:59:59'
																		  ";

	$sale_data 															= $_db->fetch_single($sale_query);
	$sale_percentage 													= number_format($sale_data / $agent->target * 100,2);																	  
	if($sale_percentage > 0){
		$class 															= get_percentage_class($sale_percentage);
	}
	else{
		$sale_percentage 												= 0;
		$no_sales 														= "<strong class='pull-right'><font color='red'>No Sales Made Today</font></strong>";
	}

	//Get Agents Lead Stats
	$lead_query 														= "SELECT 
																					COUNT(`id`) as 'count'
																		   FROM 
																		   			`leads` 
																		   WHERE 
																		   			`agent_id` = '{$agent_id}'
																		   AND 
																		   			`active` = 1 
																		  ";
	$lead_data 															= $_db->fetch_single($lead_query);
	$lead_percentage 													= number_format($lead_data/$agent->lead_limit * 100,2);
	$lead_limit 														= isset($agent->lead_limit)?$agent->lead_limit: '0';
	if($lead_data > 0 && $lead_limit > 0){
		$lead_class 													= get_percentage_class($lead_percentage);
	}
	else{
		$lead_percentage 												= 0;
		if(!$lead_data){
			$no_leads 												   .= "<strong class='pull-right'><font color='red'>Agent Currently Has No Leads</font></strong><br>";
		}
		if($lead_limit == '0'){
			$no_leads 												   .= "<strong class='pull-right'><font color='red'>Agent Lead Limit Not Set</font></strong>";	
		}
		
	}
	$html 																= "
			<!-- Agents Sales -->

			<br><div class='panel panel-default'>
					<strong>Sales</strong><p class='pull-right'>
							<strong>
									<a href='#' onclick='show_agents_sales()'>Sales <span class='badge'>$sale_data</span></a>
									Target <span class='badge'>{$agent->target}</span>
							</strong>
						</p>
					<div class='panel-body'>
						<div class='progress'>
  							<div class='progress-bar progress-bar-$class' role='progressbar' aria-valuemax='100' style='width:$sale_percentage%'>
    							<strong><font color='black'>$sale_percentage%</font></strong>
    						</div>$no_sales
						</div>
					</div>
				
			<hr>	
			
			<!-- Agents Leads -->

			<div class='panel panel-default'>
					<strong>Leads</strong><p class='pull-right'>
							<strong>
									<a href='#' onclick='show_agents_leads()'>Leads <span class='badge'>$lead_data</span></a>
									Limit <span class='badge'>{$agent->lead_limit}</span>
							</strong>
						</p>
					<div class='panel-body'><strong>
						<div class='progress'>
  							<div class='progress-bar progress-bar-$lead_class' aria-valuenow='100' aria-valuemin='0' role='progressbar' aria-valuemax='100' style='width:$lead_percentage%'>
    								<font color='black'>$lead_percentage%</font></strong>
    						</div>$no_leads
						</div>
					</div>
				</div>
			
			</div>
			";

print $html;
}
function calc_percentage($target,$total_sales){
	
	#Calculate Percentage
	$percent 					  = number_format(($total_sales/$target) * 100,2) ;

	return $percent;

}
# Sale Stat Widget
function sale_stats(){
	# Global Variables
	global $cur_page, $_db, $validator;
	
	# Get Data 
	$start  					= ($validator->validate($_GET['start_date'], "String"))? $validator->validate($_GET['start_date'], "String") : date("Y-m-d");
	$end 						= ($validator->validate($_GET['end_date'], "String"))?$validator->validate($_GET['end_date'], "String"):date("Y-m-d")  ;
	$type 						= ($validator->validate($_GET['type'], "String"))?$validator->validate($_GET['type'], "String"):"";
	$team 						= ($validator->validate($_GET['team'], "String"))?$validator->validate($_GET['team'], "String"):"";
	$teams  					= $validator->validate($_GET['teams'], "Integer");
	$types  					= $validator->validate($_GET['types'], "Integer");

	#Local Variables
	$table 						= "";
	$clause 				= "";
	
	# Construct Clauses
	if($teams){
		$table 					  = ",`agents`";
		$clause 				  = "AND 
										s.`agent_id` = `agents`.`id`
								     AND 
								   		`agents`.`team_membership` = '{$team}'
								   ";
	}
	if($types){
		$table 					  = ",`agents`";
		$clause 				  = "AND 
										s.`agent_id` = `agents`.`id`
								     AND 
								   		`agents`.`type` = '{$type}'
								    ";
	}
	$pending_count				  = 0;
	$qc_query_count 			  = 0;
	$qc_burn_count 				  = 0;
	$total_count 				  = 0;
	$qc_pass_count				  = 0;
	$successful_count 			  = 0;
	$tot_html 					  = "";
	$pending_html				  = "";
	$qc_query_html				  = "";
	$burn_html 					  = "";
	$pass_html 					  = "";
	$sucess_html 				  = "";
	$total_products 			  = array();
	$pending_products 			  = array();
	$query_products 			  = array();
	$burnt_products 			  = array();
	$pass_products 				  = array();
	$success_products 			  = array();

	#Get Sale Target For The Day 
	$sale_target 				  = "SELECT 
											`value` as 'value'
									 FROM 
									 		`widget_settings` 
									 WHERE 
									 		`name` = 'sale_target'
									";
	$target 				  	  = $_db->fetch_single($sale_target);
	
	#Get Sales For Date Range
	$sale_query 				  = "SELECT 
											s.`id` 	 	   as 'id',
											s.`status` 	   as 'status',
											s.`product_id` as 'product',
											s.`sale_date` 	   as 'sale_date'
									 FROM 
									 		`sales` s {$table}
									 WHERE 
									 		s.`sale_date` BETWEEN '{$start} 00:00:00'
									 AND
									 		'{$end} 23:59:59'
									 {$clause}
									 ORDER BY 
									 		s.`status`
									";

	$sale_data 					  = $_db->fetch($sale_query);
	$widget 		 			  = new Widget("");
	logg($sale_query);
	foreach($sale_data as $sale){
			$product 			 	= new Product($sale->product);
			# Create Sale Details Html + Generate Status Counts
			if($sale->status   		== '10'){
				$pending_count++;
				$pending_html  		.= $widget->sale_listing($sale->id,$sale->sale_date,$product->name,$sale->status);
				$pending_products[]  = $sale->product;
			}
			if($sale->status   		== '15'){
				$qc_query_count++;
				$qc_query_html 		.= $widget->sale_listing($sale->id,$sale->sale_date,$product->name,$sale->status);
				$query_products[] 	 = $sale->product;
			}
			if($sale->status   		== '20'){
				$qc_burn_count++;
				$burn_html     		.= $widget->sale_listing($sale->id,$sale->sale_date,$product->name,$sale->status);
				$burnt_products[] 	 = $sale->product;
			}
			if($sale->status   		== '25'){
				$qc_pass_count++;
				$pass_html 	   		.= $widget->sale_listing($sale->id,$sale->sale_date,$product->name,$sale->status);
				$pass_products[] 	 = $sale->product;
			}
			if($sale->status   		== '35'){
				$successful_count++;
				$sucess_html   		.= $widget->sale_listing($sale->id,$sale->sale_date,$product->name,$sale->status);
				$success_products[]  = $sale->product;
			}
			$total_count++;
			$tot_html 		   		.= $widget->sale_listing($sale->id,$sale->sale_date,$product->name,$sale->status);
			$total_products[]   	 = $sale->product;
	}
	
	# Generate Tables For Sales
	$tot_sale_html 				 = "<table class='table'>
											<tr>
												<th>#</th>
												<th>Sale Date</th>
												<th>Product</th>
												<th>Status</th>
											</tr>
												$tot_html
									</table>";
	$pending_sale_html 			 = "<table class='table'>
										<tr>
											<th>#</th>
											<th>Sale Date</th>
											<th>Product</th>
											<th>Status</th>
										</tr>
											$pending_html
									</table>";
	$query_sale_html 			=  "<table class='table'>
										<tr>
											<th>#</th>
											<th>Sale Date</th>
											<th>Product</th>
											<th>Status</th>
										</tr>
											$qc_query_html
									</table>";
	$burnt_sale_html 			=  "<table class='table'>
										<tr>
											<th>#</th>
											<th>Sale Date</th>
											<th>Product</th>
											<th>Status</th>
										</tr>
											$burn_html
									</table>";
	$pass_sale_html 			=  "<table class='table'>
										<tr>
											<th>#</th>
											<th>Sale Date</th>
											<th>Product</th>
											<th>Status</th>
										</tr>
											$pass_html
									</table>";
	$sucess_sale_html 			=  "<table class='table'>
										<tr>
											<th>#</th>
											<th>Sale Date</th>
											<th>Product</th>
											<th>Status</th>
										</tr>
											$sucess_html
									</table>";

	#Calculate Percentages
	$total_sales 				 = calc_percentage($target,$total_count);
	$pending_sales 				 = calc_percentage($total_count,$pending_count);
	$qc_querys 					 = calc_percentage($total_count,$qc_query_count);
	$burnt 						 = calc_percentage($total_count,$qc_burn_count);
	$qc_pass 					 = calc_percentage($total_count,$qc_pass_count);
	$successful 				 = calc_percentage($total_count,$successful_count);

	# Generate Income Value of Sales At Each Stage
	$total_value 				 = $widget->income_values($total_products);
	$pending_value 				 = $widget->income_values($pending_products);
	$query_value 				 = $widget->income_values($query_products);
	$burnt_value 			     = $widget->income_values($burnt_products);
	$pass_value 				 = $widget->income_values($pass_products);
	$sucess_value 				 = $widget->income_values($success_products);

	$processed					 = array(
										"total_sales"	  => array($total_sales,$tot_sale_html,$total_count,$total_value),
										"pending_sales"	  => array($pending_sales,$pending_sale_html,$pending_count,$pending_value),
										"qc_sales"		  => array($qc_querys,$query_sale_html,$qc_query_count,$query_value),
										"burnt"			  => array($burnt,$burnt_sale_html,$qc_burn_count,$burnt_value),
										"pass"			  => array($qc_pass,$pass_sale_html,$qc_pass_count,$pass_value),
										"sucessful"		  => array($successful,$sucess_sale_html,$successful_count,$sucess_value)
								   ); logg("pending : ".$pending_count);

	print json_encode($processed);

}
//Team Usage Widget
function team_usage_stats(){
	# Global Variables
	global $cur_page, $_db, $validator;

	//Get Data 
	$team  						 = $validator->validate($_GET['id'], "Integer");
	$team_name 					 = $validator->validate($_GET['team'], "String");

	# Get All Team Members
	$member_query 			     = "SELECT 
											`id` as 'agent_id',
											`call_limit` as 'limit',
											`name` as 'name' 
									 FROM 
									 		`agents`
									 WHERE 
									 		`team_membership` = '{$team}'
									";logg($member_query);
	$member_data 			 	 = $_db->fetch($member_query);
	$allowed_limit 			  	 = "";
	$calls 					 	 = "";
	$members 					 = "";
	foreach($member_data as $member){
		
		# Get Extension Agent Was Logged Into for Date Range
		# Select 1 Record Only From Login HIstory Table
		$history_query 		  = "SELECT 
										`ext` as 'ext'
								 FROM 
								 		`login_history` 
								 WHERE 
								 		`agent` = '{$member->agent_id}'
								 AND 
								 		SUBSTRING(`datetime`,1,10) = '".date("Y-m-d")."'  
								";
		$extension 		      = $_db->fetch_one($history_query); 
		if(!($extension->ext)){
			$ext 			  = 0;
		}
		else{
			$ext 			  = $extension->ext;
		}
		# Get Sum Of Duration All Calls From Extension For Date Range
		# Get From Asteriskcdrdb NOT Agent Calls Table !!!!
		$call_query 		  = "SELECT 
										SUM(`duration`) as 'total_time'
								 FROM 
								 		`agent_calls` 
								 WHERE 
								 		`agent_id` = $member->agent_id
								 AND  
								 		`extension` = '$ext'
								 AND  
								 		SUBSTRING(`datetime`,1,10) = '".date("Y-m-d")."' 
								";
		$call_duration 		  = $_db->fetch_one($call_query); // Actuall Usage
		logg("Extension".$extension->ext." Durations: ".$call_duration->total_time);
		
		#Calculate Teams Allowed Useage
		$allowed_limit 		 .= ($member->limit > 0)?$member->limit."," : '1,';
		$calls 				 .= ($call_duration->total_time)?$call_duration->total_time."," : '0,';

		$members 			 .= "'".ucfirst($member->name)."',";
	}
	$graph 				  = new widget("");
	$usage_graph 		  = $graph->usage_graph($members,$allowed_limit,$calls);

	print $usage_graph;
}
# Sale Stats Widget - Update Sale Target
function update_sale_target(){
	# Global Variables
	global $cur_page, $_db, $validator;

	//Get Data 
	$target 				  = $validator->validate($_GET['target'], "Integer");

	$_db->update(
		"widget_settings",
		array(
			"value"			  => $target,
			"date_modified"	  => date("Y-m-d H:i:s"),
			"user"			  => get_user_uid()
		),
		array(
			"name" 			  => 'sale_target'
		)
	);

	print "OK";
}
# Agent Stats Widget - Member Listing
function team_member_stats(){
	# Global Variables
	global $cur_page, $_db, $validator;

	# Get Data 
	$team 					  = $validator->validate($_REQUEST['team'], "Integer");
	$start 					  = ($validator->validate($_REQUEST['start'], "String"))? $validator->validate($_REQUEST['start'], "String") : date("Y-m-d");
	$end 					  = ($validator->validate($_REQUEST['end'], "String"))  ? $validator->validate($_REQUEST['end'], "String")   : date("Y-m-d");
	
	# Local Variables
	$class 					  = "";
	$no_sales 				  = "";
	$listing 				  = "";
	$member_listing 	      = "";
	# Get All Members In Team
	$team_query 			  = "SELECT  
										`id`,
										`name`
								 FROM
								 		`agents`
								 WHERE 
								 		`team_membership` = '{$team}'
								 AND 
								 		`active` = 1  
								";
	$team_members 			  = $_db->fetch($team_query);
	foreach($team_members as $member){
		# Get Agent Sales
		$sale_query 		  = "SELECT 
										s.`id`,
										s.`sale_date`,
										s.`agent_id`,
										s.`product_id`,
										s.`status`
								 FROM 
										`sales` s,
										`agents` 
								 WHERE 
										s.`agent_id` = `agents`.`id`
								 AND 
								 		`agents`.`id` = '{$member->id}'
								 AND 
										`sale_date` BETWEEN '{$start} 00:00:00' AND '{$end} 23:59:59'
								 ORDER BY 
								 		s.`status`
								";logg("sale_query ".$sale_query);
		$sales_data 		   = $_db->fetch($sale_query);
		$sale_count 		   = 0;
		$sale_listing 		   = "";

		#Sale listing
		foreach($sales_data as $sale){
			$product 				  = new Product($sale->product_id);
		
			$sale_listing 			 .= "<tr>
												<td>{$sale->id}</td>
												<td>{$sale->sale_date}</td>
												<td>{$sale->status}</td>
												<td>{$product->name}</td>
										 </tr>";
			$sale_count++;
		}
		$agent 						  = new Agent($member->id);
		$widget 					  = new Widget("");
		if($agent->target){
			$target 				  = $agent->target;
		}
		else{
			$target 				  = 1;
		}
		# Calculate Percentages
		$sale_percentage 			  = number_format($sale_count / $target * 100,1);																	  
		if($sale_percentage > 0){
			$class 					  = $widget->get_percentage_class($sale_percentage);
		}
		else{
			$sale_percentage 		  = 0;
			$no_sales 				  = "<strong class='pull-right'><font color='red'>No Sales Made </font></strong>";
		}
		# Generate Sale Listing
		$listing 					  = "<table class='table'>
													<tr>
														<th>#</th>
														<th>Sale Date</th>
														<th>Status</th>
														<th>Product</th>
													</tr>
													{$sale_listing}
										 </table>
										";

		# Member Stat List
		$member_listing				  .= "<br><div class='panel panel-default'>
												<strong>".ucfirst($agent->name)."</strong>
												<p class='pull-right'>
													<strong>
															<a href='javascript:void(0)' onclick=show_member_sales(\"$member->id\")>Sales <span class='badge' style='background-color:#005DFF'>{$sale_count}</span></a>
															Target <span class='badge' style='background-color:#5ED931'>{$target}</span>
													</strong>
												</p>
												<div class='panel-body'>
													<div class='progress'>
							  							<div class='progress-bar progress-bar-$class' role='progressbar' aria-valuemax='100' style='width:$sale_percentage%'>
							    							<strong><font color='black'>$sale_percentage%</font></strong>
							    						</div>$no_sales
													</div>
												</div>
											</div>
											<div id='member_sales_{$member->id}' style='display:none'>
												{$listing}
											</div>
										<hr>";
	}
		print $member_listing;
}
function agent_team_stats(){
	# Global Variables
	global $cur_page, $_db, $validator;

	$start 				= ($validator->validate($_REQUEST['start'], "String"))?$validator->validate($_REQUEST['start'], "String"):date("Y-m-d");
	$end 				= ($validator->validate($_REQUEST['end'], "String"))?$validator->validate($_REQUEST['end'], "String"):date("Y-m-d");
	
	$widget 			= new Widget("");
	$stats 				= $widget->agent_stats('minimize',$start,$end);

	print $stats;
}
function update_profile(){
		# Global Variables
		global $cur_page, $_db, $validator;
	
		# Get Data 
		$widget 	 														= $validator->validate($_GET['widget'], "String");
		$type 	 															= $validator->validate($_GET['type'], "String");

		$user_id 															= get_user_uid();

		# Check For Record
		$query 																= "SELECT 
																						DISTINCT aw.`id` as 'id'
																			   FROM 
																			   			`agent_widget` aw,
																			   			`agents`
																			   WHERE 
																			   			`agents`.`id` = aw.`agent_id`
																			   AND 
																			   			aw.`name`     = '{$widget}'
																			   AND 
																			   			aw.`agent_id` = '{$user_id}'
																			  ";
		$data 																= $_db->fetch_one($query);
		if($data){
			# Update Widget Table
			if($type == 'add'){
				$_db->update(
						"agent_widget",
						array(
							"active"											=> "1"
						),
						array(
							"agent_id"											=> $user_id,
							"name" 											    => $widget
						)
				);

				# Return
				print "Done";
			}
			else{
				$_db->update(
						"agent_widget",
						array(
							"active"											=> "0"
						),
						array(
							"agent_id"											=> $user_id,
							"name" 											    => $widget
						)
				);

				# Return
				print "Done";
			}	
		}
		else{
			# Insert Into Ticket Table
			$_db->insert(
					"agent_widget",
					array(
						"datetime"												=> date("Y-m-d H:i:s"),
						"agent_id"												=> $user_id,
						"name"													=> $widget,
						"active" 												=> 1
						));
			print "Done";
			}

	}
# =========================================================================
# ACTION HANDLER
# =========================================================================

if (isset($_GET['action'])) {
	$action 															= $_GET['action'];
	if($action 		== "update_profile"){
		update_profile();
	}
	else if($action == "get_agent_stats"){
		get_agent_stats();
	}
	else if($action == "sale_stats"){
		sale_stats();
	}
	else if($action == "team_usage_stats"){
		team_usage_stats();
	}
	else if($action == "team_member_stats"){
		 team_member_stats();
	}
	else if($action == "update_sale_target"){
		update_sale_target();
	}
	else if($action == "validate_sa_id"){
		validate_sa_id();
	}
	else if($action == "agent_team_stats"){
		agent_team_stats();
	}
	else {
		logg($action);
		print "Error: Invalid Action";
	}
}
# =========================================================================
# THE END
# =========================================================================

?>
