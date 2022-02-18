/**
* @id           $Id$
* @author       Chandra Maya (coffeelixir@gmail.com)
* @package      Chandra Memo
* @license      GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/

// Сервер на локальной машине: 
// python3 -m http.server 8000

// кол-во элементов на страницу
var ITEMS_PER_PAGE=10;
// кол-во ячеек, на которое смещена сверху первая запись в таблице Excel 
var ITEM_OFFSET_IN_TABLE=1;
// название кнопки "читать далее"
var READMORE_TEXT = 'Далее'
// название кнопки "читать далее" после того, как контент был открыт
var READMORE_HIDE_TEXT = 'Спрятать содержимое'
// отображается в выпадающем списке, если год не выбран
var YEAR_LABEL="- год -";
// отображается в выпадающем списке, если месяц не выбран
var MON_LABEL ="- мес -";
// файлы базы
var DBASE = ["base", "pictures"];
// отображаемые названия фильтров в верхней области фильтров
var FILTER_NAMES={"tag": "тег", "cat": "категория", "more": "дополнение"};

// если поле пустое, отображение происходит без залогинивания
var LOGIN_MAIL="coffeelixir@gmail.com";

// в отладочном режиме выводятся сообщения в консоль
var DEBUG=true;

var FILTER_TYPE=""; //tag, cat, more, ""
var FILTER_VALUE="";
var FILTER_SEARCH="";
var FILTER_YEAR="";
var FILTER_MON="";

var NOT_SORT=false;

// кол-во элементов в таблице excel
var ITEMS_COUNT = 0;
// сколько раз была нажата readmore - отвечает за то, сколько материалов отобразить
var READMORE_COUNT=0;

var CALENDAR_DATA={};

// Количество применений фильтра
var FILTER_COUNT=0;

// Client ID and API key from the Developer Console
var CLIENT_ID = '735889146943-tkod6am845b83ocgnrk4fgvpb1cpqjk7.apps.googleusercontent.com';
var API_KEY = 'AIzaSyAgvt8XKynPC7qH8LSJgx_rwC2i1xOD0Dc';

// Array of API discovery doc URLs for APIs used by the quickstart
var DISCOVERY_DOCS = ["https://sheets.googleapis.com/$discovery/rest?version=v4"];

// Authorization scopes required by the API; multiple scopes can be
// included, separated by spaces.
var SCOPES = "https://www.googleapis.com/auth/spreadsheets.readonly";

var COLORS= {
    "O": "#E91E63",
    "Z": "#673AB7",
    "F": "#8BC34A"
};

var IDS_LINKS = {};

// Toggle the side navigation
jQuery("#sidebarToggle").on("click", function(e) {
    e.preventDefault();
    jQuery("body").toggleClass("sb-sidenav-toggled");
});

jQuery(document).on('keypress',function(e) {
    if(e.which == 13 && jQuery("#memo-search-input").is(":focus")) {
        memoSearch();
        e.preventDefault();
        return false;
    }
});


jQuery("#memoDaynight").on("click", function(e) {
    e.preventDefault();
    jQuery("body").toggleClass("memo-night");
    createCookie('memo-daynight', (jQuery("body").hasClass("memo-night"))? 1 : 0, 1000);
});



function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}

function memoOnLoad(){
    jQuery("body").removeClass("loading");
    if(readCookie('memo-daynight')=="1"){
        jQuery("body").addClass("memo-night");
    }
}

/**
*  On load, called to load the auth2 library and API client library.
*/
function handleClientLoad() {
    gapi.load('client:auth2', initClient);
}

/**
*  Initializes the API client library and sets up sign-in state
*  listeners.
*/

function initClient() {
    gapi.client.init({
      apiKey: API_KEY,
      clientId: CLIENT_ID,
      discoveryDocs: DISCOVERY_DOCS,
      scope: SCOPES
    }).then(function () {

      if(!LOGIN_MAIL)
        main();
      // Listen for sign-in state changes.
      gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

      // Handle the initial sign-in state.

      updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
      jQuery("#userLogin")[0].onclick = handleAuthClick;
      jQuery("#userLogout")[0].onclick = handleSignoutClick;
    }, function(error) {
      memoLog(JSON.stringify(error, null, 2));
    });
}

