$(document).ready(function(){
    //Calendar Styling
    $('.date-form-control').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    // Select @ Auto Complete
    $('.select2').select2();

/*  // CHart Update Timeout
    setTimeout(function() {
        $('.chart').data('easyPieChart').update(90);
        $('#chart-percent').html("");
        $('#chart-percent').html("<strong>90</strong></div>");
    }, 5000);*/

    // Drageable Widgets
    $( ".draggable" ).draggable(); // Dragable Divs
   
    // Easy Pie Chart Config 
    // Total Sales Chart
    $('.chart').easyPieChart({
        //your options goes here
        animate: 2000,
        lineWidth: 12,
        lineCap: 'square',
        size: 120,
        scaleColor: '#394264',
        trackColor: '#e5e5e5',
        barColor: '#442E68'
     });
    // Pending Sales Chart
     $('.chart-pending').easyPieChart({
        //your options goes here
        animate: 2000,
        lineWidth: 12,
        lineCap: 'square',
        size: 100,
  
        scaleColor: '#B2B8B0',
        trackColor: '#e5e5e5',
        barColor: '#4573FF'
     });
     // QC Query Chart
     $('.chart-query').easyPieChart({
        //your options goes here
        animate: 2000,
        lineWidth: 12,
        lineCap: 'square',
        size: 100,
        scaleColor: '#B2B8B0',
        trackColor: '#e5e5e5',
        barColor: '#1097E0'
     });
     // QC Burnt Chart
    $('.chart-burnt').easyPieChart({
        //your options goes here
        animate: 2000,
        lineWidth: 12,
        lineCap: 'square',
        size: 100,
        scaleColor: '#B2B8B0',
        trackColor: '#e5e5e5',
        barColor: '#C70000'
     });
    // QC Pass Chart
    $('.chart-pass').easyPieChart({
        //your options goes here
        animate: 2000,
        lineWidth: 12,
        lineCap: 'square',
        size: 100,
        scaleColor: '#B2B8B0',
        trackColor: '#e5e5e5',
        barColor: '#96F759'
     });
    // QC Success Chart
    $('.chart-success').easyPieChart({
        //your options goes here
        animate: 2000,
        lineWidth: 12,
        lineCap: 'square',
        size: 100,
        scaleColor: '#B2B8B0',
        trackColor: '#e5e5e5',
        barColor: '#53E024'
     });

  /*  //Agent Stats Widget   
    $('#agent_stats-container').ready(function(){
        var agent_id = $('#as_agent_id').val();

        if(agent_id == 0){ 
            $('#as_agentdetails').html("<strong><font color='red'> *</font>Please Select An Agent</strong>");
        }
        else{
            // Get Selected Agent Stats
            var url  = 'include/content/ajax.php?';
                url += 'action=get_agent_stats';
                url += '&agent=' + agent_id;
              result = ajax_get_data(url);
                $('#as_agentdetails').hide().html(result).fadeIn("fast");
             
        }

    });*/

    //Auto Refresh Sale Stats
    $('#auto_refresh').on('change',function auto_refresh() {
      if($('#auto_refresh').is(':checked')) {
         process_stat_filter('auto');
         window.setTimeout(auto_refresh,7000);
         }
        
      else {
        alert("Auto Refresh Turned Off!");
        // window.setTimeout(refresh,1000);
        }
    });
});

// Show Widget Bar
function show_widgets() {
    var tblitem = $("#widget_div");
    var item = $("#widget_div");
    
    
    item.slideToggle('slow');
    tblitem.removeClass('hidden');

    var state = $('#widget_state').val();
    if(state == '0'){
        $('#icon').removeClass('glyphicon glyphicon-chevron-down');
        $('#icon').addClass('glyphicon glyphicon-chevron-up');
        $('#widget_state').val('1');
    }
    else{
        $('#icon').removeClass('glyphicon glyphicon-chevron-up');
        $('#icon').addClass('glyphicon glyphicon-chevron-down');
        $('#widget_state').val('0');
    }

}
// Add Widgets   
function add_widget(name){

        if($('input#' + name).is(':checked')) {
                 var tblitem = $("#"+name+"-container");
                 var item = $("#"+name+"-container");
                 item.slideToggle('slow');
                 tblitem.removeClass('hidden');

                 update_profile = "include/content/dash_ajax.php?action=update_profile&type=add&widget="+name;
                 update = ajax_get_data(update_profile);
                 return;
        }
        else{
              
                 var tblitem = $("#"+name+"-container");
                 var item = $("#"+name+"-container");
                 item.slideToggle('slow');
         

                 update_profile = "include/content/dash_ajax.php?action=update_profile&type=remove&widget="+name;
                 update = ajax_get_data(update_profile);
                 return;
        }
    }


