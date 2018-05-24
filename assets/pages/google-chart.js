      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Week', 'Order', 'Sales'],
          [null,  1,      1],
          ['25',  3,      2],
          ['50',  5,      3],
          ['75',  25,       30],
          ['100',  30,      35],
          ['125', 40,      40],
          ['150',  50,      50],
          ['175',  50,      60],
          ['200',  55,     60],
          ['200',  0,      5],
          ['225',  10,      60],
          ['250',  0,      5]
          
        ]);

        var options = {
          title: 'Company Performance',
          hAxis: {title: 'Week',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0},
          vAxis: {maxValue: 60},
          vAxis: { gridlines: { count: 7 } }
        };

        var chart = new google.visualization.AreaChart(document.getElementById('morris-area-example'));
        chart.draw(data, options);
      }