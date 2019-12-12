{{include file="common/header.php"}}

<div class="main">
	<form action={{'Order/deliverGoods'|url}} method="post" name="myform" id="myform">
		<table cellpadding="2" cellspacing="1" class="content" width="100%">
			<tbody>
				<tr>
					<th class="left-txt">快递厂商：</th>
					<td>
                        <select name="logistics_code" id="logistics_code" class="form-control" style="width:120px;">
                            <option value="">请选择快递厂商</option>
                            {{foreach $express as $code => $name}}
                            <option value="{{$code}}">{{$name}}</option>
                            {{/foreach}}
                        </select>
					</td>
				</tr>
				<tr>
					<th class="left-txt">快递单号：</th>
					<td>
						<input type="text" name="logistics_number" id="logistics_number" size="50" class="input-text" value="" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
                        <input type="hidden" name="orderId" value="{{$orderId}}" />
						<span><input class="btn btn-default" id="submitID" type="button" value="提交"></span>
					</td>
				</tr>
			<tbody>
		</table>

	</form>
</div>

<script type="text/javascript">
<!--

$(document).ready(function(){
	$('#submitID').click(function(){
	    $.ajax({
	    	type: 'post',
            url: $('form').eq(0).attr('action'),
            dataType: 'json',
            data: $('form').eq(0).serialize(),
            success: function(data) {
                if (data.code == 200) {
					success(data.msg, 1, 'parent');
                } else {
					fail(data.msg, 3);
                }
            }
	    });
	});
});

//-->
</script>

{{include file="common/footer.php"}}