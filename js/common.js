/*======================= Accordion js function =======================*/
 /* $(document).on('click', '.accordion dl dt', function () {
    var trigger = $(this);
    var target = trigger.next('dd');
    if (target.css('display') == 'none')
    {
      trigger.parent('dl').parent('.accordion').children('dl').removeClass('active')
      trigger.parent('dl').parent('.accordion').children('dl').children('dd').slideUp();
      target.slideDown(function(){
          var elemPos = $(this).offset(); 
          if($(window).width() < 768)
          { 
            $('body,html').animate({ scrollTop: elemPos.top-60 }, 1000)
          }
          else
          {
            $('body,html').animate({ scrollTop: elemPos.top-60 }, 1000)
          }
      });

      trigger.parent('dl').addClass('active');
    }
    else
    {
      trigger.parent('dl').parent('.accordion').children('dl').removeClass('active')
      trigger.parent('dl').parent('.accordion').children('dl').children('dd').slideUp();
    }
  }); */
/*======================= Accordion js function =======================*/
$(document).on('click', '.navbar-toggle', function () {
  if($(this).hasClass('opened'))
  {
    $(this).removeClass('opened')
    $('.navbar-collapse, .mob-nav').slideUp();
  }
  else
  {
    $(this).addClass('opened');
    $('.navbar-collapse, .mob-nav').slideDown();
  }
});

$( document ).ready(function() {
    var pageFullUrl =  document.URL;
   // var pageFullUrl = "http://localhost/GainBitCoin/view/Withdrawals.php";
    pageFullUrl = pageFullUrl.toLowerCase();
    pageUrl = pageFullUrl.split("/");
    if(typeof(pageUrl) != "undefined" && pageUrl.length > 0){
        pageUrl = pageUrl[pageUrl.length-1];
        if(typeof(pageUrl) != "undefined" && pageUrl.trim() != ""){
          pageUrl = pageUrl.split("?")[0];
  		if(pageUrl=='kitadd' || pageUrl=='orderhistory')
  		{
  		
  			 $('#mypurchase').addClass("active"); 
  		}
  		else if(pageUrl=='reqrefund' || pageUrl=='generatereq') 
  		{
  			
  			$('#refundtab').addClass("active"); 
  		}
  		else if(pageUrl=='dailyearning' || pageUrl=='directearning' || pageUrl=='binaryearning' ||  pageUrl=='businessdetails')
  		{
  			$('#outputtab').addClass("active"); 
  		}
  		else if(pageUrl=='withdrawals' || pageUrl=='withdrawalstatus')
  		{
  			
  			$('#withdrwaltab').addClass("active"); 
  		}
  		else if(pageUrl=='binarynetwork' || pageUrl=='binraynetwrk') 
  		{
  			
  			$('#networktab').addClass("active"); 
  		}
  		else if(pageUrl=='servicerequest' || pageUrl=='servicerequestlist' || pageUrl=='faq' || pageUrl=='faq') 
  		{
  			
  			$('#helptab').addClass("active"); 
  		}
          else if(typeof(pageUrl) != "undefined" && pageUrl.trim() != ""){
              $("#side-menu a").each(function(){
                  var href= $(this).attr("href");
                  if(typeof(href)!="undefined"){
                    href = href.toLowerCase();
                    if(href.trim() == pageUrl){
                        $(this).addClass("active"); 
                    }
                  }
              });
           }
        }
      }

$('.tabNav li').each(function () {
  var tabContent = $(this).html();
  var relation = $(this).find('a').attr('rel')
  var resultCnt =  $(this).parents('.tabNav').next('.tabResult');
  resultCnt.children('div#'+relation).prepend('<div class="mobile-menu">'+ tabContent +'</div>')
})

/*script for mobile navigation */
$(document).on('click','.mobile-menu',function(){
  if($(this).next('.content').css('display') == 'none')
  {
    $('.tabResult .tabBx .content').slideUp();
    $(this).next('.content').slideDown(function(){
          var elemPos = $(this).offset(); 
          if($(window).width() < 768)
          { 
            $('body,html').animate({ scrollTop: elemPos.top-42}, 1000)
          }
          else
          {
            $('body,html').animate({ scrollTop: elemPos.top-42}, 1000)
          }
      });
  }
  
})
/*script for desktop navigation */
$('.tabNav li a').click(function(){
  var relation = $(this).attr('rel')
  var tabNavigation = $(this).parents('.tabNav')
  var resultCnt =  $(this).parents('.tabNav').next('.tabResult');
  
  tabNavigation.children().find('a').removeClass('active')
  tabNavigation.children().find('li').removeClass('activeli')
  $(this).addClass('active');
  $(this).parents('li').addClass('activeli')
  
  if(resultCnt.children('div#'+relation).css('display') == 'none')
  {
    resultCnt.children('div').slideUp();
    resultCnt.children('div#'+relation).slideDown();
  }
  
})

});

/*$(document).on({
	  mouseenter: function(){        
	      $(this).find('.sub-menu').stop().slideDown('slow')
	  },
	  mouseleave: function(){
	    $(this).find('.sub-menu').stop().slideUp('slow')
	  }
	}, '.has-sub-menu');*/

$(document).on('click','.has-sub-menu', function() {
    //alert("in");
    if($('.sub-menu',this).css('display') == 'block')
      {	
    	/*$("p").css({"background-color": "yellow", "font-size": "200%"});*/
    	/*$(this).find('a').css('background', 'url("../images/drop-down.png") no-repeat scroll center center rgba(0, 0, 0, 0)').removeClass(".drop-down");*/
    	$(this).find('p').addClass("down").removeClass("up");
    	/*$(this).find('a').css('background', 'url("../images/drop-down.png") no-repeat scroll center center rgba(0, 0, 0, 0)');*/
    	$('.sub-menu').hide(500);
    	$('.sub-menu').hide(500);
      }
     if($('.sub-menu',this).css('display') == 'none')
      {
    	 
    	 //$('.has-sub-menu a').css('background', 'url("../images/slide_up.png") no-repeat scroll center center rgba(0, 0, 0, 0)');
    	/* $(this).find('a').css('background', 'url("../images/slide_up.png") no-repeat scroll center center rgba(0, 0, 0, 0)');*/
     	//$(this).find('a').removeClass("drop-down");
    	 $(".has-sub-menu p").addClass("down").removeClass("up");

     	$(this).find('p').removeClass("down").addClass("up");


		$('.sub-menu').hide(500);
        $(this).find('.sub-menu').show(500);
      }	
});

$(document).on('click','#click_advance', function() {
    // alert("in");
	$('#contractid').removeClass('active');
	$("#contractlist").css("display","none"); 
	$("#contractlist1").css("display","none");
    if($('#select_kit').css('display') == 'block')
    {	
        //alert("hide div");
        $('#select_kit').slideToggle('swing');
        $('#kitselectid').removeClass('active');
    }
    if($('#select_kit').css('display') == 'none')
    {
		//alert("show div");
		$('#select_kit').slideToggle('swing');
		$('#kitselectid').addClass('active');		
	}	
 });


$("#notification").click(function(){
    $("#notify_popup").slideToggle();
});

$("#close_noti").click(function(){
    $("#notify_popup").slideToggle();
});