/**
*  Called when the signed in status changes, to update the UI
*  appropriately. After a sign-in, the API is called.
*/

function updateSigninStatus(isSignedIn) {

    if(LOGIN_MAIL){
        jQuery('#memo-main').removeClass('login-incorrect');
        jQuery('#memo-main').removeClass('login-guest');
    }

    if (isSignedIn) {

      var profile = gapi.auth2.getAuthInstance().currentUser.get().getBasicProfile();
      //memoLog('Email: ' + profile.getEmail());
      var img="<img id='memo-avatar' class=\"rounded-circle\" src='"+profile.getImageUrl()+"' /> ";
      jQuery('#memo-logged-in').addClass('auth');
      jQuery('#memo-logged-in').html(img + profile.getName());


      jQuery("#userLogged").show();
      jQuery("#userNotLogged").hide();

      if(LOGIN_MAIL){
        if(profile.getEmail()==LOGIN_MAIL){
            main();
        }else{
            jQuery('#memo-main').addClass('login-incorrect');
        }

      }
    } else {
      jQuery("#userLogged").hide();
      jQuery("#userNotLogged").show();
      jQuery('#memo-logged-in').removeClass('auth');
      jQuery('#memo-logged-in').html("Guest");

      if(LOGIN_MAIL)
        jQuery('#memo-main').addClass('login-guest');

    }
}

/**
*  Sign in the user upon button click.
*/
function handleAuthClick(event) {
    gapi.auth2.getAuthInstance().signIn();
}

/**
*  Sign out the user upon button click.
*/
function handleSignoutClick(event) {
    gapi.auth2.getAuthInstance().signOut();
}

function memoLog(message) {
    if(DEBUG)
        console.log(message);
}


var MAIN_LOADED=false;
function main() {

    if(MAIN_LOADED)
        return;
    MAIN_LOADED=true;

    var current_count = FILTER_COUNT;

    var first = ITEM_OFFSET_IN_TABLE +1

    jQuery.get( "memo/db/pictures.csv", function( pictures_raw ) {
        jQuery.get( "memo/db/base.csv", function( data_raw ) {

            if(FILTER_COUNT > current_count)
            return;

            var rows = data_raw.split("\n");
            if (rows.length > 0) {
                
                var tags_obj = {};
                var cats_obj = {};
                var more_obj = {};

                ITEMS_COUNT = rows.length;
                var items = doSort(rows, pictures_raw);

                for (i = 0; i < items.length; i++) {
                var row = items[i];

                // сохраняем теги элементов
                var tags = row.tags.split(" ");
                for(var j=0; j<tags.length; j++){
                    if(!tags[j])
                        continue;
                    if(!(tags[j] in tags_obj)){
                        tags_obj[tags[j]]=1;
                    }else{
                        tags_obj[tags[j]]++;
                    }
                }

                // сохраняем категории элементов
                var cats = row.cats.split(",");
                for(var j=0; j<cats.length; j++){
                    var cat = jQuery.trim(cats[j]);
                    if(!cat)
                        continue;
                    if(!(cat in cats_obj)){
                        cats_obj[cat]=1;
                    }else{
                        cats_obj[cat]++;
                    }
                }

                // сохраняем дополнения
                var more = row.mores.split(",");
                for(var j=0; j<more.length; j++){
                    var mo = jQuery.trim(more[j]);
                    if(!mo)
                        continue;
                    if(!(mo in more_obj)){
                        more_obj[mo]=1;
                    }else{
                        more_obj[mo]++;
                    }
                }

                // сохраняем даты
                var cal = row.date.split(".");
                if(cal.length==3){
                    if(!(cal[2] in CALENDAR_DATA))
                        CALENDAR_DATA[cal[2]]={};
                    if(!(cal[1] in CALENDAR_DATA[cal[2]]))
                        CALENDAR_DATA[cal[2]][cal[1]]=0;

                    CALENDAR_DATA[cal[2]][cal[1]]++;
                }

                // создаем заготовку с первичными данными
                createRow(row);

                }
                createTagsArea(tags_obj);
                createCatsArea(cats_obj);
                createMoreArea(more_obj);
                createCalendarArea();
                createStatArea(tags_obj);
                
                showNew();
            } else {
                memoLog('No data found.');
            }
        });
    });

}

