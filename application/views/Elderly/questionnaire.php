<div id='questionnairy_wrapper' class="container">
    <div id='questionnairy_content' class="container">
        
        <div id="score_row" class="row">
            <div class="col-xs-offset-3 col-xs-6">
            
                <div id="score_panel" class="panel panel-default">
                    <span class="fontfamily" id="score_text">{score_text} <span class="fontfamily" id="score"> {score} <i id="starIcon" class="fa fa-star fa-1x"></i></span></span>
                </div> 
            </div>   
        </div>
        <div id='question_row' class="row">
            <div id='question_column' class="col-xs-offset-1 col-xs-10">


                <div id="question_panel" class="panel panel-default">
                    <table>
                        <tr>
                            <th id="tableheader_topic">Topic</th>
                            <th id="tableheader_Question">Question</th>
                        </tr>
                        {questions}
                        <tr>
                            <td id="topic_placeholder">{Topic}</td>
                            <td id="question_placeholder">{Question}</td>
                        </tr>
                        {/questions}
                    </table>
                </div>
            </div>
        </div>
        <div id='answers_row' class="row">
            <div class="col-sm-1"> 
            </div>

            {answers}
            <div class="col-sm-2">
                <button type="button" id="{id}" onclick="{onclick}" class="{className}">{name}</button>
            </div>
            {/answers}
            <div class="col-sm-1">
            </div>
        </div>


        <div id='bar_row' class="row">
            <div class="col-xs-1">
            </div>
            <div id='progress_column' class="col-xs-10">

                <div class="progress">
                    <div id="progressbar" class="progress-bar" role="progressbar" style="width:{initial_pbWidth}%">
                    </div>
                </div>
                <span class='fontfamily' id="progressbarQuestionCount">{pbQuestionText} <span id="pbQuestionCount">{questionNumber} {question_out_of}</span> </span>   
            </div>
            <div class="col-xs-3">
            </div>
        </div>
    </div>

    <footer>
        <div class="row">
            <div class="col-xs-1">
            </div>
            {navigationbuttons}
            <div class="col-xs-5" id="{title}">
                <button id="{id}" style="white-space: normal" type="button" class ="{class}" onclick="{func}" >{name}</button>
            </div>
            {/navigationbuttons}
            <div class="col-xs-1">
            </div>
        </div>
    </footer>
</div>