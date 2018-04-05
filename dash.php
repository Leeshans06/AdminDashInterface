<?php
/**
 * Call Center White Label
 * 
 * @author Leeshan	Subramunier <leeshan@implyit.co.za>
 * @version 1.0
 * @copyright Copyright (C) Imply Development CC 2014
 * @package Call Center White Label
 * 
 */

# =========================================================================
# SCRIPT SETTINGS
# =========================================================================

$cur_page																= "?p=dash";

# Check for External Users
$is_external															= $_db->get_data("agents", "external", "id", get_user_uid());
if ($is_external) {
	redirect("?p=external_sales");
	die();
}

# =========================================================================
# DISPLAY FUNCTIONS
# =========================================================================

/**
 * The default function called when the script loads
 */
function display(){
	# Global Variables
	global $cur_page, $_db;
	
	$user_id 															= get_user_uid();

	# Get All Agent Widgets
	$query 																= "SELECT
																					DISTINCT  `name` as 'name',
																					`active` as 'active'
																		   FROM 
																		   			`agent_widget`
																		   WHERE 
																		   			`agent_id`      = '{$user_id}'
																		  ";
	$data 																= $_db->fetch($query);
	
	# Initialize Widgets
	$agent_stats_class 													= "hidden";	// Agent Stats																	
	$agent_stats_checked 												= "";		// Agent Stats
	$sale_stat_class 													= "hidden"; // Sales Stats
	$sale_stat_checked 													= "";		// Sales Stats
	$sale_stat_html 													= "";		// Sales Stats
	$team_usage_html 													= "";
	$team_usage_class 													= "hidden";	
	$team_usage_checked	 												= "";
	$banking_class 														= "hidden";	
	$banking_checked 													= "";
	if($data){
		foreach($data as $item){
			# Agent Stats Widget
			if($item->name == 'agent_stats'){
				if($item->name == 'agent_stats' && $item->active == 1){
					$agent_stats_class 									= "minimize";
					$agent_stats_checked 								= "CHECKED=true";
				}
			}
			if($item->name == 'sale_stats'){
				if($item->name == 'sale_stats' && $item->active == 1){
					$sale_stat_class 									= "";
					$sale_stat_checked	 								= "CHECKED=true";
				}
				$sale_stat_checked	 									= ($item->name == "agent_stats")? "CHECKED=true" : "";
			}
			if($item->name == 'team_usage'){
				if($item->name == 'team_usage' && $item->active == 1){
					$team_usage_class 									= "fullgraph";
					$team_usage_checked	 								= "CHECKED=true";
				}
			}
			if($item->name == 'banking'){
				if($item->name == 'banking' && $item->active == 1){
					$banking_class 										= "";
					$banking_checked 									= "CHECKED=true";
				}
			}
		}

	}
	$widget 												= new Widget("");
	$agent_stats_html 										= $widget->agent_stats($agent_stats_class,"","");
	$sale_stat_html 										= $widget->sale_stats($sale_stat_class,"","");
	$team_usage_html 										= $widget->team_usage($team_usage_class,"","");
	$weekly_banking 										= $widget->weekly_banking($banking_class,"","");
	
	# Generate HTML
	$html													= "
		<link href='include/css/bootstrap.css' rel='stylesheet'>
        <link href='include/css/bootstrap-theme.css' rel='stylesheet'>
		<link rel='stylesheet' href='include/css/select2.css'>
		<link href='include/css/main.css' rel='stylesheet'>
        <script src='include/scripts/vendor/bootstrap.js'></script>
        <script src='include/scripts/plugins.js'></script>
        <script src='include/scripts/widget.js'></script>
        <script src='include/scripts/vendor/select2.js'></script>
                
		<script src='include/scripts/vendor/js/highcharts.js'></script>
        <script src='include/scripts/vendor/js/modules/exporting.js'></script>
		<div class='container wrapper'>

			<!-- Widget Sections Start-->
			<br><br><div class='row'>
			<div id='widget_div' class='hidden well' style='height:100px;'> 
				<div id='widget_agent_stats' class='well' style='float: left;'>	
						<span class='glyphicon glyphicon-user' style='height:40;width:50;'></span> Agent Stats <input type='checkbox' id='agent_stats' {$agent_stats_checked} onclick='add_widget(\"agent_stats\")'>       									
 				</div>
			
				<div id='widget_agent_stats' class='well'  style='float: left;' >	
						<span class='glyphicon glyphicon-signal' style='height:40;width:50;'></span> Sale Stats <input type='checkbox' id='sale_stats' {$sale_stats_checked} onclick='add_widget(\"sale_stats\")'>       									
 				</div> 					
				<div id='widget_airtime_usage' class='well'  style='float: left;' >	
						<span class='glyphicon glyphicon-phone-alt' style='height:40;width:50;'></span> AirTime Usage <input type='checkbox' id='team_usage' {$team_usage_checked} onclick='add_widget(\"team_usage\")'>       									
 				</div> 
 				<div id='widget_banking' class='well'  style='float: left;' >	
						 <img src='include/images/money_bag.png' style='width:20px;height:20px;'></img> Ready To Bank <input type='checkbox' id='banking' {$banking_checked} onclick='add_widget(\"banking\")'>       									
 				</div> 
			</div>
				<div class='col-lg-5' style='float:right; position:absolute; right: -29%;'>
							<a href='#' onclick='show_widgets()'>
								<span class='label label-default' style='font-size: 100%;'>
									<span id='icon' class='glyphicon glyphicon-chevron-down'>
										<input type='hidden' id='widget_state' value='0'>
									</span> Widgets
								</span>
							</a>
				</div>
				<br><br>			
			<!-- Widget Sections End--> 
			<div id='widgets'>
				<!-- Sale Stat Widget -->
				{$sale_stat_html}
				<!-- Agent Stat Widget -->
				<div class='row'>
					<div id='agent_team_stats' class='col-lg-7'>
							{$agent_stats_html}
					</div>
			
					<!-- Airtime Stat Widget -->
					<div id='team_usage' class='col-lg-5'>{$team_usage_html}</div>
				</div>
				{$weekly_banking}
			</div>
		<br><br>
";

	# Display HTML
	print $html;
}

# =========================================================================
# PROCESSING FUNCTIONS
# =========================================================================

# =========================================================================
# ACTION HANDLER
# =========================================================================

if (isset($_GET['action'])){
	$action 															= $_GET['action'];
	if ($action 														== "display"){
		display();
	}
	else {
		error("Invalid action `$action`.");
	}
}
else {
	display();
}

# =========================================================================
# THE END
# =========================================================================

