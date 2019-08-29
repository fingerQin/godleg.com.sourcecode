{{include file="common/header.php"}}

<style type="text/css">
.albums_image {float:left; margin: 5px;}
</style>

<div class="main">
    <form action="{{'Task/addSponsor'|url}}" method="post" name="myform" id="goods-prods-form">
        <table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">主办方名称：</th>
				<td><input type="text" name="name" id="name" size="60" class="form-control" value=""></td>
			</tr>
            <tr>
				<th class="left-txt">县区编码：</th>
				<td><input type="text" name="district_code" id="district_code" size="60" class="form-control" value="520112000"></td>
			</tr>
            <tr>
				<th class="left-txt">主办方联系地址：</th>
				<td><input type="text" name="address" id="address" size="60" class="form-control" value=""></td>
            </tr>
            <tr>
				<th class="left-txt">联系人：</th>
				<td><input type="text" name="link_man" id="link_man" size="60" class="form-control" value=""></td>
            </tr>
            <tr>
				<th class="left-txt">联系方式：</th>
                <td>
                    <input type="text" name="link_phone" id="link_phone" size="60" class="form-control" value="">
                    <span style="color:grey;">手机或电话</span>
                </td>
            </tr>
            <tr>
				<th class="left-txt">纬度：</th>
				<td>
                    <input type="text" name="longitude" id="longitude" size="60" class="form-control" value="" style="width:70px;display:inline;">
                    <input type="text" name="latitude" id="latitude" size="60" class="form-control" value="" style="width:70px;display:inline;">
                    <div style="color:grey;">(主办方所在地址经纬度)</div>
                </td>
            </tr>
            <tr>
                <th class="left-txt">主办方相册：</th>
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
uploadImage(filUrl, baseJsUrl, 'previewImage1', 'input_voucher1', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage2', 'input_voucher2', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage3', 'input_voucher3', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage4', 'input_voucher4', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage5', 'input_voucher5', 120, 120, uploadUrl);

// layer.tips(errmsg, '#form-submit');

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