function doSort(rows, pictures_raw){

    var pictures = pictures_raw.split("\n");
    var pic = {};
    for(var i=0; i<pictures.length; i++){
        var img = pictures[i].split(";");
        pic[img[0]]=img[1];
    }

    var items=[];
    for (var i=0; i<rows.length; i++) {
        var cols = rows[i].split(";");
        var obj = {};
        obj.id=i+1;

        obj.dbid = cols[0];
        var date = cols[1].split("-");
        obj.date = date[2]+"."+date[1]+"."+date[0];
        obj.path = date[0]+"/"+date[1]+"-"+date[2];
        obj.name = cols[2];
        obj.tags = cols[4];
        obj.img = pic[obj.dbid];

        obj.links = cols[5];

        IDS_LINKS[obj.dbid]=[obj.name, obj.date];

        obj.color = "";
        var info = cols[3];
        var cats = [];
        if(info.indexOf("R") !== -1)
            cats.push("Музыка");
        if(info.indexOf("S") !== -1)
            cats.push("Аюрведа");
        if(info.indexOf("F") !== -1){
            cats.push("Мантра");
            obj.color = COLORS["F"];
        }

        obj.cats = cats.join(",")

        var mores = [];
        if(info.indexOf("O") !== -1){
            mores.push("Чай");
            obj.color = COLORS["O"];
        }
        if(info.indexOf("Z") !== -1){
            mores.push("Алхимия");
            obj.color = COLORS["Z"];
        }
            
        obj.mores = mores.join(",");
        
        items.push(obj);
    }

    if(NOT_SORT)
        return items;

    var sorted = date_sort(items);
    return sorted;

}


function date_sort(items) { 
    var i = 0, j; 
    while (i < items.length) { 
        j = i + 1; 
        while (j < items.length) { 
            if (toDate(items[j].date) > toDate(items[i].date)) { 
                var temp = items[i]; 
                items[i] = items[j]; 
                items[j] = temp; 
            } 
            j++; 
        } 
        i++; 
    } 
    return items;
} 

function toDate(str){
    var d = str.split(".");
    if(d.length<3)
        return "";
    return d[2]+"."+d[1]+"."+d[0];
    /*if(d.length<3)
        return new Date();
    return new Date(d[2], d[1], d[0], 0, 0, 0, 0);*/
}


function createTagsArea(tags_obj){

    var tags_html="";

    var sortable = [];
    for (var tag in tags_obj) {
        sortable.push([tag, tags_obj[tag]]);
    }

    sortable.sort(function(a, b) {
        return b[1] - a[1];
    });


    for (var i in sortable) {
        tags_html +='<a class="memo-tag badge badge-warning" onclick="memoFilter(\''+sortable[i][0]+
        '\', \'tag\')" href="javascript:void(null)" >'+sortable[i][0]
        +'&nbsp;<span class="badge badge-dark">'+sortable[i][1]+'</span></a> ';
    }
    jQuery("#memo-tags-area").html(tags_html);
}

function createCatsArea(cats_obj){
    var icons = ['fa-tachometer-alt', 'fa-chart-area', 'fa-table'];
    var cats_html="";

    var inum = 0;
    for (var cat in cats_obj) {

        cats_html +='<a class="nav-link" href="javascript:void(null)" \
            onclick="memoFilter(\''+cat+ '\', \'cat\')">\
            <div class="sb-nav-link-icon"><i class="fas '+icons[inum]+'"></i></div>\
            '+cat+'&nbsp;<span class="badge badge-warning">'+cats_obj[cat]+'</span>\
        </a>';

        inum = (inum==icons.length-1)? 0 : inum+1;

    }
    jQuery("#memo-cats-area").html(cats_html);
}


function createMoreArea(more_obj){
    var icons = ['fa-star', 'fa-book-open', 'fa-table'];
    var more_html="";

    var inum = 0;
    for (var more in more_obj) {

        more_html +='<a class="nav-link" href="javascript:void(null)" \
                onclick="memoFilter(\''+more+ '\', \'more\')">\
            <div class="sb-nav-link-icon"><i class="fas '+icons[inum]+'"></i></div>\
            '+more+'&nbsp;<span class="badge badge-warning">'+more_obj[more]+'</span>\
        </a>';

        inum = (inum==icons.length-1)? 0 : inum+1;

    }
    jQuery("#memo-more-area").html(more_html);
}

