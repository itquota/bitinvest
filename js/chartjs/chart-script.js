/*
* Trending line chart
*/
//var randomScalingFactor = function(){ return Math.round(Math.random()*10)};
/* var data = {
	labels : ["JAN","FEB","MAR","APR","MAY","JUNE","JULY"], 
	datasets : [
		{
			label: "First dataset",
			fillColor : "rgba(128, 222, 234, 0.6)",
			strokeColor : "#ffffff",
			pointColor : "#00bcd4",
			pointStrokeColor : "#ffffff",
			pointHighlightFill : "#ffffff",
			pointHighlightStroke : "#ffffff",
			data: [1, 5, 2, 4, 8, 5, 8]
		},
		{
			label: "Second dataset",
			fillColor : "rgba(128, 222, 234, 0.3)",
			strokeColor : "#80deea",
			pointColor : "#00bcd4",
			pointStrokeColor : "#80deea",
			pointHighlightFill : "#80deea",
			pointHighlightStroke : "#80deea",
			data: [6, 2, 9, 2, 5, 10, 4]
		}
	]
}; */
//var data = JSON.parse(document.getElementById("dataValue").value);
 //console.log(datesdata);
// alert(data);

//var data={"labels":["2","2","2","1","0","0","0"],"datasets":{"1":{"label":"First dataset","fillColor":"#A7B3BC","strokeColor":"#ffffff","pointColor":"#A7B3BC","pointStrokeColor":"#ffffff","pointHighlightFill":"black","pointHighlightStroke":"#A7B3BC","data":usersdata},"2":{"label":"Second dataset","fillColor":"#2577B5","strokeColor":"#ffffff","pointColor":"#2577B5","pointStrokeColor":"#ffffff","pointHighlightFill":"black","pointHighlightStroke":"#80deea","data":["0.2","0.2","0.2","1","0","0","0"]}}};
var data={"labels":["23","24","25","26","27","28","29"],"datasets":{"1":{"label":"First dataset","fillColor":"#A7B3BC","strokeColor":"#ffffff","pointColor":"#A7B3BC","pointStrokeColor":"#ffffff","pointHighlightFill":"black","pointHighlightStroke":"#A7B3BC","data":["103","206","246","357","321","374","162"]},"2":{"label":"Second dataset","fillColor":"#2577B5","strokeColor":"#ffffff","pointColor":"#2577B5","pointStrokeColor":"#ffffff","pointHighlightFill":"black","pointHighlightStroke":"#80deea","data":["60","211","220","297","273","287","126"]}}};
var nReloads = 0;
var min = 1;
var max = 10;
var l =0;
var trendingLineChart;
function update() {
	nReloads++;

	var x = Math.floor(Math.random() * (max - min + 1)) + min;
	var y = Math.floor(Math.random() * (max - min + 1)) + min;
	trendingLineChart.addData([x, y], data.labels[l]);
	trendingLineChart.removeData();
	l++;
	if( l == data.labels.length)
		{ l = 0;}
}
//setInterval(update, 300000);



/*
Polor Chart Widget
*/
 




