var ui = {
    init: function() {
        this.search.init();
        this.load.me();
    },
    search: {
        active: -1,
        init: function () {
            var search_class = this;
            $("#search").keydown(function(e) {
                console.log(e.which);
                switch (e.which) {
                    case 27: //escape key
                        search_class.blur();
                        break;
                    case 38: // up arrow key
                        if (search_class.active > -1) {
                            search_class.active--;
                            search_class.activate();
                        }
                        break;
                    case 40: // down arrow key
                        if (search_class.active < $('.search_result').length - 1) {
                            search_class.active++;
                            search_class.activate();
                        }
                        break;
                    case 13: // enter key
                        if (search_class.active > -1) {
                            $('.search_result').eq(search_class.active).click();
                            search_class.blur();
                        }
                    default:
                        search_class.active = -1;
                        search_class.activate();
                        ui.search.search($(this).val());
                        break;
                }
            });
        },
        blur: function() {
            $("#search").blur();
            this.clearItems();
            this.active = -1;
        },
        activate: function() {
            $('.search_result').removeClass('search_result_active');
            if (this.active > -1) {
                $('.search_result').eq(this.active).addClass('search_result_active');
            }
        },
        search: function(q) {
            var search_class = this;
            $.post('search', {'q': q}, function(data) {
                search_class.displayItems(data);
            });
        },
        displayItems: function(data) {
            this.clearItems();
            for (var i=0; i<data.length; i++) {
                var item = data[i];
                this.appendItem(item);
            }
            if (this.active > data.length - 1) {
                this.active = data.length - 1;
                this.activate();
            }
        },
        appendItem: function(item) {
            var search_class = this;
            var $result = $('#primatives .search_result').clone();
            $result.html(item.body);
            $result.click(function() {
                //search_class.blur();
                switch (item.type) {
                    case 'class':
                        ui.load.classData.byId(item.id);
                        break;
                    case 'person':
                        ui.load.person.byId(item.id);
                        break;
                }
            });
            $result.appendTo("#search_results");
        },
        clearItems: function() {
            $("#search_results").html('');
        },
        
    },
    action: function(action) {
        action = action.split('/');
        switch(action[0]) {
            case 'class':
                ui.load.classById(action[1]);
                break;
            case 'compare':
                ui.pages.display('compare');
                break;
            default:
                throw 'Unrecognized action type ' + action[0];
        }
    },
    load: {
        me: function() {
            $.post('load/me', function(data) {
                var view = new components.ScheduleView('#user_schedule');
                view.importData(data.classes);
            });
        },
        person: {
            byId: function(id) {
                ui.pages.display('compare');
                ui.compare.append(id);
            }
        },
        classData: {
            byInfo: function(info) {
                classData = this;
                $.post('load/classData/byInfo', info, function(data) {
                    classData.load(data);
                });
            },
            byId: function(id) {
                classData = this;
                $.post('load/classData/byId', {'id': id}, function(data) {
                    classData.load(data);
                });
            },
            load: function(data) {
                console.log(data);
                ui.pages.display('class');
                var names = [data.name];
                var periods = [this.genPeriodStr(data.day, data.period)];
                var teachers = [data.teacher];
                
                var relatedPeriods = [];
                for (var i=0; i<data.related.periods.length; i++) {
                    var item = data.related.periods[i];
                    relatedPeriods.push(this.genPeriodStr(item.day, item.period));
                }
                
                var relatedNames = [];
                for (var i=0; i<data.related.names.length; i++) {
                    var item = data.related.names[i];
                    relatedNames.push(this.name);
                }
                
                names = names.concat(relatedNames);
                periods = periods.concat(relatedPeriods);
                teachers = teachers.concat(data.related.teachers);
                
                this.clear();
                this.fillSelect('#class_name', names);
                this.fillSelect('#class_period', periods);
                this.fillSelect('#class_teacher', teachers);
                
                for (var i=0; i<data.people.length; i++) {
                    $("#class ul").append('<li>' + data.people[i] + '</li>');
                }
                
                $('#class select').change(function(e) {
                    var info = {};
                    info.name = $('#class_name').val();
                    info.teacher = $('#class_teacher').val();
                    var periodStr = $('#class_period').val();
                    
                    if (periodStr.indexOf('Monday') != -1) {
                        info.day = 1;
                    } else if (periodStr.indexOf('Tuesday') != -1) {
                        info.day = 2;
                    } else if (periodStr.indexOf('Wednesday') != -1) {
                        info.day = 3;
                    } else if (periodStr.indexOf('Thursday') != -1) {
                        info.day = 4;
                    } else if (periodStr.indexOf('Friday') != -1) {
                        info.day = 5;
                    } else {
                        throw 'Error processing period string: ' + periodStr;
                    }
                    
                    info.period = periodStr.substr(periodStr.length-1, 1);
                    
                    ui.load.classData.byInfo(info);
                });
            },
            clear: function() {
                $("#class select").unbind('change').html('');
                $("#class ul").html('');
            },
            genPeriodStr: function(day, period) {
                switch(day) {
                    case '1':
                        day = 'Monday';
                        break;
                    case '2':
                        day = 'Tuesday';
                        break;
                    case '3':
                        day = 'Wednesday';
                        break;
                    case '4':
                        day = 'Thursday';
                        break;
                    case '5':
                        day = 'Friday';
                        break;
                    default:
                        throw 'Day index out of bounds';
                }
                var string = day + ', ' + utils.getPeriodString(period);
                return string;
            },
            fillSelect: function(elem, items) {
                for (var i=0; i<items.length; i++) {
                    $(elem).append('<option>' + items[i] + '</option>');
                }
            }
        }
    },
    pages: {
        current: 'compare',
        all: ['compare', 'class'],
        display: function(page) {
            if (page != this.current && $.inArray(page, this.all) != -1) {
                this.current = page;
                $('page').hide();
                $('page#' + page).show();
            }
        }
    },
    compare: {
        append: function(id) {
            $.post('load/person', {'id': id}, function(data) {
                $elem = $('#primatives > .compare_column').clone();
                $elem.find('.compare_column_name').html(person);
                $elem.appendTo('page.compare');
                var view = new components.ScheduleView($elem);
                view.importData(data.classes);
            });
        }
    }
}

