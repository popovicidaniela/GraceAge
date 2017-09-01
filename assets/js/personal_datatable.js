

google.charts.load('current', {packages: ['bar'], "callback": initialize});
//google.charts.setOnLoadCallback(initialize());

var id;
var isInit = false;

$(document).ready(function(){
    
    /*
     * Makes the datatable
     * Automatically sorts on name
     * Allows to search on name only.
     */
    var table = $('#personal-datatable').DataTable({
        "language": {
            "search": "",   //No text before the search field.
            "searchPlaceholder": "Find patient" //Placeholder to indicate what to type
        },
        //Puts search box underneath the "Showing elements".
        "dom": '<lf<t>ip>', 
        
        //Only search on the first column.
        "aoColumnDefs": [
            {"bSearchable": false, "aTargets": [1,2,3]}
        ],
        "paging": false
    });
    id = 2;
    
    $('#modChart').on('shown.bs.modal', drawChart);
    
    //Get the username from the url. If 'undefined', no urgent patient was clicked.
    var patient_to_find = decodeURI(getUrlVars()["username"]);
    
    //If no urgent patient was clicked, no username will be seen, so we have
    //to check for that.
    //If an urgent patient was clicked, search on that patients name and redraw
    //the table.
    if(patient_to_find !== "undefined") table.search(patient_to_find).draw();
});
    
/*
 * 
 * @param {type} new_id
 */
function setID(new_id){
    id = new_id;
}

/*
 * 
 * @param {type} new_title
 * @param {type} new_subtitle
 */
function setBarChartTitle(new_title, new_subtitle){
    document.getElementById("exampleModalLabel").innerHTML = new_title;
    document.getElementById("modalSubtitle").innerHTML = new_subtitle;
}

/*
 * 
 * @param {type} bad_answer_array
 * @returns {undefined}
 */
function setBadAnswersDiv(bad_answer_array){
    document.getElementById("badanswer").innerHTML = bad_answer_array;
}

/*
 * If a caregiver clicks on an urgent patient in the general page, we can get
 * the username of the clicked patient from the url, to search on this patient
 * immediately.
 * @returns {Array|getUrlVars.vars}
 */
function getUrlVars(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++){
         hash = hashes[i].split('=');
         vars.push(hash[0]);
         vars[hash[0]] = hash[1];
     }
     return vars;
}

/*
 * When a table row is clicked, call the drawChart() function.
 */
function initialize(){
    if(!isInit){
        $('#personal-datatable tbody tr').on('click', function(){
            drawChart();
        });
        isInit = true;
    }else{
        drawChart();
    }
    //When clicked on a table row, this function is called, which opens the dialog screen and draws the chart.
}

/*
 * Draw the bar chart with the correct data. (Basically the same as the bar
 * chart from chart.js
 */
function drawChart(){
    //alert("hello");
    //Get the data of the clicked patient from the database using a function from the CaregiverController
    var request = $.ajax({
        method: "POST",
        url: "getPersonalScores",   //PHP function from the controller.
        data: {id: id}, //Give the ID of the patient you want to see data from.
        dataType: "json"    //Get data as json.
    });
    
    request.done(function(data){
        var chart_data = google.visualization.arrayToDataTable([]);
        chart_data.addColumn('string', "");
        chart_data.addColumn('number', "Score");
        for(var i = 0; i < data.length; i++){
            chart_data.addRow([data[i].Topic, parseInt(data[i].Score)]);
        }

         var options = {
                bars: 'horizontal',
                width: 800, height: 400,
                //chartArea: {width: '100%', height: '100%'},
                chartArea: {left: '8%', top: '8%', width: '80%', height: '80%'},
                colors: ['#cddc39'],
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
                    }
                }
            };
         var chart = new google.charts.Bar(document.getElementById('canvas'));  //Specify where to draw the chart.
         chart.draw(chart_data, google.charts.Bar.convertOptions(options)); //Draw the chart with the correct data and options
    });
}
