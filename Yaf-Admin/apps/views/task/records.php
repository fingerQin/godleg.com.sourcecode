{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>打卡记录
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Task', 'records');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Task/taskList'|url}}" method="get">
					<div class="form-group">
                        <span class="pull-left form-span">打卡任务名称</span>
						<input type="text" name="openid" class="form-control" style="width:180px;" value="" placeholder="请输入打卡任务名称查询">
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
                        <th class="text-center w10">用户名</th>
                        <th class="text-center w10">步数</th>
						<th class="text-center w10">金币</th>
						<th class="text-center w15">经纬度</th>
						<th class="text-center w15">打卡时间</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-center">{{$item.realname}}</td>
						<td class="text-center">{{$item.step_count}}</td>
                        <td class="text-center">{{$item.gold}}</td>
						<td class="text-center">{{$item.longitude}}/{{$item.latitude}}</td>
						<td class="text-center">{{$item.c_time}}</td>
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

</script>

{{include file="common/footer.php"}}