//Refresh Widget
function refresh_widget(widget){
   if(widget == 'agent_stats'){
      var start_date     = $('#agent_stats_start').val();
      var end_date       = $('#agent_stats_end').val();
      var team           = $('#display_team').val();
      if(team > 0){
        var url          = "include/content/dash_ajax.php?";
        url             += "action=team_member_stats";
        url             += "&team="+team;
        url             += "&start="+start_date;
        url             += "&end="+end_date;

        result           = ajax_get_data(url);
        $("#as_agentdetails").html("");
        $("#as_agentdetails").html(result);
        $("#display_team").val(team);
        if(start_date.length > 0 && end_date.length){
             $("#agent_stats_start").val(start_date);
             $("#agent_stats_end").val(end_date);
        }
      }
      else{
        var url          = "include/content/dash_ajax.php?";
        url             += "action=agent_team_stats";
        url             += "&start="+start_date;
        url             += "&end="+end_date;

        result           = ajax_get_data(url);
   
        $("#agent_team_stats").html("");
        $("#agent_team_stats").html(result);
        if(start_date.length > 0 && end_date.length){
             $("#agent_stats_start").val(start_date);
             $("#agent_stats_end").val(end_date);
        }
      }
   }
 }

/*// Agent Stat Widget
function agent_stats(){
    var agent  = $('#agent_stat_agent option:selected').val();
    // Get Agent Stats   
    document.getElementById('as_agent_id').value = agent;

    $('#as_agentdetails').html("");
    $('#as_agentdetails').html();

    if(agent>0){
         // Get Selected Agent Stats
         var url  = 'include/content/ajax.php?';
             url += 'action=get_agent_stats';
             url += '&agent=' + agent;
          result  = ajax_get_data(url);
         $('#as_agentdetails').html(result);    
         $('#as_agentdetails').hide().html(result).fadeIn("slow");
    }
    else{
        $('#as_agentdetails').html("<strong><font color='red'> *</font>Please Select An Agent</strong>");   
    }
 }*/
// MaxiMize Widget
function maximize(widget) {
  $("#"+widget+"-btn").html('');
  $("#"+widget+"-btn").html("<a class='btn-minimize' href='javascript:void(0)' onclick=minimize('"+widget+"') id='"+widget+"-btn'><span class='glyphicon glyphicon-resize-small'></i></a>");

  $("#"+widget).slideDown('fast');
}
//MiniMize Widget
function minimize(widget) {
  $("#"+widget).slideUp('fast');

  $("#"+widget+"-btn").html('');
  $("#"+widget+"-btn").html("<a class='btn-minimize' href='javascript:void(0)' onclick=maximize('"+widget+"') id='"+widget+"-btn'><span class='glyphicon glyphicon-resize-full'></i></a>");
}
//Close Widget
function close_widget(name){
   
   var tblitem = $("#"+name+"-container");
   var item = $("#"+name+"-container");
   item.slideToggle('slow');
         

   update_profile = "include/content/ajax.php?action=update_profile&type=remove&widget="+name;
   update = ajax_get_data(update_profile);

}
// Show Sale Stats Filter
function sale_stat_filter(type){
   //Date
   if(type == 'date'){
        if($('#stat_dates').is(':checked')) {
            
           $('#date_filter').removeClass("hidden");

         }
        else{
           $('#date_filter').addClass("hidden");
           $('#date_filter').slideToggle('slow');
        }
   }
    //Team
   else if(type == 'team'){
        if($('#stat_team').is(':checked')) {
           $('#agenttype_filter').addClass("hidden"); 
           $('#stat_agenttype').html("");
           $('#stat_agenttype').html("<input type='checkbox' id='stat_agenttype' name='stat_team' onchange='sale_stat_filter(\"agenttype\")'> ");
           $('#team_filter').removeClass("hidden");

         }
        else{
           $('#team_filter').addClass("hidden");
           $('#team_filter').slideToggle('slow');
        }

   }
    //Agent Type
   else if(type == 'agenttype'){
        if($('#stat_agenttype').is(':checked')) {
           $('#team_filter').addClass("hidden");
           $('#stat_team').html("");
           $('#stat_team').html("<input type='checkbox' id='stat_team' name='stat_team' onchange='sale_stat_filter(\"team\")'>"); 
           $('#agenttype_filter').removeClass("hidden");

         }
        else{
           $('#agenttype_filter').addClass("hidden");
           $('#agenttype_filter').slideToggle('slow');
        }

    }
   if(!($('#stat_agenttype').is(':checked')) && !($('#stat_team').is(':checked')) && !($('#stat_dates').is(':checked'))){
      $('#process').addClass("hidden");
   }
   else{
      $('#process').removeClass("hidden");
   }
}

