<!doctype html>
<html lang="en">
	<head>
    	<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<title>pimonitor - Power Consumption</title>
		
		<script src="js/jquery-3.2.1.min.js"></script>
		<script src="js/popper.min.js"></script>
		<script src="bootstrap-4.0.0-dist/js/bootstrap.bundle.min.js"></script>
		<script src="js/Chart.bundle.min.js"></script>
		<script src="js/chartjs-plugin-datalabels.min.js"></script>
		
		<link href="bootstrap-4.0.0-dist/css/bootstrap.css" rel="stylesheet" />
		<link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" />
		
		<script>
			$.urlParam = function(name){
			    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
			    if (results) {
			        return results[1] || 0;
			    }
			    return 0;
			}
	
			$(document).ready(function(){
			    updateByHour();
			    updateTheRest();
			});
			
			function getRandomColor() {
				var r = Math.floor(Math.random() * 256);
				var g = Math.floor(Math.random() * 256);
				var b = Math.floor(Math.random() * 256);
				
				return "rgba(" + r + "," + g + "," + b;
			}
			
			function refreshByHour(day) {
			    if (day) {
			        window.history.pushState("", "","index.php?byHourDay=" + day);
			    } else {
			        window.history.pushState("", "","index.php");
			    }
			    updateByHour();
			}
			
			function updateByHour() {
				// http://www.chartjs.org/docs/latest/charts/bar.html
				var postvars = { action: "byhour"};
				
				if ($.urlParam('byHourDay') != 0) {
			        postvars.byHourDay = $.urlParam('byHourDay');
			    }
			    
				$.post( "api.php", postvars, function(result) {
					result = $.parseJSON(result);
					
					labels = [];
					points = result;
					
					var alp = "0.5";
					background_colors = [];
					border_colors = [];
					
			        $.each(result, function(i,v)
			        {
						labels.push(i);
						
						var color = getRandomColor();
						
						background_colors.push(color + "," + alp + ")");
						border_colors.push(color + ")");
			        });
			        
			        if (postvars.byHourDay) {
			            //$("#chart_title").text("Power Consumption (" + decodeURIComponent(postvars.byHourDay) + ")");
			            $("#goback").fadeIn(600);
			        } else {
				        //$("#chart_title").text("Power Consumption");
			        }
			        
			        var myBarChart = new Chart(document.getElementById("byHour"), {
					    "type": 'bar',
					    "data": {
						    "labels": labels,
						    "datasets": [{
							    //"label": "Hourly Consumption (Today)",
							    "data": points,
							    "fill": false,
							    "backgroundColor" : background_colors,
							    "borderColor" : border_colors,
							    "borderWidth": 2
						    }]
					    },
					    "options":{
						    plugins: {
								datalabels: {
									color: 'white',
									display: function(context) {
										return context.dataset.data[context.dataIndex] > 2;
									},
									font: {
										weight: 'bold'
									},
									formatter: Math.round
								}
							},
							"scales": {
								"xAxes": [{
									"ticks": {
										"beginAtZero": true
									}
								}],
								"yAxes": [{
									"ticks": {
										callback: function(value, index, values) {
					                        return value.toLocaleString() + 'kWh';
					                    }
									}
								}]
							},
							legend: {
								display: false,
	        				}
						}
					});
					
				});
			}
	
			function updateTheRest() {
				$.post( "api.php", { action: "byday"}, function( result) {
		        	result = $.parseJSON(result);
		        	
		        	labels = [];
					points = [];
					
					var alp = "0.5";
					background_colors = [];
					border_colors = [];
					
			        $.each(result, function(i,v)
			        {
						labels.push(i);
						points.push(v);
						
						var color = getRandomColor();
						
						background_colors.push(color + "," + alp + ")");
						border_colors.push(color + ")");
			        });
			        
			        var myBarChart = new Chart(document.getElementById("byDay"), {
					    "type": 'horizontalBar',
					    "data": {
						    "labels": labels,
						    "datasets": [{
							    //"label": "Hourly Consumption (Today)",
							    "data": points,
							    "fill": false,
							    "backgroundColor" : background_colors,
							    "borderColor" : border_colors,
							    "borderWidth": 2
						    }]
					    },
					    "options":{
						    plugins: {
								datalabels: {
									color: 'white',
									display: function(context) {
										return context.dataset.data[context.dataIndex] > 2;
									},
									font: {
										weight: 'bold'
									},
									formatter: Math.round
								}
							},
							"scales": {
								"xAxes": [{
									"ticks": {
										"beginAtZero": true,
										callback: function(value, index, values) {
					                        return value + 'kWh';
					                    }
									}
								}]
							},
							legend: {
								display: false,
	        				},
	        				events: ['click'],
	        				onClick: function (evt, item) {
								var bar = item[0]; 										// bar (index)
								var x_value = this.data.labels[bar._index];				// date
								var y_value = this.data.datasets[0].data[bar._index];	// usage
								
								$('a[href="#hourly"]').tab('show');
								
								refreshByHour(x_value);
		        			}
						}
					});
		        });
		        
		        $.post( "api.php", { action: "byweek"}, function( result) {
		        	result = $.parseJSON(result);
		        	
		        	labels = [];
					points = [];
					
					var alp = "0.5";
					background_colors = [];
					border_colors = [];
					
			        $.each(result, function(i,v)
			        {
						labels.push(i);
						points.push(v);
						
						var color = getRandomColor();
						
						background_colors.push(color + "," + alp + ")");
						border_colors.push(color + ")");
			        });
			        
			        labels.reverse();
			        points.reverse();
			        
			        var myBarChart = new Chart(document.getElementById("byWeek"), {
					    "type": 'horizontalBar',
					    "data": {
						    "labels": labels,
						    "datasets": [{
							    //"label": "Hourly Consumption (Today)",
							    "data": points,
							    "fill": false,
							    "backgroundColor" : background_colors,
							    "borderColor" : border_colors,
							    "borderWidth": 2
						    }]
					    },
					    "options":{
						    plugins: {
								datalabels: {
									color: 'white',
									display: function(context) {
										return context.dataset.data[context.dataIndex] > 2;
									},
									font: {
										weight: 'bold'
									},
									formatter: Math.round
								}
							},
							"scales": {
								"xAxes": [{
									"ticks": {
										"beginAtZero": true,
										callback: function(value, index, values) {
					                        return value.toLocaleString() + 'kWh';
					                    }
									}
								}],
								"yAxes": [{
									"ticks": {
										callback: function(value, index, values) {
					                        return 'Week ' + value;
					                    }
									}
								}]
							},
							legend: {
								display: false,
	        				}
						}
					});
		        });
		        
		        $.post( "api.php", { action: "bymonth"}, function(result) {
		        	result = $.parseJSON(result);
		        	
		        	labels = [];
					points = [];
					
					var alp = "0.5";
					background_colors = [];
					border_colors = [];
					
			        $.each(result, function(i,v)
			        {
						labels.push(i);
						points.push(v);
						
						var color = getRandomColor();
						
						background_colors.push(color + "," + alp + ")");
						border_colors.push(color + ")");
			        });
			        
			        var myBarChart = new Chart(document.getElementById("byMonth"), {
					    "type": 'bar',
					    "data": {
						    "labels": labels,
						    "datasets": [{
							    //"label": "Hourly Consumption (Today)",
							    "data": points,
							    "fill": false,
							    "backgroundColor" : background_colors,
							    "borderColor" : border_colors,
							    "borderWidth": 2
						    }]
					    },
					    "options":{
						    plugins: {
								datalabels: {
									color: 'white',
									display: function(context) {
										return context.dataset.data[context.dataIndex] > 2;
									},
									font: {
										weight: 'bold'
									},
									formatter: Math.round
								}
							},
							"scales": {
								"xAxes": [{
									"ticks": {
										"beginAtZero": true
									}
								}],
								"yAxes": [{
									"ticks": {
										callback: function(value, index, values) {
					                        return value.toLocaleString('en-US', {minimumFractionDigits: 2}) + 'kWh';
					                    }
									}
								}]
							},
							legend: {
								display: false,
	        				}
						}
					});
		        });
			}
		</script>
	</head>
	
	<body>
		<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
			<a class="navbar-brand col-sm-3 col-md-2 mr-0" href="."><i class="fa fa-bar-chart" aria-hidden="true"></i>&nbsp;pimonitor</a>
		</nav>
		
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" id="hourly-tab" data-toggle="tab" href="#hourly" role="tab" aria-controls="hourly" aria-selected="true">Hourly</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="daily-tab" data-toggle="tab" href="#daily" role="tab" aria-controls="daily" aria-selected="false">Daily</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="weekly-tab" data-toggle="tab" href="#weekly" role="tab" aria-controls="weekly" aria-selected="false">Weekly</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="monthly-tab" data-toggle="tab" href="#monthly" role="tab" aria-controls="monthly" aria-selected="false">Monthly</a>
			</li>
		</ul>
		
		<div class="tab-content" id="myTabContent">
			<div class="tab-pane fade show active" id="hourly" role="tabpanel" aria-labelledby="hourly-tab">
				<canvas id="byHour" class="chartjs"></canvas>
			</div>
			<div class="tab-pane fade" id="daily" role="tabpanel" aria-labelledby="daily-tab">
				<canvas id="byDay" class="chartjs"></canvas>
			</div>
			<div class="tab-pane fade" id="weekly" role="tabpanel" aria-labelledby="weekly-tab">
				<canvas id="byWeek" class="chartjs"></canvas>
			</div>
			<div class="tab-pane fade" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
				<canvas id="byMonth" class="chartjs"></canvas>
			</div>
		</div>
		
		<div class="container-fluid">
			<div class="row">
				<div class="col-6">
					<span id="goback" class="ml-3" style="display:none;">
						<i class="fa fa-chevron-circle-left" aria-hidden="true"></i>&nbsp;<a href='' onclick='refreshByHour(); $("#goback").fadeOut(600); return false;'>Back</a>
					</span>
				</div>
				<div class="col-6">
					<span id="chart_title"><!-- Power Consumption --></span>
				</div>
			</div>
		</div>
	</body>
	
</html>