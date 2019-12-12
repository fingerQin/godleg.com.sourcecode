{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>竞猜活动列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				{{if 'Guess'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:void(0)" onclick="add()">添加竞猜活动</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Guess', 'list');">帮助</button>
			</div>
		</div>
		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Guess/list'|url}}" onsubmit="return submitBefore();" method="get">
					<div class="form-group">
						<span class="pull-left form-span">是否开奖</span>
						<select name="is_open" class="form-control" style="width:120px;">
							<option {{if $is_open==-1}}selected="selected"{{/if}} value="-1">全部</option>
							<option {{if $is_open==0}}selected="selected"{{/if}} value="0">否</option>
							<option {{if $is_open==1}}selected="selected"{{/if}} value="1">是</option>
						</select>
					</div>
					<div class="form-group">
						<span class="pull-left form-span">活动标题</span>
						<input type="text" name="title" id="title" class="form-control" style="width: 180px;" value="{{$title}}" placeholder="请输入活动标题">
                    </div>
                    <div class="form-group" style="width:415px;">
						<span class="pull-left form-span">时间</span>
						<input type="text" name="start_time" id="start_time" value="{{$start_time}}" size="20" class="date form-control" style="display:inline;width:160px;" /> ～ 
						<input type="text" name="end_time" id="end_time" value="{{$end_time}}" size="20" class="date form-control" style="display:inline;width:160px;" />
						<script type="text/javascript">
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
						</script>
					</div>
					<div class="form-group">
						<button type="submit" class="form-control btn btn-info" ><span class="glyphicon glyphicon-search"></span> 查询</button>
					</div>
				</form>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="w5 text-center">活动ID</th>
						<th class="w10 text-center">活动名称</th>
						<th class="w10 text-center">活动图片</th>
						<th class="w10 text-center">截止时间</th>
						<th class="w5 text-center">是否开奖</th>
						<th class="w5 text-center">开奖结果</th>
						<th class="w5 text-center">参与总人数</th>
						<th class="w5 text-center">中奖总人数</th>
						<th class="w5 text-center">投注总额</th>
						<th class="w5 text-center">中奖总额</th>
						<th class="w10 text-center">修改时间</th>
						<th class="w10 text-center">创建时间</th>
						<th class="w10 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
                    {{foreach $list as $item}}
    	            <tr>
						<td align="center">{{$item.guessid}}</td>
						<td align="center">{{$item.title}}</td>
						<td align="center"><img alt="活动图片" src="{{$item.image_url}}" width="200" /></td>
						<td align="center">{{$item.deadline}}</td>
						<td align="center">{{if $item.is_open}}是{{else}}否{{/if}}</td>
						<td align="center">{{$item.open_result}}</td>
						<td align="center">{{$item.total_people}}</td>
						<td align="center">{{$item.prize_people}}</td>
						<td align="center">{{$item.total_bet_gold}}</td>
						<td align="center">{{$item.total_prize_gold}}</td>
						<td align="center">{{$item.u_time}}</td>
						<td align="center">{{$item.c_time}}</td>
						<td align="center">
							{{if 'Guess'|access:'record'}}
							<a href="###" onclick="records({{$item.guessid}}, '{{$item.title}}')">竞猜记录</a>
							{{/if}}

							{{if 'Guess'|access:'edit'}}
							<a href="###" onclick="edit({{$item.guessid}}, '{{$item.title}}')">编辑</a>
							{{/if}}

							{{if 'Guess'|access:'delete'}}
							<a class="del" rel="{{$item.guessid}}" href="javascript:void(0)">删除</a>
							{{/if}}
						</td>
			        </tr>
				    {{/foreach}}
                </tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav>
									<ul class="pagination">{{$pageHtml nofilter}}</ul>
								</nav>
							</div>
						</td>
					</tr>
				</tfoot>
            </table>
		</div>
	</div>
</div>

<script type="text/javascript">
//表单提交
$(function () {
	$('tbody tr td .del').click(function () {
		var id = $(this).attr('rel');
		layer.confirm('您确定要删除吗？', {
			title: "操作提示",
			btn: ['确定', '取消'] //按钮
		}, function() {
			$.ajax({
				type: 'post',
				data: {'guessid': id},
				url: "{{'Guess/delete'|url}}",
				dataType: 'json',
				success: function (rsp) {
					if (rsp.code == 200) {
						success(rsp.msg, 1, '');
					} else {
						fail(rsp.msg, 3);
					}
				}
			});
		}, function(){
			return true;
		});
	});
	$('.reload').click(function(){
		window.location.reload()
	})
});

function add() {
	postDialog('addGuess', '{{'Guess/add'|url}}', '添加竞猜活动', 700, 700);
}
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Guess/edit'|url}}?guessid="+id;
	postDialog('editGuess', page_url, title, 700, 700);
}
function records(id, name) {
	var title = '竞猜记录『' + name + '』';
	postDialog('records', '{{'Guess/record'|url}}'+"?guessid=" + id, title, 800, 600);
}
</script>

{{include file="common/footer.php"}}