//Update Sale Stat Charts
function update_sale_stats(values){
    var json = $.parseJSON(values)

    //Update Charts
    $('.chart').data('easyPieChart').update(json.total_sales[0]);
    $('#chart-percent').html("");
    $('#chart-percent').html("<strong>"+json.total_sales[0]+"</strong></div>");
    $('#total_sales').html("");
    $('#total_sales').html(json.total_sales[1]);
    $('#total_sales_badge').html("");
    $('#total_sales_badge').html(json.total_sales[2]);
    $('#total_value_badge').html("");
    $('#total_value_badge').html(json.total_sales[3]);

    $('.chart-pending').data('easyPieChart').update(json.pending_sales[0]);
    $('#chart-pending-percent').html("");
    $('#chart-pending-percent').html("<strong>"+json.pending_sales[0]+"</strong></div>");
    $('#pending_sales').html("");
    $('#pending_sales').html(json.pending_sales[1]);
    $('#pending_sales_badge').html("");
    $('#pending_sales_badge').html(json.pending_sales[2]);
    $('#pending_value_badge').html("");
    $('#pending_value_badge').html(json.pending_sales[3]);

    $('.chart-query').data('easyPieChart').update(json.qc_sales[0]);
    $('#chart-query-percent').html("");
    $('#chart-query-percent').html("<strong>"+json.qc_sales[0]+"</strong></div>");
    $('#query_sales').html("");
    $('#query_sales').html(json.qc_sales[1]);
    $('#query_sales_badge').html("");
    $('#query_sales_badge').html(json.qc_sales[2]);
    $('#query_value_badge').html("");
    $('#query_value_badge').html(json.qc_sales[3]);

    $('.chart-burnt').data('easyPieChart').update(json.burnt[0]);
    $('#chart-burnt-percent').html("");
    $('#chart-burnt-percent').html("<strong>"+json.burnt[0]+"</strong></div>");
    $('#burnt_sales').html("");
    $('#burnt_sales').html(json.burnt[1]);
    $('#burnt_sales_badge').html("");
    $('#burnt_sales_badge').html(json.burnt[2]);
    $('#burnt_value_badge').html("");
    $('#burnt_value_badge').html(json.burnt[3]);

    $('.chart-pass').data('easyPieChart').update(json.pass[0]);
    $('#chart-pass-percent').html("");
    $('#chart-pass-percent').html("<strong>"+json.pass[0]+"</strong></div>");
    $('#pass_sales').html("");
    $('#pass_sales').html(json.pass[1]);
    $('#pass_sales_badge').html("");
    $('#pass_sales_badge').html(json.pass[2]);
    $('#pass_value_badge').html("");
    $('#pass_value_badge').html(json.pass[3]);

    $('.chart-success').data('easyPieChart').update(json.sucessful[0]);
    $('#chart-success-percent').html("");
    $('#chart-success-percent').html("<strong>"+json.sucessful[0]+"</strong></div>");
    $('#sucess_sales').html("");
    $('#sucess_sales').html(json.sucessful[1]);
    $('#sucess_sales_badge').html("");
    $('#sucess_sales_badge').html(json.sucessful[2]);
    $('#success_value_badge').html("");
    $('#success_value_badge').html(json.sucessful[3]);
}

// Process Sale Stat Filter
function process_stat_filter(auto){

    ///////Complete Auto Refresh !!!!!!! Cycling threw teams or agent types
    var teams           = 0;
    var types           = 0;
    var date_start      = "";
    var date_end        = "";
    
    // Get All Filter Options
    if($('#stat_dates').is(':checked')) {
        if($('#stats_start').val() == '' || $('#stats_end').val() == ''){
            alert("Please Select A Valid Date Range");
            return false;
        }
        else{
            date_start   = $('#stats_start').val();
            date_end     = $('#stats_end').val()
        }
    }
    if($('#stat_team').is(':checked')) {
        if($('#team_select').val() == 0){
            alert("Please Select A Team");
            return false;
        }
        else{
            var team        = $('#team_select').val();
            var teams       = 1;
        }
    }
    if($('#stat_agenttype').is(':checked')) {
        if($('#agenttype_select').val() == 0){
            alert("Please Select An Agent Type");
            return false;
        }
        else{
            var type        = $('#agenttype_select').val();
            var types       = 1;
        }
    }

    // Construct url string
    var url  = "include/content/dash_ajax.php?";
        url += "action=sale_stats";
        url += "&start_date="+date_start;
        url += "&end_date="+date_end;
        url += "&team="+team;
        url += "&type="+type;
        url += "&teams="+teams;
        url += "&types="+types;
    result   = ajax_get_data(url);

    //Update Sale Stat Charts
    update_sale_stats(result);
}

