{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>打卡任务列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Task', 'taskList');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Task/taskList'|url}}" method="get">
					<div class="form-group">
                        <span class="pull-left form-span">打卡任务名称</span>
						<input type="text" name="openid" class="form-control" style="width:180px;" value="{{$task_name}}" placeholder="请输入打卡任务名称查询">
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
                        <th class="text-center w15">任务名称</th>
                        <th class="text-center w15">主办方</th>
						<th class="text-center w10">地区</th>
						<th class="text-center w10">相册</th>
						<th class="text-center w15">地址</th>
						<th class="text-center w10">经纬度</th>
						<th class="text-center w5">修改时间</th>
						<th class="text-center w5">创建时间</th>
						<th class="text-center w10">管理操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.taskid}}</td>
						<td class="text-center">{{$item.task_name}}</td>
						<td class="text-center">{{$item.name}}</td>
                        <td class="text-center">{{$item.district}}</td>
                        <td class="text-center"><img src="{{$item.imageUrl}}" width="180" alt=""></td>
						<td class="text-left">{{$item.address}}</td>
						<td class="text-center">{{$item.longitude}}/{{$item.latitude}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Task'|access:'records'}}
							<a href="###" onclick="records({{$item.taskid}}, '{{$item.task_name}}')" title="打卡记录">打卡记录</a> | 
							{{/if}}
							{{if 'Task'|access:'editTask'}}
							<a href="###" onclick="edit({{$item.taskid}}, '{{$item.task_name}}')" title="修改">修改</a> | 
							{{/if}}
							{{if 'Task'|access:'deleteTask'}}
							<a href="###" onclick="deleteDialog('deleteTask', '{{'Task'|url:deleteTask:['taskid' => $item.taskid]}}', '{{$item.task_name}}')" title="删除">删除</a>
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

function records(id, name) {
	var title = '『' + name + '』的打卡记录';
	var pageUrl = "{{'Task/records'|url}}?taskid="+id;
	postDialog('taskRecords', pageUrl, title, 800, 600);
}

function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Task/editTask'|url}}?taskid="+id;
	postDialog('editTask', page_url, title, 600, 650);
}

</script>

{{include file="common/footer.php"}}