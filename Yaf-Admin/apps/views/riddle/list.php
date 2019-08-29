{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>游戏配置列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				{{if 'Riddle'|access:'add'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加游戏配置</button>
				{{/if}}
				{{if 'Riddle'|access:'resetCache'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="resetCacheDialog();">重置游戏缓存</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Riddle', 'list');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Riddle/list'|url}}" method="get">
					<div class="form-group">
                        <span class="pull-left form-span">OpenID</span>
						<input type="text" name="openid" class="form-control" style="width:180px;" value="{{$openid}}" placeholder="请输入要查询的OpenID">
					</div>
					<div class="form-group">
                        <span class="pull-left form-span">难度分</span>
						<input type="text" name="score" class="form-control" style="width:80px;" value="{{$score}}" placeholder="请输入难度分">
					</div>
                    <div class="form-group">
                        <span class="pull-left form-span">来源</span>
						<select id="source" name="source" class="form-control" style="width:100px;">
                            <option value="-1">请选择</option>
							<option {{if $source==1}}selected="selected"{{/if}} value="1">系统创建</option>
                            <option {{if $source==2}}selected="selected"{{/if}} value="2">用户创建</option>
						</select>
					</div>
                    <div class="form-group">
                        <span class="pull-left form-span">优先级</span>
						<select id="priority" name="priority" class="form-control" style="width:100px;">
                            <option value="-1">请选择</option>
							<option {{if $priority==1}}selected="selected"{{/if}} value="1">高</option>
                            <option {{if $priority==2}}selected="selected"{{/if}} value="2">中</option>
							<option {{if $priority==3}}selected="selected"{{/if}} value="3">低</option>
						</select>
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
						<th class="text-center w5">OpenID</th>
						<th class="text-center w5">难度分</th>
						<th class="text-center w5">优先级</th>
						<th class="text-center w5">来源</th>
						<th class="text-center w15">题目</th>
						<th class="text-center w15">题目图片</th>
						<th class="text-center w15">答案</th>
						<th class="text-center w15">答案图片</th>
						<th class="text-center w5">修改时间</th>
						<th class="text-center w5">创建时间</th>
						<th class="text-center w10">管理操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-center">{{$item.openid}}</td>
						<td class="text-center">{{$item.score}}</td>
						<td class="text-center">{{$item.priority}}</td>
						<td class="text-center">{{$item.source}}</td>
						<td class="text-center">{{$item.question}}</td>
						<td class="text-center"><img width="80" src="{{$item.question_img}}" /></td>
						<td class="text-center">{{$item.answer}}</td>
						<td class="text-center"><img width="80" src="{{$item.answer_img}}" /></td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Riddle'|access:'edit'}}
							<a href="###" onclick="edit({{$item.id}}, '{{$item.openid}}')" title="修改">修改</a> | 
							{{/if}}
							{{if 'Riddle'|access:'delete'}}
							<a href="###" onclick="deleteDialog('deletePrelife', '{{'Riddle'|url:delete:['id' => $item.id]}}', '{{$item.openid}}')" title="删除">删除</a>
							{{/if}}
						</td>
					</tr>
    				{{/foreach}}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav><ul class="pagination">{{$pageHtml}}</ul></nav>
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
	postDialog('addRiddle', '{{'Riddle/add'|url}}', '添加游戏配置', 500, 650);
}
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Riddle/edit'|url}}?id="+id;
	postDialog('editRiddle', page_url, title, 500, 650);
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
			url: '{{'Riddle/resetCache'|url}}',
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