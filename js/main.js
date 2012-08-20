var ui = {
    animationDuration: 150,
    init: function() {
        this.search.init();
        this.load.me();
    },
    search: {
        active: -1,
        init: function () {
            var search_class = this;
            $("#search").keypress(function(e) {
                search_class.active = -1;
                search_class.activate();
                ui.search.search($(this).val());
            });
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
                        e.preventDefault();
                        break;
                    case 40: // down arrow key
                        if (search_class.active < $('.search_result').length - 1) {
                            search_class.active++;
                            search_class.activate();
                        }
                        e.preventDefault();
                        break;
                    case 13: // enter key
                        if (search_class.active == -1) {
                            search_class.active = 0;
                        }
                        $('.search_result').eq(search_class.active).click();
                        search_class.blur();
                        e.preventDefault();
                        break;
                }
            });
        },
        blur: function() {
            //$("#search").blur();
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
            $.post('load/person/me', function(data) {
                if (data.classes.length == 0) {
                    $('#user_schedule').html('<a href="import/">Import your class schedule...</a>');
                } else {
                    var view = new components.ScheduleView('#user_schedule');
                    view.importData(data.classes);
                }
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
                var name = data.name;
                var period = {'day': data.day, 'period': data.period};
                var period_str = this.genPeriodStr(data.day, data.period);
                var teacher = data.teacher;
                
                var relatedNames = [];
                for (var i=0; i<data.related.names.length; i++) {
                    var item = data.related.names[i];
                    relatedNames.push(item.name);
                }
                
                var names = [name].concat(relatedNames);
                var periods = [period].concat(data.related.periods);
                var teachers = [teacher].concat(data.related.teachers);
                
                // Sort names and teachers alphabetically
                names.sort();
                teachers.sort();
                
                // Sort periods
                periods.sort(function(a, b) {
                    if (a.day < b.day) {
                        return -1;
                    } else if (b.day < a.day) {
                        return 1;
                    } else {
                        if (a.period < b.period) {
                            return -1;
                        } else if (b.period < a.period) {
                            return 1;
                        } else {
                            return 0;
                        }
                    }
                });
                
                // Generate period strings
                var periods_str = [];
                for (var i=0; i<periods.length; i++) {
                    var item = periods[i];
                    periods_str.push(this.genPeriodStr(item.day, item.period));
                }
                
                this.clearSelect();
                this.fillSelect('#class_name', names, name);
                this.fillSelect('#class_period', periods_str, period_str);
                this.fillSelect('#class_teacher', teachers, teacher);
                
                // Animate out the list of people, then animate in the new list
                $("#class ul").stop(true, true).hide('drop', {'direction': 'down'}, ui.animationDuration, function() {
                    ui.load.classData.clearPeople();
                    for (var i=0; i<data.people.length; i++) {
                        var name = data.people[i].name;
                        var id = data.people[i].id;
                        $html = $('<li>' + name + '</li>');
                        $html.click(function() {
                            ui.load.person.byId(id);
                        });
                        $html.appendTo("#class ul");
                    }
                }).show('drop', {'direction': 'up'}, ui.animationDuration);
                    
                
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
            clearSelect: function() {
                $("#class select").unbind('change').html('');
            },
            clearPeople: function() {
                $("#class ul").html('');
            },
            genPeriodStr: function(day, period) {
                day = utils.getDayStringLong(day);
                var string = day + ', ' + utils.getPeriodString(period);
                return string;
            },
            fillSelect: function(elem, items, selected) {
                for (var i=0; i<items.length; i++) {
                    $html = $('<option>' + items[i] + '</option>');
                    if (items[i] == selected) {
                        $html.attr('selected', 'selected');
                    }
                    $html.appendTo(elem);
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
                $('page').not('#' + page).hide('drop', {'direction': 'down'}, ui.animationDuration);
                $('page#' + page).show('drop', {'direction': 'up'}, ui.animationDuration);
                if (page != 'compare') {
                    ui.compare.unColorize();
                }
            }
        }
    },
    compare: {
        current: [],
        append: function(id) {
            var this_class = this;
            $.post('load/person/byId', {'id': id}, function(data) {
                if (ui.compare.current.indexOf(data.id) == -1) {
                    $elem = $('#primatives > .compare_column').clone();
                    $elem.find('.compare_column_name').html(data.name);
                    $elem.insertBefore('#compare_column_info');
                    var view = new components.ScheduleView($elem);
                    view.importData(data.classes);
                    ui.compare.current.push(data.id);
                }
                ui.pages.display('compare');
                this_class.colorize();
            });
        },
        remove: function(id) {
            var index = ui.compare.current.indexOf(id);
            if (index != -1) {
                $('#compare .compare_column').eq(index).empty().remove();
                ui.compare.current.splice(index, 1);
            }
        },
        colors: [
            '#B7E4FF',
            '#70CAFF',
            '#71B1D7',
            '#3C93C7',
            '#7698D7',
            '#76A6FF',
            '#BAD3FF',
            '#3F6FC7',
            '#6BD7CB',
            '#67FFEF',
            '#B3FFF7',
            '#37C7B8'
        ],
        colorize: function() {
            var $elems = $('#wrapper .ScheduleView_class');
            var $user_elems = $('#user_schedule .ScheduleView_class');
            var lastColor = 0;
            var colors = this.colors;
            var setColors = {};
            $elems.css({'background': 'none'});
            for (var a=0; a<$user_elems.length; a++) {
                var data_a = $elems.eq(a).data('data');
                for (var b=$user_elems.length; b<$elems.length; b++) {
                    var data_b = $elems.eq(b).data('data');
                    if (data_a.id == data_b.id) {
                        if (data_a.id in setColors) {
                            var color = setColors[data_a.id];
                        } else {
                            var color = colors[lastColor];
                            setColors[data_a.id] = color;
                            if (lastColor < colors.length - 1) {
                                lastColor++;
                            } else {
                                lastColor = 0;
                            }
                        }
                        $elems.eq(a).animate({'background-color': color}, ui.animationDuration);
                        $elems.eq(b).animate({'background-color': color}, ui.animationDuration);
                    }
                }
            }
        },
        unColorize: function() {
            $('#wrapper .ScheduleView_class').animate({'background-color': '#FFFFFF'}, ui.animationDuration);
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
            $class.data('data', data);
            $class.find('.ScheduleView_name').html(data.name);
            day = utils.getDayStringShort(data.day);
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
    },
    getDayStringLong: function(day) {
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
        return day;
    },
    getDayStringShort: function(day) {
        switch(day) {
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
                throw 'Day index out of bounds';
        }
        return day;
    }
    
}

$(function() {
    ui.init();
});
