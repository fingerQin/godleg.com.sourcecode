{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">题目：</th>
				<td>
					<textarea type="text" name="question" class="textarea">{{$detail.question|escape}}</textarea>
				</td>
			</tr>
			<tr>
				<th class="left-txt">题目图片：</th>
				<td>
					<input type="hidden" name="question_img" id="question_img" value="{{$detail.question_img}}" />
					<div id="previewImage"></div>
				</td>
			</tr>
            <tr>
				<th class="left-txt">优先级：</th>
				<td>
					<select name="priority" class="form-control">
						<option {{if $detail.priority==1}}selected="selected"{{/if}} value="1">高(50%)</option>
						<option {{if $detail.priority==2}}selected="selected"{{/if}} value="2">中(49%)</option>
						<option {{if $detail.priority==3}}selected="selected"{{/if}} value="3">低(1%)</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">难度分：</th>
				<td>
					<input type="text" name="score" id="score" class="input-text" value="{{$detail.score}}" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">答案：</th>
				<td>
					<textarea type="text" name="answer" class="textarea">{{$detail.answer|escape}}</textarea>
				</td>
			</tr>
			<tr>
				<th class="left-txt">题目图片：</th>
				<td>
					<input type="hidden" name="answer_img" id="answer_img" value="{{$detail.answer_img|escape}}" />
					<div id="answerPreviewImage"></div>
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

<script src="{{'AjaxUploader/uploadImage.js'|js}}"></script>
<script type="text/javascript">

var uploadUrl = '{{'Index/Upload'|url}}';
var baseJsUrl = '{{''|js}}';
var filUrl    = '{{$files_domain_name}}';
uploadImage(filUrl, baseJsUrl, 'previewImage', 'question_img', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'answerPreviewImage', 'answer_img', 120, 120, uploadUrl);


$(document).ready(function(){
	$('#submitID').click(function(){
	    $.ajax({
	    	type: 'post',
            url: '{{'Riddle/edit'|url}}',
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