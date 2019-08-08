{{include file="common/header.php"}}

<div class="main">
	<form name="myform" id="myform" action="{{'Guess/edit'|url}}" method="post">
		<input type="hidden" name="guessid" value="{{$detail.guessid}}"/>
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">竞猜标题：</th>
				<td><input type="text" name="title" id="title" size="30" class="input-text" value="{{$detail.title|escape}}"></td>
			</tr>
			<tr>
				<th class="left-txt">竞猜图片：</th>
				<td>
					<input type="hidden" name="image_url" id="input_voucher" value="{{$detail.image_url}}" />
					<div id="previewImage"><img style="max-width: 119px; max-height: 119px;" src="{{$detail.image_url}}" /></div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">截止参与时间：</th>
				<td><input type="text" name="deadline" id="deadline" size="20" class="date input-text" value="{{$detail.deadline}}"></td>
			</tr>
			<tr>
				<th class="left-txt">是否开奖：</th>
				<td>
					<select name="is_open" id="is_open" class="form-control">
						<option {{if $detail.is_open==0}}selected="selected"{{/if}} value="0">否</option>
						<option {{if $detail.is_open==1}}selected="selected"{{/if}} value="1">是</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">开奖结果：</th>
				<td>
					<select id="open_result" class="form-control">
						<option value="A">-</option>
						{{foreach $options as $opk => $opv}}
						<option {{if $detail.open_result==$opk}}selected="selected"{{/if}} value="{{$opk}}">{{$opv}}</option>
						{{/foreach}}
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">选项：</th>
				<td>
					{{foreach $options as $opk => $opv}}
					<p style="padding-top: 10px;">
						<input type="text" name="options_data[{{$opk}}][op_title]" size="30" class="input-text" style="width:300px;" value="{{$detail.option_data.$opk.op_title}}">[{{$opv}}]
						<input type="text" name="options_data[{{$opk}}][op_odds]" size="5" class="input-text" style="width:50px;" value="{{$detail.option_data.$opk.op_odds}}">[{{$opv}}赔率]
					</p>
					{{/foreach}}
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
uploadImage(filUrl, baseJsUrl, 'previewImage', 'input_voucher', 120, 120, uploadUrl);

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

Calendar.setup({
	weekNumbers: false,
    inputField : "deadline",
    trigger    : "deadline",
    dateFormat: "%Y-%m-%d %H:%I:%S",
    showTime: true,
    minuteStep: 1,
    onSelect   : function() {this.hide();}
});

</script>

{{include file="common/footer.php"}}