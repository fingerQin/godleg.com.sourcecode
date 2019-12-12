{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">身份：</th>
				<td>
					<input type="text" name="title" id="title" class="input-text" value="{{$detail.title}}" />
				</td>
			</tr>
            <tr>
				<th class="left-txt">优先级：</th>
				<td>
					<select name="priority" class="form-control" >
						<option {{if $detail.priority==1}}selected="selected"{{/if}} value="1">高(50%)</option>
						<option {{if $detail.priority==2}}selected="selected"{{/if}} value="2">中(49%)</option>
						<option {{if $detail.priority==3}}selected="selected"{{/if}} value="3">低(1%)</option>
					</select>
				</td>
			</tr>
            <tr>
				<th class="left-txt">身份类型：</th>
				<td>
					<select name="type" class="form-control" >
						<option {{if $detail.type==1}}selected="selected"{{/if}} value="1">士</option>
						<option {{if $detail.type==2}}selected="selected"{{/if}} value="2">农</option>
						<option {{if $detail.type==3}}selected="selected"{{/if}} value="3">工</option>
						<option {{if $detail.type==4}}selected="selected"{{/if}} value="4">商</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">名字解释</th>
				<td>
					<textarea type="text" name="intro" class="textarea">{{$detail.intro}}</textarea>
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
            url: '{{'Prelife/edit'|url}}',
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