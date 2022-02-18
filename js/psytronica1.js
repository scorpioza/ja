var PSY_LIVE = "./";

/*********************************
        Search - javascript
*********************************/

        
var PSY_SEARCH_DATA = "";
var SEARCH_TXT_SYMBOLS = 50;

$(document).ready(function(){

    $('#psy_search').click(function(){
        makeSearch();
    }).keypress(function(){
        makeSearch();
    }).keyup(function(){
        makeSearch();
    }).blur(function(ev){

    });

    $(document).click(function(ev){
        if(!$(ev.target).closest("#psy_search_results").length){
            $('#psy_search_results').html("").hide();
        }
    });

});

function makeSearch(){

         if($('#psy_search').val()){
        	$('#psy_search').addClass('opened');
       }else{
        	$('#psy_search').removeClass('opened');
       }
  
    if(!PSY_SEARCH_DATA){
        $.get(PSY_LIVE+"search/index.html", function(data, status){
            PSY_SEARCH_DATA = data;
            doSearch();
        }, "json");

    }else{
        doSearch();
    }
}

function doSearch(){
    var fragment = $('#psy_search').val().toLowerCase();
    if(!fragment) return;

    $('#psy_search_results').html("").show();

    var regex = /(<([^>]+)>)/ig ;

    var shtml= "";
    for(var i=0; i< PSY_SEARCH_DATA.length; i++){
            var dt = PSY_SEARCH_DATA[i];

            var txt = dt["text"].replace(regex, "").toLowerCase();

            if (dt["title"].toLowerCase().indexOf(fragment) != -1 || txt.indexOf(fragment) != -1) {

                    shtml+="<div class='psy_search_res'>";
                    
                     shtml+="<a target='_blank' href='"+PSY_LIVE+dt["url"]+".html'>"+dt["title"]+"</a>";
                    
                    if( txt.indexOf(fragment) != -1) {
                            txt = txt.substring(txt.indexOf(fragment)-SEARCH_TXT_SYMBOLS, 
                                        txt.indexOf(fragment)+SEARCH_TXT_SYMBOLS);
                            txt = txt.replace(fragment, "<b>"+fragment+"</b>");
                            shtml+="<div class='psy_search_content'>"+txt+"</div>";
                    }

                    shtml+="</div>";
            }
    }
    $('#psy_search_results').html(shtml);

}


/*********************************
        js - основной функционал
*********************************/

        
var PSY_MINI_WIDTH = 768;

$(document).ready(function(){

    setBodyWidth();
    psyArchiveToggle();
  	rightMobileMenu();
  	topMobileMenu();
  $(window).resize(function(){
    setBodyWidth();
  });
  
$(document).ready(function() {
	$(".fancybox-thumb").fancybox({
		prevEffect	: 'none',
		nextEffect	: 'none',
		helpers	: {
			title	: {
				type: 'outside'
			},
			thumbs	: {
				width	: 50,
				height	: 50
			},
            padding : 0
		}
	});
  $('[data-fancybox="images"]').fancybox({
    buttons : [ 
      'slideShow',
      'share',
      'zoom',
      'fullScreen',
      'close'
    ],
    thumbs : {
      autoStart : true
    }
  });  
  
});  
  
});

function setBodyWidth(){

       if($('body').width() <=PSY_MINI_WIDTH){
           $('body').addClass('body_mini');
       }else{
            $('body').removeClass('body_mini');
       }
   
}

function psyArchiveToggle(){

  $('.psy_archive a.toggle').click(function(){

    var arch_elems = $(this).closest(".psy_archive")
      .children(".psy_archive_elems");
    var el_open = arch_elems.children(".el_open").html();
    var el_closed = arch_elems.children(".el_closed").html();

    if($(this).children('span').hasClass("toggle-open")){

      $(this).parent().children("ul").children("li")
        .hide(400, function(){
      });
      $(this).children('span')
        .addClass("toggle-closed")
        .removeClass("toggle-open").html(el_closed);

    }else{

      $(this).parent().children("ul").children("li")
        .show(400, function(){
      });
      $(this).children('span')
        .removeClass("toggle-closed")
        .addClass("toggle-open").html(el_open);

    }

  });

}

function getRussianTerm(number, arr){
    number = parseInt(number)+"";
    var last_sym = number.substring(number.length-1);
    var last_2sym = number.substring(number.length-2);

    if( last_2sym>'10' && last_2sym<'15'){
        return arr[2];
    }else if(last_sym=='1'){
        return arr[0];
    }else if(last_sym>'1' && last_sym<'5'){
        return arr[1];
    }else return arr[2];

}
function rightMobileMenu(){
  	$("#ja_tags").click(function(){
       if($('body').hasClass('body_mini')){ $("#psytronica_right_column").removeClass("active_menu").removeClass("active_archive").toggleClass("active_tags");
  		}
  });
	$("#ja_menu").click(function(){
     if($('body').hasClass('body_mini')){ $("#psytronica_right_column").removeClass("active_tags").removeClass("active_archive").toggleClass("active_menu");
                                        }
  });
	$("#ja_archive").click(function(){
     if($('body').hasClass('body_mini')){ $("#psytronica_right_column").removeClass("active_tags").removeClass("active_menu").toggleClass("active_archive");
                                        }
  });  
}

function topMobileMenu(){

  	if(!$("#psytronica_menu .psy_active")[0]){
    	$("#psytronica_menu ul").prepend("<li class='li_selected li_aux'><span class='psy_active'>Меню</span></li>");
    }else{

    	$("#psytronica_menu .psy_active").parent().addClass("li_selected");

    }
    $("#psytronica_menu .psy_active").click(function(e){
        if($('body').hasClass('body_mini')){
            e.preventDefault();
            $('#psytronica_menu').toggleClass("open");
        }
    });

}



