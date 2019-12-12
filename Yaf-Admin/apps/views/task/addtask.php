{{include file="common/header.php"}}

<style type="text/css">
.albums_image {float:left; margin: 5px;}
</style>

<div class="main">
    <form action="{{'Task/addTask'|url}}" method="post" name="myform" id="goods-prods-form">
        <table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">打卡任务名称：</th>
				<td><input type="text" name="task_name" id="task_name" size="60" class="form-control" value=""></td>
            </tr>
            <tr>
				<th class="left-txt">奖励金币：</th>
                <td>
                    <input type="text" name="gold" id="gold" size="60" class="form-control" value="0" style="width:60px;display:inline;">
                    <span style="margin-left:5px;font-size:14px;font-weight:bold;color:#555;">枚</span>
                </td>
            </tr>
            <tr>
				<th class="left-txt">运动步数：</th>
                <td>
                    <input type="text" name="move_step" id="move_step" size="60" class="form-control" value="0" style="width:60px;display:inline;">
                    <span style="margin-left:5px;font-size:14px;font-weight:bold;color:#555;">步</span>
                </td>
            </tr>
            <tr>
				<th class="left-txt">每人参与上限：</th>
                <td>
                    <input type="text" name="times_limit" id="times_limit" size="60" class="form-control" value="1" style="width:60px;display:inline;">
                    <span style="margin-left:5px;font-size:14px;font-weight:bold;color:#555;">次（0 不限制参与次数）</span>
                </td>
			</tr>
            <tr>
				<th class="left-txt">县区编码：</th>
				<td><input type="text" name="district_code" id="district_code" size="60" class="form-control" value="520112000"></td>
			</tr>
            <tr>
				<th class="left-txt">打卡地址：</th>
				<td><input type="text" name="address" id="address" size="60" class="form-control" value=""></td>
            </tr>
            <tr>
				<th class="left-txt">经纬度：</th>
                <td>
                    <input type="text" name="longitude" id="longitude" size="60" class="form-control" value="" style="width:70px;display:inline;">
                    <input type="text" name="latitude" id="latitude" size="60" class="form-control" value="" style="width:70px;display:inline;">
                    <div style="color:grey;">(打卡位置经纬度)</div>
                </td>
            </tr>
            <tr>
				<th class="left-txt">参与时间：</th>
                <td>
                    <input type="text" name="start_time" id="start_time" size="150" class="date form-control" value="" style="width:150px;display:inline;">
                    <input type="text" name="end_time" id="end_time" size="150" class="date form-control" value="" style="width:150px;display:inline;">
                </td>
            </tr>
            <tr>
				<th class="left-txt">每日/总参与上限：</th>
                <td>
                    <input type="text" name="everyday_times" id="everyday_times" size="60" class="form-control" value="1000" style="width:70px;display:inline;">
                    ~ 
                    <input type="text" name="total_times" id="total_times" size="60" class="form-control" value="10000" style="width:70px;display:inline;">
                    <div style="color:grey;">(每日允许参与上限/累计允许参与人数上限)</div>
                </td>
            </tr>
            <tr>
                <th class="left-txt">显示状态：</th>
                <td>
                    <select name="display" class="form-control" style="width:100px;">
                        <option value="1">显示</option>
                        <option value="0">隐藏</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="left-txt">宣传相册：</th>
                <td>
                    <input type="hidden" name="albums[]" id="input_voucher1" value="" />
                    <div id="previewImage1" class="albums_image"></div>

                    <input type="hidden" name="albums[]" id="input_voucher2" value="" />
                    <div id="previewImage2" class="albums_image"></div>

                    <input type="hidden" name="albums[]" id="input_voucher3" value="" />
                    <div id="previewImage3" class="albums_image"></div>

                    <input type="hidden" name="albums[]" id="input_voucher4" value="" />
                    <div id="previewImage4" class="albums_image"></div>

                    <input type="hidden" name="albums[]" id="input_voucher5" value="" />
                    <div id="previewImage5" class="albums_image"></div>
                </td>
            </tr>
            <tr>
				<td></td>
				<td>
                    <input type="hidden" name="sponsorid" value="{{$sponsorid}}" />
					<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
				</td>
			</tr>
		</table>

	</form>
</div>

<script charset="utf-8" src="{{'kindeditor/kindeditor-all.js'|js}}"></script>
<script charset="utf-8" src="{{'kindeditor/lang/zh-CN.js'|js}}"></script>
<script src="{{'AjaxUploader/uploadImage.js'|js}}"></script>

<script type="text/javascript">

var uploadUrl = '{{'Index/Upload'|url}}';
var baseJsUrl = '{{''|js}}';
var filUrl    = '{{$files_domain_name}}';
uploadImage(filUrl, baseJsUrl, 'previewImage1', 'input_voucher1', 100, 100, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage2', 'input_voucher2', 100, 100, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage3', 'input_voucher3', 100, 100, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage4', 'input_voucher4', 100, 100, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage5', 'input_voucher5', 100, 100, uploadUrl);

Calendar.setup({
	weekNumbers: false,
    inputField : "start_time",
    trigger    : "start_time",
    dateFormat: "%Y-%m-%d %H:%I:%S",
    showTime: true,
    minuteStep: 1,
    onSelect   : function() {this.hide();}
});

Calendar.setup({
	weekNumbers: false,
    inputField : "end_time",
    trigger    : "end_time",
    dateFormat: "%Y-%m-%d %H:%I:%S",
    showTime: true,
    minuteStep: 1,
    onSelect   : function() {this.hide();}
});

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

</script>

{{include file="common/footer.php"}}