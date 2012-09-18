$(document).ready(function(){
	
	///////////////Sort
	$(".tb_sort").tableDnD({
		onDragClass:"hover"
	});
	$(".move").live('mouseup',function(){
		 sortA();
	});
	
	///////Active
	$('.active_status').live('click',function()
	{
		var tb=$("#action").val();
		var tb2=$("#action2").val();
		var id=$(this).attr('id');
		var dataString = 'id='+id+'&tb='+tb+'&tb2='+tb2;
		$.ajax({type:"POST",url:"/ajaxadmin/active",dataType:'json', data:dataString,cache:false,success:
		function(data)
		{
			if(!data.access)$('#'+id).html(data.active);//alert(tb);
			$('#message').html(data.message);	
			autoHide();
		}
	});});

    $('#add_module').live('change',function()
    {
        var id=$(this).val();
        var dataString = 'id='+id;
        $.ajax({type:"POST",url:"/ajaxadmin/addmodule",dataType:'json', data:dataString,cache:false,success:
            function(data)
            {
                if(data)
                {
                    $('#name_module').val(data.name);
                    $('#comment_module').val(data.comment);
                    $('#tables_module').val(data.tables);
                }
                else{
                    $('#name_module').val('');
                    $('#comment_module').val('');
                    $('#tables_module').val('');
                }
            }
        });
    });
	
	$('#catalog_add').live('change',function()
    {
        var id=$(this).val();
        var dataString = 'id='+id;
        $.ajax({type:"POST",url:"/ajaxadmin/orderproduct",dataType:'json', data:dataString,cache:false,success:
            function(data)
            {
            	$('#product_add').html(data.content);
            }
        });
    });
	
	$('#product_add').live('change',function()
    {
        var id=$(this).val();
		var order_id=$(this).attr('name');
        var dataString = 'id='+id+'&order_id='+order_id;
        $.ajax({type:"POST",url:"/ajaxadmin/orderproductview",dataType:'json', data:dataString,cache:false,success:
            function(data)
            {
            	$('#order_product').html(data.content);
				$('#total').html(data.total);
            }
        });
    });
	
	$('#addprice').live('click',function()
    {
        var id=$("input[name=id]").val();
        var dataString = 'id='+id;
        $.ajax({type:"POST",url:"/ajaxadmin/addprice",dataType:'json', data:dataString,cache:false,success:
            function(data)
            {
            	$('#price_tb').html(data.content);
            }
        });
    });
});

function sortA()
{
	var tb=$("#action").val();
	var tb2=$("#action2").val();
	var arr=$(".tb_sort").tableDnDSerialize();
	var dataString = 'arr='+arr+'&tb='+tb+'&tb2='+tb2;//alert(dataString);
	$.ajax({type: "POST",url: "/ajaxadmin/sort",dataType:'json',data: dataString,cache: false,success:function(data){$('#message').html(data.message);autoHide();}});
}