window.onload = function(){
	var trendingLineChart = document.getElementById("trending-line-chart").getContext("2d");
	window.trendingLineChart = new Chart(trendingLineChart).Line(data, {		
		scaleShowGridLines : true,///Boolean - Whether grid lines are shown across the chart		
		scaleGridLineColor : "#CECECE",//String - Colour of the grid lines		
		scaleGridLineWidth : 1,//Number - Width of the grid lines		
		scaleShowHorizontalLines: true,//Boolean - Whether to show horizontal lines (except X axis)		
		scaleShowVerticalLines: false,//Boolean - Whether to show vertical lines (except Y axis)		
		bezierCurve : true,//Boolean - Whether the line is curved between points		
		bezierCurveTension : 0.4,//Number - Tension of the bezier curve between points		
		pointDot : true,//Boolean - Whether to show a dot for each point		
		pointDotRadius : 5,//Number - Radius of each point dot in pixels		
		pointDotStrokeWidth : 2,//Number - Pixel width of point dot stroke		
		pointHitDetectionRadius : 20,//Number - amount extra to add to the radius to cater for hit detection outside the drawn point		
		datasetStroke : true,//Boolean - Whether to show a stroke for datasets		
		datasetStrokeWidth : 3,//Number - Pixel width of dataset stroke		
		datasetFill : true,//Boolean - Whether to fill the dataset with a colour				
		animationSteps: 10,// Number - Number of animation steps		
		animationEasing: "easeOutQuart",// String - Animation easing effect			
		tooltipTitleFontFamily: "robotoregular",// String - Tooltip title font declaration for the scale label		
		scaleFontSize: 12,// Number - Scale label font size in pixels		
		scaleFontStyle: "normal",// String - Scale label font weight style	
		scaleFontFamily:"robotoregular",		
		scaleFontColor: "black",// String - Scale label font colour
		tooltipEvents: ["mousemove", "touchstart", "touchmove"],// Array - Array of string names to attach tooltip events		
		tooltipFillColor: "rgba(255,255,255,0.8)",// String - Tooltip background colour		
		tooltipTitleFontFamily: "robotoregular",// String - Tooltip title font declaration for the scale label		
		tooltipFontSize: 12,// Number - Tooltip label font size in pixels
		tooltipFontColor: "#000",// String - Tooltip label font colour		
		tooltipTitleFontFamily: "robotoregular",// String - Tooltip title font declaration for the scale label		
		tooltipTitleFontSize: 14,// Number - Tooltip title font size in pixels		
		tooltipTitleFontStyle: "bold",// String - Tooltip title font weight style		
		tooltipTitleFontColor: "#000",// String - Tooltip title font colour		
		tooltipYPadding: 8,// Number - pixel width of padding around tooltip text		
		tooltipXPadding: 16,// Number - pixel width of padding around tooltip text		
		tooltipCaretSize: 10,// Number - Size of the caret on the tooltip		
		tooltipCornerRadius: 6,// Number - Pixel radius of the tooltip border		
		tooltipXOffset: 10,// Number - Pixel offset from point x to tooltip edge
		responsive: true
		
		});
		
			var trendingLineChartUser = document.getElementById("trending-line-chart-user").getContext("2d");
	window.trendingLineChartUser = new Chart(trendingLineChartUser).Line(data1, {			
		scaleShowGridLines : true,///Boolean - Whether grid lines are shown across the chart		
		scaleGridLineColor : "#CECECE",//String - Colour of the grid lines		
		scaleGridLineWidth : 1,//Number - Width of the grid lines		
		scaleShowHorizontalLines: true,//Boolean - Whether to show horizontal lines (except X axis)		
		scaleShowVerticalLines: false,//Boolean - Whether to show vertical lines (except Y axis)		
		bezierCurve : true,//Boolean - Whether the line is curved between points		
		bezierCurveTension : 0.4,//Number - Tension of the bezier curve between points		
		pointDot : true,//Boolean - Whether to show a dot for each point		
		pointDotRadius : 5,//Number - Radius of each point dot in pixels		
		pointDotStrokeWidth : 2,//Number - Pixel width of point dot stroke		
		pointHitDetectionRadius : 20,//Number - amount extra to add to the radius to cater for hit detection outside the drawn point		
		datasetStroke : true,//Boolean - Whether to show a stroke for datasets		
		datasetStrokeWidth : 3,//Number - Pixel width of dataset stroke		
		datasetFill : true,//Boolean - Whether to fill the dataset with a colour				
		animationSteps: 10,// Number - Number of animation steps		
		animationEasing: "easeOutQuart",// String - Animation easing effect			
		tooltipTitleFontFamily: "robotoregular",// String - Tooltip title font declaration for the scale label		
		scaleFontSize: 12,// Number - Scale label font size in pixels		
		scaleFontStyle: "normal",// String - Scale label font weight style	
		scaleFontFamily:"robotoregular",		
		scaleFontColor: "black",// String - Scale label font colour
		tooltipEvents: ["mousemove", "touchstart", "touchmove"],// Array - Array of string names to attach tooltip events		
		tooltipFillColor: "rgba(255,255,255,0.8)",// String - Tooltip background colour		
		tooltipTitleFontFamily: "robotoregular",// String - Tooltip title font declaration for the scale label		
		tooltipFontSize: 12,// Number - Tooltip label font size in pixels
		tooltipFontColor: "#000",// String - Tooltip label font colour		
		tooltipTitleFontFamily: "robotoregular",// String - Tooltip title font declaration for the scale label		
		tooltipTitleFontSize: 14,// Number - Tooltip title font size in pixels		
		tooltipTitleFontStyle: "bold",// String - Tooltip title font weight style		
		tooltipTitleFontColor: "#000",// String - Tooltip title font colour		
		tooltipYPadding: 8,// Number - pixel width of padding around tooltip text		
		tooltipXPadding: 16,// Number - pixel width of padding around tooltip text		
		tooltipCaretSize: 10,// Number - Size of the caret on the tooltip		
		tooltipCornerRadius: 6,// Number - Pixel radius of the tooltip border		
		tooltipXOffset: 10,// Number - Pixel offset from point x to tooltip edge
		responsive: true
		});

		var doughnutChart = '';
		window.myDoughnut = new Chart(doughnutChart).Doughnut(doughnutData, {
			segmentStrokeColor : "#fff",
			tooltipTitleFontFamily: "'Roboto','Helvetica Neue', 'Helvetica', 'Arial', sans-serif",// String - Tooltip title font declaration for the scale label		
			percentageInnerCutout : 50,
			animationSteps : 15,
			segmentStrokeWidth : 4,
			animateScale: true,
			percentageInnerCutout : 60,
			responsive : true
		});

		var trendingBarChart = '';
		window.trendingBarChart = new Chart(trendingBarChart).Bar(dataBarChart,{
			scaleShowGridLines : false,///Boolean - Whether grid lines are shown across the chart
			showScale: true,
			animationSteps:15,
			tooltipTitleFontFamily: "'Roboto','Helvetica Neue', 'Helvetica', 'Arial', sans-serif",// String - Tooltip title font declaration for the scale label		
			responsive : true
		});

		/* window.trendingRadarChart = new Chart(document.getElementById("trending-radar-chart").getContext("2d")).Radar(radarChartData, {
		    
		    angleLineColor : "rgba(255,255,255,0.5)",//String - Colour of the angle line		    
		    pointLabelFontFamily : "'Roboto','Helvetica Neue', 'Helvetica', 'Arial', sans-serif",// String - Tooltip title font declaration for the scale label	
		    pointLabelFontColor : "#fff",//String - Point label font colour
		    pointDotRadius : 4,
		    animationSteps:15,
		    pointDotStrokeWidth : 2,
		    pointLabelFontSize : 12,
			responsive: true
		}); */

		// var pieChartArea = document.getElementById("pie-chart-area").getContext("2d");
		// window.pieChartArea = new Chart(pieChartArea).Pie(pieData,{
		// 	responsive: true		
		// });


};
