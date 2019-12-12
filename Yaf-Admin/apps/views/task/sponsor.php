{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>主办方列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				{{if 'Task'|access:'addSponsor'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加主办方</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Task', 'sponsor');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Task/sponsor'|url}}" method="get">
					<div class="form-group">
                        <span class="pull-left form-span">主办方名称</span>
						<input type="text" name="openid" class="form-control" style="width:180px;" value="{{$name}}" placeholder="请输入名称查询">
					</div>
					<div class="form-group">
						<button type="submit" class="form-control btn btn-info"><span class="glyphicon glyphicon-search"></span> 查询</button>
					</div>
				</form>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="text-center w5">ID</th>
						<th class="text-center w15">主办方名称</th>
						<th class="text-center w10">地区</th>
						<th class="text-center w15">相册</th>
						<th class="text-center w25">联系人/电话/地址</th>
						<th class="text-center w10">经纬度</th>
						<th class="text-center w5">修改时间</th>
						<th class="text-center w5">创建时间</th>
						<th class="text-center w10">管理操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.sponsorid}}</td>
						<td class="text-center">{{$item.name}}</td>
						<td class="text-center">{{$item.district}}</td>
						<td class="text-center"><img src="{{$item.imageUrl}}" width="180" alt=""></td>
						<td class="text-left">{{$item.link_man}}<br/>{{$item.link_phone}}<br/>{{$item.address}}</td>
						<td class="text-center">{{$item.longitude}}/{{$item.latitude}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Task'|access:'addTask'}}
							<p><a href="###" onclick="addTask({{$item.sponsorid}}, '{{$item.name}}')" >发布打卡任务</a></p>
							{{/if}}
							{{if 'Task'|access:'taskList'}}
							<p><a href="{{'Task'|url:taskList:['sponsorid' => $item.sponsorid]}}">查看打卡任务</a></p>
							{{/if}}
							{{if 'Task'|access:'editSponsor'}}
							<a href="###" onclick="edit({{$item.sponsorid}}, '{{$item.name}}')" title="修改">修改</a> | 
							{{/if}}
							{{if 'Task'|access:'deleteSponsor'}}
							<a href="###" onclick="deleteDialog('deleteSponsor', '{{'Task'|url:deleteSponsor:['sponsorid' => $item.sponsorid]}}', '{{$item.name}}')" title="删除">删除</a>
							{{/if}}
						</td>
					</tr>
    				{{/foreach}}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav><ul class="pagination">{{$pageHtml nofilter}}</ul></nav>
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
function add() {
	postDialog('addSponsor', '{{'Task/addSponsor'|url}}', '添加主办方', 600, 650);
}

function edit(sponsorid, name) {
	var title = '修改『' + name + '』';
	var pageUrl = "{{'Task/editSponsor'|url}}?sponsorid="+sponsorid;
	postDialog('editSponsor', pageUrl, title, 600, 650);
}

function addTask(sponsorid, sponsorName) {
	var pageUrl = "{{'Task/addTask'|url}}?sponsorid="+sponsorid;;
	postDialog('addTask', pageUrl, '《' + sponsorName + '》：发布打卡任务', 600, 650);
}

/**
 * 弹出一个重置游戏缓存的对话框。
 * @return void
 */
function resetCacheDialog(request_url) {
	layer.confirm('您确定要重置游戏缓存吗？', {
		btn: ['确定', '取消'],
		title: '操作提示'
	}, 
	function() {
		$.ajax({
			type: "POST",
			url: '{{'Task/resetCache'|url}}',
			dataType: 'json',
			success: function(data){
				if (data.code == 200) {
					dialogTips(data.msg, 1);
				} else {
					dialogTips(data.msg, 5);
					return false;
				}
			}
		});
	},
	function(){
		// 点击取消按钮啥事也不做。
	});
}

</script>

{{include file="common/footer.php"}}