function createStatArea(tags_obj){
    jQuery('#memo-entry-count').text(ITEMS_COUNT);
    jQuery('#memo-tags-count').text(Object.keys(tags_obj).length);
}

function createCalendarArea(){

    jQuery('#memo-calendar-year-btn').text(YEAR_LABEL);
    jQuery('#memo-calendar-mon-btn').text(MON_LABEL);

    jQuery('#memo-calendar-year-list').append(
        '<a class="dropdown-item" onclick="changeDate(\'\', \'year\')" \
            href="javascript:void(null)">'+YEAR_LABEL+'</a>');

    for (var year in CALENDAR_DATA) {
      jQuery('#memo-calendar-year-list').append(
        '<a class="dropdown-item" onclick="changeDate(\''+year+'\', \'year\')" \
            href="javascript:void(null)">'+year+'</a>');
    }
    jQuery('#memo-calendar-mon').hide();
}

function changeDate(value, type){
    memoLog("changeDate value="+value+", type="+type);

    var txt = (value)? value : ((type=="year")? YEAR_LABEL : MON_LABEL);

    jQuery('#memo-calendar-'+type+'-btn').text(txt);
    if(type=="mon")
        return;

    jQuery('#memo-calendar-mon-btn').text(MON_LABEL);

    var year = value;

    jQuery('#memo-calendar-mon-list').html(
    '<a class="dropdown-item" onclick="changeDate(\'\', \'mon\')" \
        href="javascript:void(null)">'+MON_LABEL+'</a>');

    if(value){
        jQuery('#memo-calendar-mon').show();

        for(mon in CALENDAR_DATA[year]){
            jQuery('#memo-calendar-mon-list').append(
                '<a class="dropdown-item" onclick="changeDate(\''+mon+'\', \'mon\')" \
                href="javascript:void(null)">'+mon+' <span class="badge badge-warning">'+
                CALENDAR_DATA[year][mon]+'</span></a>');
        }

    }else{
        jQuery('#memo-calendar-mon').hide();
    }
    
}

function selectDate(value, type){
    memoLog("selectDate value="+value+", type="+type);
    if(type=="year"){
        FILTER_YEAR=(value!=YEAR_LABEL)? value : "";
        FILTER_MON="";
    }else{
        FILTER_MON=(value!=MON_LABEL)? value : "";
        if(FILTER_MON){
            var year = jQuery("#memo-calendar-year-btn").text();
            FILTER_YEAR=(year!=YEAR_LABEL)? year : "";
        }
    }        
    setFilter();
}


function memoFilter(item, type){
    FILTER_TYPE=type;
    FILTER_VALUE=item;
    setFilter();
}

function memoSearch(){
    FILTER_SEARCH=jQuery.trim(jQuery('#memo-search-input').val());
    setFilter();
}

function memoSearchEntry(name, date){
    clearFilter();
    FILTER_SEARCH=jQuery.trim(name);
    var d = date.split('.');
    FILTER_YEAR=d[2];
    FILTER_MON=d[1];
    setFilter();
}

function setFilter(){
    FILTER_COUNT++;
    READMORE_COUNT=0;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    jQuery('.memo-row').addClass('hidden').removeClass('xexclude');

    if(FILTER_TYPE){
        jQuery("#memo-filters").addClass("factive");

        var type=(FILTER_TYPE in FILTER_NAMES)? FILTER_NAMES[FILTER_TYPE] : FILTER_TYPE;

        jQuery("#memo-filter-type").text(type);
        jQuery("#memo-filter-value").text(FILTER_VALUE);
    }else{
        jQuery("#memo-filters").removeClass("factive");
        jQuery("#memo-filter-type").text("");
        jQuery("#memo-filter-value").text("");
    }

    if(FILTER_SEARCH){
        jQuery("#memo-filters").addClass("sactive");
        jQuery("#memo-search-value").text(FILTER_SEARCH);

    }else{
        jQuery("#memo-filters").removeClass("sactive");
        jQuery("#memo-search-value").text("");
    }

    if(FILTER_YEAR){
        jQuery("#memo-filters").addClass("dactive");
        jQuery("#memo-date-year-value").text(FILTER_YEAR);
        if(FILTER_MON){
            jQuery("#memo-date-mon-value").text(FILTER_MON);
        }else{
            jQuery("#memo-date-mon-value").text("");
        }
    }else{
        jQuery("#memo-filters").removeClass("dactive");
        jQuery("#memo-date-year-value").text("");
        jQuery("#memo-date-mon-value").text("");
    }
    showNew();

}

