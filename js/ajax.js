$(document).ready(function(){
	bascket();
	$("#f_name").live('focus', function(){if($(this).val()=='Имя')$(this).val('')});
	$("#f_name").live('blur', function(){if($(this).val()=='')$(this).val('Имя')});
	
	$("#f_email").live('focus', function(){if($(this).val()=='E-mail')$(this).val('')});
	$("#f_email").live('blur', function(){if($(this).val()=='')$(this).val('E-mail')});
	
	$("#f_phone").live('focus', function(){if($(this).val()=='Телефон')$(this).val('')});
	$("#f_phone").live('blur', function(){if($(this).val()=='')$(this).val('Телефон')});
	
	$("#f_message").live('focus', function(){if($(this).val()=='Сообщение')$(this).val('')});
	$("#f_message").live('blur', function(){if($(this).val()=='')$(this).val('Сообщение')});
	
	/////Add to delivery prices
	$('#delivery').live('change',function(){
		
		var id=$(this).val();
		var dataString = 'id='+id;
		$.ajax({type: "POST",url: "/ajax/deliveryprice",data: dataString,cache: false,success: function(html)
		{
			$('#deliver_price').html(html);
		}});
	});
});


/////Add to shop cart
$('.buy').live('click',function(){
	
	var id=$(this).attr('name');
	var price_id=$('#price'+id).val();
	//var amount=$('#amount'+id).val();//alert(amount);
	var amount=$('#cnt').val();
	var dataString = 'id='+id+'&amount='+amount+'&price_id='+price_id;
	$.ajax({type: "POST",url: "/ajax/incart",data: dataString,cache: false,success: function(html)
	{
		bascket();
		$.stickr({note:'Товар добавлен!',className:'next',position:{right:0,bottom:0},time:1000,speed:300});
	}});
});



///Bascket
function bascket()
{
	$.ajax
	({
		type: "POST",
		url: "/ajax/bascket",
		cache: false,
		success: function(html)
		{
			$("#bascket").html(html);
		}
	});
}


////Add comments
function addComment(id, type)
{
    var name = $("#name_form").val();
    var message = $("#text_form").val();
	var photo = $("#avatar").val();
    var dataString = 'id='+id+'&type='+type+'&name='+name+'&message='+message+'&photo='+photo;
    $.ajax({type: "POST",url: "/ajax/addcomment", data: dataString,cache: false,success: function(html){$("#input").html(html);$("#name_form").val('');$("#text_form").val('');}});
    return false;
}

/////////

///Send form feedback
function sendFeedback()
{
    //$("#loader").css('display', 'block');
    var name = $("#f_name").val();
    var email = $("#f_email").val();//alert(email);
	var phone = $("#f_phone").val();//alert(email);
    var message = $("#f_message").val();

    var dataString = 'name='+name+'&email='+email+'&phone='+phone+'&message='+message;
    $.ajax({type: "POST",url: "/ajax/feedback", data: dataString,dataType: 'json',cache: false, success:function(html){
        $("#message").html(html[1]);

        if(html[0]==1)
		{
			$("#f_name").val('');
			$("#f_email").val('');//alert(email);
			$("#f_phone").val('');
			$("#f_message").val('');
			//closeFeedback(5000);
		}

    }});
    //$("#message").html(html);
    $("#loader").css('display', 'none');
    return false;
}

////Show form feedback
function showFeedback()
{
    $("#feedback").animate({
        display:'block', width:265, opacity: 0.9, height: 340
    }, 100, "linear");
    $("#feedback").css('display', 'block');
}

////Close form feedback
function closeFeedback(sec)
{
    $("#feedback").fadeIn(function(){setTimeout(function(){$("#feedback").fadeOut("fast");$("#f_message").val('');$("#message").html('');}, sec);});
}

////Add comments
function mail_to()
{
    var email = $("input[name=mailer]").val();
    var dataString = 'email='+email;
    $.ajax({type: "POST",url: "/ajax/mailto", data: dataString,cache: false,success: function(html){$("#message_mailer").html(html);}});
    return false;
}