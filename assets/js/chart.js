
google.charts.load('current', {packages: ['bar']});
//do this callback function when loading the page.
google.charts.setOnLoadCallback(drawMultSeries);

var chart_data;
var options;
var chart;

/*
 * To redraw only when window resize is completed and avoid multiple triggers,
 * create trigger to resizeEnd event.
 */
$(window).resize(function(){
    if(this.resizeTO) clearTimeout(this.resizeTO);
    this.resizeTO = setTimeout(function(){
        $(this).trigger('resizeEnd');
    }, 250); //Waits 250ms to recognize a end of resize.
});

//Redraw chart when window resize is completed.
$(window).on('resizeEnd', function(){
    chart.draw(chart_data, google.charts.Bar.convertOptions(options));
});

function drawMultSeries(){
    //Get the json data from the Controller function getArray.
    $.getJSON("getArray", function(data){
        chart_data = google.visualization.arrayToDataTable([]);
        
        //Add a value for the y-axis, this can be left empty in this case.
        chart_data.addColumn('string', "");
        
        //Displays how the score should be interpreted. It is stored as the
        //last element of the json object.
        chart_data.addColumn('number', data[Object.keys(data).length].Score);
        
        //Add all topics and corresponding score to the bar chart
        //Leave out the last element, since it contains the title.
        for(var i = 1; i < Object.keys(data).length - 1; i++){
            chart_data.addRow([data[i].Topic, parseInt(data[i].Score)]);
        }
        
        //Add options for the layout of the chart.
        options = {
            bars: 'horizontal',
            chartArea: {width: '100%', height: '100%'}, //This is the width of the bar chart inside its div
            colors: ['lightgray'],
            legend: {
                position: 'none'
            },
            vAxis: {
                textPosition: 'none'
            },
            hAxis: {
                textPosition: 'none',
                viewWindow: {
                    max: 100
                },
                ticks: [0, 25, 50, 75, 100]
            }
        };
        //Create a new chart and define where to draw it.
        chart = new google.charts.Bar(document.getElementById('chart_div'));
        //Actually draws the chart.
        chart.draw(chart_data, google.charts.Bar.convertOptions(options));
    });
}