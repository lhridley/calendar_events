/** 
* @file
*
*/

(function ($, Drupal, DrupalSettings){
    'use strict';

    var deb = 1;
    var opt;

    var debug = function(param){
        if (deb==1){
            return console.log(param);
        }
        return false;
    }
    //debug(DrupalSettings.calendar_events.text_initial_date);
    //debug(DrupalSettings);
    
    var global_events_json = JSON.parse(DrupalSettings.calendar_events.eventsjson);
    var array_dates_data = [];
    var text_in_modal = DrupalSettings.calendar_events.text_in_modal;
    var text_end_date = DrupalSettings.calendar_events.text_end_date;
    var text_initial_date = DrupalSettings.calendar_events.text_initial_date;
    var num_cal = DrupalSettings.calendar_events.num_cal;
    var drupal_lang = DrupalSettings.calendar_events.lang.toLowerCase();
    var lang = (DrupalSettings.calendar_events.lang.length > 0 && typeof pickmeup.defaults.locales[drupal_lang] == 'object' ) ? drupal_lang : 'en';
    var bg_color = DrupalSettings.calendar_events.bg_color;
    var bg_color_selected = DrupalSettings.calendar_events.bg_color_selected;
    var bg_color_today = DrupalSettings.calendar_events.bg_color_today;
    var bg_color_event = DrupalSettings.calendar_events.bg_color_event;
    var border_radius = DrupalSettings.calendar_events.border_radius;
    var color = DrupalSettings.calendar_events.color;
    var color_event = DrupalSettings.calendar_events.color_event;
    var color_other = DrupalSettings.calendar_events.color_other;
    var color_month = DrupalSettings.calendar_events.color_month;
    var week_first_day = DrupalSettings.calendar_events.week_first_day;

    

    function validateColors(color){
        if(null == color ){
            return false;
        }
        if(color.length > 3 && color.length <= 7 && color[0] =="#"){
            return true;
        } 
        return false;
    }

    function validateRadius(radius){
        var len = radius.length;
        var res = false;
        for(var i=0; i < (len - 1); i++){
            if(typeof parseInt(radius[i]) != 'number'){              
                return res;
            }
        }
        if(radius[len - 1] != '%'){
          return res;
        }
        return true;
    }

    function setCss(){
        if(validateColors(bg_color)){
            $('.containerDate .pickmeup').css('background-color', bg_color);
        } 
        if(validateColors(color_other)){ 
            $('.containerDate .pickmeup .pmu-day-of-week, .containerDate .pickmeup .pmu-not-in-month, .containerDate .pickmeup .pmu-instance .pmu-button').css('color', color_other, 'important');
        }
        if(validateColors(color)){ 
            $(".containerDate .pickmeup .pmu-instance .pmu-button").css("color", color, 'important');
        }
        if(validateRadius(border_radius)){ 
            $(".containerDate .pickmeup .pmu-instance div[class*='__event_calendar']").css("border-radius", border_radius, 'important');
        }
        if(validateColors(color_event)){
            $(".containerDate .pickmeup .pmu-instance div[class*='__event_calendar']").css("color", color_event, 'important');
        }
        if(validateColors(bg_color_event)){ 
            $(".containerDate .pickmeup .pmu-instance div[class*='__event_calendar']").css("background-color", bg_color_event, 'important');
        }
        if(validateColors(bg_color_selected)){ 
            $(".containerDate .pickmeup .pmu-instance .pmu-selected").css("background-color", bg_color_selected, 'important');
        }
        if(validateColors(bg_color_today)){
            $(".containerDate .pickmeup .pmu-instance .pmu-today").css("background-color", bg_color_today, 'important');
        }
        if(validateColors(color_month)){
            $(".containerDate .pickmeup .pmu-instance .pmu-month").css("color", color_month, 'important'); 
        } 
    
    }

    function split_days(start, end){
        var arr_d = start.split('-');
        var array_range = [];
        var aux_date = new Date(arr_d[0],(arr_d[1] - 1),arr_d[2]);
        var aux_d;
        var c = 0;
        while(end != aux_d){
            c++;
            aux_date = new Date(aux_date.setDate(aux_date.getDate() + 1));
            aux_d = aux_date.getUTCFullYear() + "-" + ("0" + (aux_date.getUTCMonth() + 1 )).slice(-2) + '-' + ("0" + aux_date.getUTCDate()).slice(-2);
            array_range.push(aux_d);
            if(c > 150){
                console.log('Event error')
                break;
            }
        }
        return array_range; 
    }

    function fill_calendar(){
        $.each(global_events_json, function(){
            var start_aux, end_aux, title, body, url;
            start_aux = $(this)[0].start.split("T");
            end_aux = ($(this)[0].end != null) ? $(this)[0].end.split("T") : [null,null];
            title = $(this)[0].title;
            body = $(this)[0].body;
            url  = $(this)[0].url;
            if(null != end_aux){
                var continuos_days = split_days(start_aux[0], end_aux[0]);
                for (var i in continuos_days){
                    if (Array.isArray(array_dates_data[continuos_days[i]]) == false){
                        array_dates_data[continuos_days[i]] = [];
                    }
                    array_dates_data[continuos_days[i]]['start'] = start_aux[0];
                    array_dates_data[continuos_days[i]]['start_time'] = start_aux[1];
                    array_dates_data[continuos_days[i]]['end'] = end_aux[0];
                    array_dates_data[continuos_days[i]]['end_time'] = end_aux[1];
                    array_dates_data[continuos_days[i]]['title'] = title;
                    array_dates_data[continuos_days[i]]['body'] = body;
                    array_dates_data[continuos_days[i]]['url'] = url;
                }
            }
            if (Array.isArray(array_dates_data[start_aux[0]]) == false){
                array_dates_data[start_aux[0]] = [];
            }
            array_dates_data[start_aux[0]]['start'] = start_aux[0];
            array_dates_data[start_aux[0]]['start_time'] = start_aux[1];
            array_dates_data[start_aux[0]]['end'] = end_aux[0];
            array_dates_data[start_aux[0]]['end_time'] = end_aux[1];
            array_dates_data[start_aux[0]]['title'] = title;
            array_dates_data[start_aux[0]]['body'] = body;
            array_dates_data[start_aux[0]]['url'] = url;
        });
    }

    function init(){
        fill_calendar();
        pickmeup('.containerDate', {
            format	: 'Y-m-d',
            flat : true,
                date      : [
                  new Date
              ],
              mode      : 'single',
              first_day: week_first_day,
              locale: lang,
              calendars: num_cal,
              render: function(date){
                var date_calendar = date.getFullYear() + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + ("0" + (date.getDate())).slice(-2);
                var ev = eval(array_dates_data[date_calendar]);
                var dt = '';
                if(Array.isArray(ev)){
                    dt = '__event_calendar';                    
                }
                setCss();
                return { class_name: 'data_' + date_calendar + dt }
                },
               before_show: function(){
                   return setCss();
               },
            }); // close pickmeup call

            event_click();
            $(".containerDate").on('pickmeup-fill', function (e) {
                event_click();
                setCss();                
            });

            //$("div[class*='__event_calendar']").unbind("click");
            
             
              
    } // close init


    function event_click(){
        $("div[class*='__event_calendar']").each(function(){                    
            opt = {
                autoOpen: true,
                modal: true,
                title: 'detail', 
                close: function(event, ui){
                    $('.containerDate').focus();
                }
            };
        });
        $("div[class*='__event_calendar']").on("click", function(event){
            event.preventDefault;
            debug($(this).attr('class'));
            var aux_class = $(this).attr("class").split("__event_calendar"); 
            var data_day = array_dates_data[aux_class[0].split("data_")[1]];
            if(text_in_modal == 0){
                return window.location.href=data_day['url'];
            }
            var dl = $('.dialog').dialog(opt).dialog("open");
            dl.html(data_day['body']);
            dl.append('<p>' + text_initial_date + ': ' + data_day['start'] + ' ' + data_day['start_time'] + '</p>');
            if(data_day['end'] != null)
                dl.append('<p>' + text_end_date + ': ' + data_day['end'] + ' ' + data_day['end_time'] + '</p>');
            $('.ui-dialog-title').html(data_day['title']);
            setCss();
          });
    }

    

     
    Drupal.behaviors.calendar_events = {
        attach: function(context, settings){
            init();
            setCss();
        }
    }
})(jQuery, Drupal, drupalSettings); 

//init();