//Member Usage
function member_usage(){
    var id    = $('#team').select2('data').id;
    var value = $('#team').select2('data').text;
    if(id == 0){
        alert("Please Select A Team To Continue");
        return false;
    }
    // Construct url string
    var url   = "include/content/dash_ajax.php?";
        url  += "action=team_usage_stats";
        url  += "&id="+id;
        url  += "&team="+value;

    result   = ajax_get_data(url);

    $("#team_usage_graph").html("");
    $("#team_usage_graph").html(result);
}

// Sale Stat Settings
  function sale_stat_settings(){
    $('#sale_settings').slideToggle('fast');
    $('#sale_settings').removeClass('hidden');
    
  }
// Sale Stats Widget Sale Listing
  function sale_listing(id){
     $('#'+id).dialog({
            title: "Sale Listing",
            modal: true,
            width: 600,
            height: 400,
            buttons: {
                "Cancel": function() {
          
                        $(this).dialog("close");
                   
                }
            }
    });
  }
// Agent/Team stats Widget Sale Listing
  function show_team_sales(team){
     $('#team_sales_'+team).dialog({
            title: "Sale Listing",
            modal: true,
            width: 600,
            height: 400,
            buttons: {
                "Cancel": function() {
          
                        $(this).dialog("close");
                   
                }
            }
    });
  }
  // Agent/Team stats Widget Sale Listing - Team Drill Down
  function show_member_sales(member){
        $('#member_sales_'+member).dialog({
            title: "Sale Listing",
            modal: true,
            width: 600,
            height: 400,
            buttons: {
                "Cancel": function() {
          
                        $(this).dialog("close");
                   
                }
            }
    });
  }
  function update_sale_target(){
    $('#sale_target_modal').dialog({
            title: "Sale Target",
            modal: true,
            width: 300,
            height: 200,
            buttons: {
                "Cancel": function() {
          
                        $(this).dialog("close");
                   
                },
                "Update": function() {
                    var target       = $("#target_value").val();
      
                   
                    /* Validate Fields */
                    if(target === '' || target <= 0) {
                        alert('Please add a Target before continuing.');
                        return false;
                    } 

                        var url  = 'include/content/dash_ajax.php?';
                            url += 'action=update_sale_target';
                            url += '&target=' + target;
                            result = ajax_get_data(url);
                      
                    $(this).dialog({
                       title: "Sale Target Updated",
                       modal: true,
                       width: 600,
                       height: 400,
                         buttons: {
                            "Reload": function() {
                                $(this).dialog("close");
                                document.location='?p=dash';
                            }
                        }
                    });
                        
                        $(this).html('<h2>Sale Target Updated Successfully!</h2><p>Sale Target Updated Successfully. For Sale Statistics To Update A Reload Is Required .This window can now be closed.</p>');
                        
                }
            }
    });
  }

  // Team Member Stats
  function team_member_stats(team){
    var start = $('#agent_stats_start').val();
    var end = $('#agent_stats_end').val();
    // Construct url string
    var url   = "include/content/dash_ajax.php?";
        url  += "action=team_member_stats";
        url  += "&team="+team;
        url  += "&start="+start;
        url  += "&end="+end;

    result   = ajax_get_data(url);

    $("#as_agentdetails").html("");
    $("#as_agentdetails").html(result);
    $("#back").append("<button type='button' class='btn btn-default btn-xs' onclick='agent_stat_back()'><span class='glyphicon glyphicon-arrow-left'></span> Back</button>");
   // $("#as_dates_setting").addClass('date_position');
    $("#display_team").val(team);
  }

  // Agent Stat Settings
  function agent_stat_setting(){   

    var tblitem = $("#agent_stat_setting");
    
    
    tblitem.slideToggle('slow');
    tblitem.removeClass('hidden');

    var state = $('#as_setting_state').val();
    if(state == '0'){
        $('#as_setting_btn').removeClass('glyphicon glyphicon-chevron-down');
        $('#as_setting_btn').addClass('glyphicon glyphicon-chevron-up');
        $('#as_setting_state').val('1');
    }
    else{
        $('#as_setting_btn').removeClass('glyphicon glyphicon-chevron-up');
        $('#as_setting_btn').addClass('glyphicon glyphicon-chevron-down');
        $('#as_setting_state').val('0');
    }
  }

  function agent_stat_back(){
    var start = $('#agent_stats_start').val();
    var end = $('#agent_stats_end').val();
    // Construct url string
    var url   = "include/content/dash_ajax.php?";
        url  += "action=agent_team_stats";
        url  += "&start="+start;
        url  += "&end="+end;

    result    = ajax_get_data(url);

    $("#agent_team_stats").html("");
    $("#agent_team_stats").html(result);
    $("#display_team").val(0);
    if(start.length > 0 && end.length){
        $("#agent_stats_start").val(start);
        $("#agent_stats_end").val(end);
    }
  }