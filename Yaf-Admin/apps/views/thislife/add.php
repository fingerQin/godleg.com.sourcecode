{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">身份：</th>
				<td>
					<input type="text" name="title" id="title" class="input-text" />
				</td>
			</tr>
            <tr>
				<th class="left-txt">优先级：</th>
				<td>
					<select name="priority" class="form-control" >
						<option value="1">高(50%)</option>
						<option value="2">中(49%)</option>
						<option value="3">低(1%)</option>
					</select>
				</td>
			</tr>
            <tr>
				<th class="left-txt">评分：</th>
				<td>
					<input type="text" name="score" id="score" class="input-text" vaule="60" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">身份介绍</th>
				<td>
					<textarea type="text" name="intro" class="textarea" ></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
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
            url: '{{'Thislife/add'|url}}',
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