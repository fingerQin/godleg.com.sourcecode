{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">题目：</th>
				<td>
					<textarea type="text" name="question" class="textarea" ></textarea>
				</td>
			</tr>
			<tr>
				<th class="left-txt">题目图片：</th>
				<td>
					<input type="hidden" name="question_img" id="question_img" value="" />
					<div id="previewImage"></div>
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
				<th class="left-txt">难度分：</th>
				<td>
					<input type="text" name="score" id="score" class="input-text" value="60" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">答案：</th>
				<td>
					<textarea type="text" name="answer" class="textarea"></textarea>
				</td>
			</tr>
			<tr>
				<th class="left-txt">题目图片：</th>
				<td>
					<input type="hidden" name="answer_img" id="answer_img" value="" />
					<div id="answerPreviewImage"></div>
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
            url: '{{'Riddle/add'|url}}',
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