function clearFilter(){
    FILTER_TYPE="";
    FILTER_VALUE="";
    FILTER_SEARCH="";
    FILTER_YEAR="";
    FILTER_MON="";
    setFilter();
}

function checkFilter(el){
    return checkFil(el, FILTER_TYPE, FILTER_VALUE, false)
        && checkDate(el);
}

function checkFil(el, type, value, icase){

    memoLog("checkFil type="+type+", value="+value)
    switch(type) {
      case '': 
        return true;

      case 'tag':  
        var tags_row=jQuery(el).attr('tags');
        if(!tags_row)
            return false;
        var tags = tags_row.split(" ");
        for(var i=0; i<tags.length; i++){
            var tag = (icase)? tags[i].toLowerCase() : tags[i];
            //memoLog("tag="+tag, "value="+value);
            if(tag==value)
                return true;
        }
        return false;
      case 'cat':
        var cats_row=jQuery(el).attr('cats');
        if(!cats_row)
            return false;
        var cats = cats_row.split(",");
        for(var i=0; i<cats.length; i++){
            var cat = (icase)? jQuery.trim(cats[i]).toLowerCase() : jQuery.trim(cats[i]);
            //memoLog("cat='"+cat+"'", "value='"+value+"'");
            if(cat==value){
                memoLog("**** FOUND cat='"+cat+"', row='"+jQuery(el).find('.memo-name').text()+"'");
                return true;
            }
        }
        return false;

      case 'more':
        var more_row=jQuery(el).attr('mores');
        if(!more_row)
            return false;
        var mores = more_row.split(",");
        for(var i=0; i<mores.length; i++){
            var more = (icase)? jQuery.trim(mores[i]).toLowerCase() : jQuery.trim(mores[i]);
            memoLog("more='"+more+"'", "value='"+value+"'");
            if(more==value)
                return true;
        }
        return false;

    }
}

function checkDate(el){
    if(!FILTER_YEAR)
        return true;

    var memo_date=jQuery(el).find(".memo-date").first();
    if(!memo_date)
        return false;

    var cal = memo_date.text().split(".");
    if(cal.length!=3)
        return false;

    if(cal[2]!=FILTER_YEAR)
        return false;

    if(!FILTER_MON)
        return true;

    return cal[1]==FILTER_MON;

}

function checkSearch(el){

    var search = FILTER_SEARCH.toLowerCase();

    if(!search)
        return true;
    if(checkFil(el, "tag", search, true))
        return true;
    if(checkFil(el, "cat", search, true))
        return true;
    if(checkFil(el, "more", search, true))
        return true;

    var memo_name=jQuery(el).find(".memo-name").first();
    if(memo_name){
        memoLog("MEMO name: "+memo_name.html());
        var search_data = memo_name.text().replace(regex, "").toLowerCase();
        if((search_data.indexOf(search) != -1))
            return true;
    }

    var memo_content=jQuery(el).find(".memo-content").first();
    if(!memo_content)
        return false;

    var search_data = memo_content.text();

    var regex = /(<([^>]+)>)/ig ;

    search_data = search_data.replace(regex, "").toLowerCase();
    if(!search_data) return false;

    return (search_data.indexOf(search) != -1) 

}


function showMore(){

    READMORE_COUNT++;
    showNew();
}

function showNew(){

    var els = jQuery('.memo-row').not('.xexclude');
    var els_hidden = jQuery('.memo-row.hidden').not('.xexclude');

    memoLog("!! showNew hiddens: "+els_hidden.length);


    if(els_hidden.length==0){
        jQuery('#memo-readmore').hide();
        toggleProgress(false);
        return;
    }
    

    var visible_count = els.length - els_hidden.length;
    if(visible_count>=(READMORE_COUNT+1)*ITEMS_PER_PAGE){
        toggleProgress(false);
        jQuery('#memo-readmore').show();
        return;
    }

    for(var i=0; i< els_hidden.length; i++){
        if(checkFilter(els_hidden[i])){
            memoLog("checkFilter, i="+i);
            showElement(els_hidden[i]);
            return;
        }else{
            jQuery(els_hidden[i]).addClass('xexclude');
        }
    }
    showNew();
}