var components = {
    ScheduleView: function(elem) {
        var create = function() {
            $(elem).addClass('ScheduleView');
        }
        this.importData = function(data) {
            // data is an array of classes
            // each element of the array is an object with the following properties:
            //   id = class id
            //   name = class name
            //   day = class day
            //   period = class period
            //   teacher = class teacher
            
            for (var i=0; i<data.length; i++) {
                var item = data[i];
                addClass(item);
            }
        }
        var addClass = function(data) {
            var $class = $('#primatives .ScheduleView_class').clone();
            $class.attr('data-id', data.id);
            $class.find('.ScheduleView_name').html(data.name);
            switch(data.day) {
                case '1':
                    var day = 'Mon';
                    break;
                case '2':
                    var day = 'Tues';
                    break;
                case '3':
                    var day = 'Wed';
                    break;
                case '4':
                    var day = 'Thurs';
                    break;
                case '5':
                    var day = 'Fri';
                    break;
                default:
                    throw 'Day index out of bounds in ScheduleView constructor';
            }
            $class.find('.ScheduleView_day').html(day);
            $class.find('.ScheduleView_period').html(utils.getPeriodString(data.period));
            $class.find('.ScheduleView_name').html(data.name);
            $class.find('.ScheduleView_teacher').html(data.teacher);
            $class.click(function() {
                ui.load.classData.byId(data.id);
            });
            $class.appendTo(elem);
        }
        
        create();
    }
}

var utils = {
    getPeriodString: function(period) {
        switch (period) {
            case '0':
                return 'AM';
                break;
            case '3.5':
                return 'Lunch';
                break;
            case '6':
                return 'PM';
                break;
            default:
                return 'P' + period;
                break;
        }
    }
}

$(function() {
    ui.init();
});