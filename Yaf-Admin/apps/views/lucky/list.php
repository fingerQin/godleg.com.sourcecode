{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>抽奖活动奖品列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				{{if 'lucky'|access:'record'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:void(0);" onclick="view();">查看投资记录</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Lucky', 'list');">帮助</button>
			</div>
		</div>

		<form name="myform" id="myform" action="{{'Lucky/set'|url}}" method="post">
		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="w20 text-center">奖品名称</th>
						<th class="w10 text-center">奖品图片</th>
						<th class="w10 text-center">每天中奖最大次数</th>
						<th class="w5 text-center">随机最小值</th>
						<th class="w5 text-center">随机最大值</th>
						<th class="w5 text-center">奖励数值</th>
						<th class="w10 text-center">创建人</th>
						<th class="w10 text-center">创建时间</th>
					</tr>
				</thead>
				<tbody>
					{{foreach $list as $item}}
    	            <tr>
						<td align="center">
							<input type="text" class="form-control" name="goods[{{$item.id}}][goods_name]" value="{{$item.goods_name}}" />
						</td>
						<td align="center">
							<input type="hidden" class="form-control" name="goods[{{$item.id}}][image_url]" id="avatar_{{$item.id}}" value="{{$item.image_url}}" />
							<div id="avatar_view_{{$item.id}}"></div>
						</td>
						<td align="center"><input type="text" size="5" class="form-control" name="goods[{{$item.id}}][day_max]" value="{{$item.day_max|intval}}" /></td>
						<td align="center"><input type="text" size="5" class="form-control" name="goods[{{$item.id}}][min_range]" value="{{$item.min_range|intval}}" /></td>
						<td align="center"><input type="text" size="5" class="form-control" name="goods[{{$item.id}}][max_range]" value="{{$item.max_range|intval}}" /></td>
						<td align="center"><input type="text" class="form-control" size="5" name="goods[{{$item.id}}][reward_val]" value="{{intval($item.reward_val)}}" /></td>
						<td align="center">{{$item.real_name}}</td>
						<td align="center">{{$item.c_time}}</td>
			        </tr>
                	{{/foreach}}
					<tr>
						<td colspan="20">
							<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
						</td>
					</tr>
					</tbody>
			</table>
		</div>
	</div>
</div>

<script src="{{'AjaxUploader/uploadImage.js'|js}}"></script>
<script type="text/javascript">

var uploadUrl = '{{'Index/Upload'|url}}';
var baseJsUrl = '{{''|js}}';
var filUrl    = '{{$files_domain_name}}';

uploadImage(filUrl, baseJsUrl, 'avatar_view_1', 'avatar_1', 40, 40, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'avatar_view_2', 'avatar_2', 40, 40, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'avatar_view_3', 'avatar_3', 40, 40, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'avatar_view_4', 'avatar_4', 40, 40, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'avatar_view_5', 'avatar_5', 40, 40, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'avatar_view_6', 'avatar_6', 40, 40, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'avatar_view_7', 'avatar_7', 40, 40, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'avatar_view_8', 'avatar_8', 40, 40, uploadUrl);

$(document).ready(function(){
	$('#submitID').click(function(){
	    $.ajax({
	    	type: 'post',
            url: $('form').eq(0).attr('action'),
            dataType: 'json',
            data: $('form').eq(0).serialize(),
            success: function(data) {
                if (data.code == 200) {
					success(data.msg, 1);
                } else {
					fail(data.msg, 3);
                }
            }
	    });
	});
});

function view() {
	postDialog('view', '{{'Lucky/record'|url}}', '大转盘投资记录', 800, 600);
}
</script>

{{include file="common/footer.php"}}