function showElement(el){

    memoLog("showElement, id="+el.id);

    if(jQuery(el).hasClass('xempty')){
        loadItem(el);
    }else{
        if(checkSearch(el)){
            memoLog("+++++++++ MAKE VIS, id="+el.id);
            jQuery(el).removeClass('hidden');
        }
        else
            jQuery(el).addClass('xexclude');
        showNew();
    }
}  

function loadItem(el){

    var current_count = FILTER_COUNT;

    toggleProgress(true);

    jQuery('#memo-readmore').hide();


    var mname=jQuery(el).find(".memo-name").first().text();
    var mpath = jQuery(el).attr("path");
    
    jQuery.get( "memo/"+mpath+" "+mname+".txt", function( txt ) {

      if(FILTER_COUNT > current_count)
        return;

        fillRow(el, txt, current_count);
      

    });
}

function fillRow(el, txt, current_count){

    if(jQuery(el).hasClass("xempty")){
        jQuery(el).removeClass("xempty");

        var row = txt.replace(/\n/g, "<br />").split("<br />&&&<br />");

        var content = row[0];
        if(row[1]){
            content += '<div class="memo-comment alert alert-success" role="alert">'+
            row[1]+'</div>';
        }

        jQuery(el).find('.memo-content').prepend(content);

    }

    if(checkSearch(el)){
        memoLog("+++++++++ MAKE VIS, id="+el.id);
        jQuery(el).removeClass('hidden');
    }else{
        jQuery(el).addClass('xexclude');
    }
    if(current_count==FILTER_COUNT)
        showNew();
    

}

function createRow(row){

    var tags_html = "";
    var num = row.id;

    var tags = row.tags.split(" ");
    for(var i=0; i<tags.length; i++){
        tags_html += '<a class="badge badge-pill badge-warning" href="javascript:void(null)" \
         onclick="memoFilter(\''+tags[i]+'\', \'tag\')">'+tags[i]+'</a> ';
    }
    if(tags_html){
        tags_html= '<div class="memo-tags"><div class="memo-tags-inner">'+tags_html+'</div>\
        <div style="clear:both"></div></div>';
    }

    var cats_html = "";
    var cats = row.cats.split(",");
    for(var i=0; i<cats.length; i++){
        if(i>0)
            cats_html +=", ";
        var cat = jQuery.trim(cats[i]);
        cats_html += '<a onclick="memoFilter( \''+cat+'\', \'cat\')" \
            href="javascript:void(null)">'+cat+'</a>';
    }

    if(cats_html){
        cats_html = '<span class="memo-cats">Категории: <span class="memo-cats-inner">'
        +cats_html+'</span></span>';
    }
    var more_html = "";
    var mores = row.mores.split(",");

    for(var i=0; i<mores.length; i++){
        if(i>0)
            more_html +=" ";
        var more = jQuery.trim(mores[i]);
        more_html += '<a class="memo-mores-lbl badge badge-warning" href="javascript:void(null)" \
        onclick="memoFilter(\''+more+'\', \'more\')" >'+more+'</a>';
    }

    if(more_html){
        more_html = '<span class="memo-mores">'+more_html+'</span>';
    }

    var copy_html= '<a class="memo-copy" onclick="copyToClipboard(this)" href="javascript:void(null)">'+
    '<i class="fas fa-save"></i></a>';

    var links_html = "";

    var lnks = row.links.split(" ");

    if(lnks.length){

        for(var i = 0; i< lnks.length; i++){
            if(lnks[i] in IDS_LINKS){
                if(i>0)
                    links_html+=", ";
                var mdate = IDS_LINKS[lnks[i]][1];
                var mname = IDS_LINKS[lnks[i]][0];
                links_html+="<a href='javascript:void(null)' onclick='memoSearchEntry(\""+mname+
                        "\", \""+mdate+"\")'>"+mname+"</a>";
            }
        }
        if(links_html)
            links_html = '<div class="memo-comment alert alert-primary" role="alert">'+
                links_html +'</div>';
    }

    var html = 
      '<div id="memo-row-'+num+'" num="'+num+'" class="row memo-row hidden xempty" tags="'+row.tags+
            '" cats="'+row.cats+ '" mores="'+row.mores+'" path="'+row.path+'">\
            <div class="col-md-12">\
              <div id="memo-card-'+num+
                    '" class="card flex-md-row mb-4 box-shadow h-md-250">\
                <div id="memo-card-body-'+num+
                    '" class="card-body d-flex flex-column">\
                  <h3 class="memo-name mb-0 text-dark">'+row.name+'</h3>\
                  <div class="memo-subinfo alert alert-warning" role="alert">\
                    <i class="fas fa-table mr-1"></i><span class="memo-date">'+row.date+"</span>"
                        +copy_html+more_html+cats_html+'</div>'+tags_html+'\
                  <div id="memo-content-'+num+'" class="memo-content card-text">'+links_html+'</div>\
                  <div class="readmore_wrapper"><button type="button" id="memo-readmore-'+num+
                  '" class="btn btn-warning" onclick="toggleShow(this)">\
                  '+READMORE_TEXT+'</button></div>\
                </div>\
              </div>\
            </div>\
        </div>';
    jQuery('#memo_wrapper').append(html);

    if(row.img){
        var img = '<img class="memo-img rounded" '
        +' src='+row.img+' />';
        jQuery('#memo-card-'+num).append(img);
        setTimeout( function(){
            jQuery('#memo-card-'+num).addClass('with-img');
        }, 2000 );
    }

    // цвет
    if(row.color){
        jQuery('#memo-card-'+num).css('border-left', '3px solid '+row.color);
    }
    

}

