<!DOCTYPE html>
<html>
    <head>
        <title>Class Schedules</title>
        <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/main.css"></link>
        <script type="text/javascript" src="<?=base_url()?>js/jquery-1.8.0.min.js"></script>
        <script type="text/javascript" src="<?=base_url()?>js/jquery-ui-1.8.23.custom.min.js"></script>
        <script type="text/javascript" src="<?=base_url()?>js/main.js"></script>
    </head>
    <body>
        <div id="wrapper">
            <div id="left">
                <input type="text" id="search" placeholder="Find classes / people..." autocomplete="off"></input>
                <div id="search_results">
                </div>
                <div id="user_schedule">
                </div>
                <div id="logout">
                    <a href="<?=site_url('login/logout')?>">Logout</a>
                </div>
            </div>
            <div id="right">
                <div id="topbar_right">
                    <page id="compare">
                        <div class="compare_column" id="compare_column_info">
                            Search for a person to add them to the schedule comparison list...
                        </div>
                    </page>
                    <page id="class">
                        <div class="class_column">
                            <span>Class Name:</span>
                            <select id="class_name">
                            </select>
                            <span>Day / Period:</span>
                            <select id="class_period">
                            </select>
                            <span>Teacher:</span>
                            <select id="class_teacher">
                            </select>
                        </div>
                        <div class="class_column">
                            <ul>
                            </ul>
                        </div>
                    </page>
                </div>
            </div>
        </div>
        
        <div id="primatives">
            <div class="ScheduleView_class">
                <div class="ScheduleView_time">
                    <div class="ScheduleView_day"></div>
                    <div class="ScheduleView_period"></div>
                </div>
                <div class="ScheduleView_right">
                    <div class="ScheduleView_name"></div>
                    <div class="ScheduleView_teacher"></div>
                </div>
            </div>
            <div class="search_result"></div>
            <div class="compare_column">
                <div class="compare_column_name"></div>
            </div>
        </div>
    </body>
</html>
