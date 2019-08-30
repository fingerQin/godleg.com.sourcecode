{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>抽奖记录列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Lucky', 'record');">帮助</button>
			</div>
		</div>
		
		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Lucky/record'|url}}" method="post">
					<div class="form-group">
						<input type="text" name="mobile" class="form-control" style="width:180px;" value="{{$mobile}}" placeholder="请输入手机账号查询">
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
						<th class="w5 text-center">ID</th>
						<th class="w5 text-center">奖品名称</th>
						<th class="w5 text-center">中奖人手机</th>
						<th class="w5 text-center">奖励金币数量</th>
						<th class="w5 text-center">随机值</th>
						<th class="w5 text-center">抽奖时间</th>
					</tr>
				</thead>
				<tbody>
                	{{foreach $list as $item}}
    	           	<tr>
						<td align="center">{{$item.id}}</td>
						<td align="center">{{$item.goods_name}}</td>
						<td align="center">{{$item.mobile}}</td>
						<td align="center">{{$item.reward_val}}</td>
						<td align="center">{{$item.range_val}}</td>
						<td align="center">{{$item.c_time}}</td>
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