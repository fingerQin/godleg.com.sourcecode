{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">名字：</th>
				<td>
					<input type="text" name="name" id="name" class="input-text" value="{{$detail.name}}" />
				</td>
			</tr>
            <tr>
				<th class="left-txt">性别：</th>
				<td>
					<select name="sex" class="form-control" >
						<option {{if $detail.sex==1}}'selected'="selected"{{/if}} value="female">男</option>
						<option {{if $detail.sex==2}}'selected'="selected"{{/if}} value="male">女</option>
					</select>
				</td>
			</tr>
            <tr>
				<th class="left-txt">性别：</th>
				<td>
					<select name="type" class="form-control" >
						<option {{if $detail.type==1}}selected="selected"{{/if}} value="1">单字</option>
						<option {{if $detail.type==2}}selected="selected"{{/if}} value="2">双字</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">名字解释</th>
				<td>
					<textarea type="text" name="expl" class="textarea" >{{$detail.expl}}</textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
                    <input type="hidden" name="id" value="{{$detail.id}}" /> 
					<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">

$(document).ready(function(){
	$('#submitID').click(function(){
	    $.ajax({
	    	type: 'post',
            url: '{{'Name/edit'|url}}',
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
</script>

{{include file="common/footer.php"}}