function toggleProgress(wait){
    if(wait){
        jQuery("body").addClass("in-progress");
    }else{
        jQuery("body").removeClass("in-progress");
    }
}

function toggleShow(self){
    var content = jQuery(self).parent().parent().find('.memo-content');
    content.toggleClass('active');
    var txt = (content.hasClass('active'))? READMORE_HIDE_TEXT : READMORE_TEXT;
    self.textContent = txt;
}


function copyToClipboard(el) {

    var icase = true;

    var row = jQuery(el).closest('.memo-row').first();
    var tagz = [];

    var cats_row=row.attr('cats');
    if(cats_row){
        var cats = cats_row.split(",");
        for(var i=0; i<cats.length; i++){
            var cat = (icase)? jQuery.trim(cats[i]).toLowerCase() : jQuery.trim(cats[i]);
            tagz.push(cat);
        }
    }

    var more_row=row.attr('mores');
    if(more_row){
        var mores = more_row.split(",");
        for(var i=0; i<mores.length; i++){
            var more = (icase)? jQuery.trim(mores[i]).toLowerCase() : jQuery.trim(mores[i]);
            tagz.push(more);
        }    
    }

    var tags_row=row.attr('tags');
    if(tags_row){
        var tags = tags_row.split(" ");
        for(var i=0; i<tags.length; i++){
            var tag = (icase)? tags[i].toLowerCase() : tags[i];
            tagz.push(tag.replace(/-/gi, ""));
        }
    }

    var str = '★ '+row.find('.memo-name').text()+"  ★ ("+
        row.find('.memo-date').text()+")\n";

    for(var i=0; i<tagz.length; i++){
        str += "#"+tagz[i]+"@zeidmare ";
    }
    str += "\n"+row.find('.memo-content').html()
        .replace(/<br\s*[\/]?>/gi, "\n").replace(/<\/div>/gi, "</div>\n")
        .replace(/(<([^>]+)>)/ig,"")
        .replace(/&lt;/ig,"<").replace(/&gt;/ig,">").replace(/&nbsp;/ig," ")
        .replace(/\n\n/ig,"\n");





    memoLog(str);

    const elem = document.createElement('textarea');
    elem.value = str;
    document.body.appendChild(elem);
    elem.select();
    document.execCommand('copy');
    document.body.removeChild(elem);
}

function calcPrice(self){
    try {
       $('#sendPrice').val(parseInt(self.value)*15);
    }
    catch (e) {
        $('#sendPrice').val('');
    }

}