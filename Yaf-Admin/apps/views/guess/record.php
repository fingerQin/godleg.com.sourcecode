{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>竞猜记录 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Guess', 'record');">帮助</button>
			</div>
		</div>
		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Guess/list'|url}}" onsubmit="return submitBefore();" method="get">
					<div class="form-group">
						<span class="pull-left form-span">中奖状态</span>
						<select name="prize_status" class="form-control" style="width:120px;">
							<option {{if $prize_status==-1}}selected="selected"{{/if}} value="-1">全部</option>
							<option {{if $prize_status==0}}selected="selected"{{/if}} value="0">未开奖</option>
							<option {{if $prize_status==1}}selected="selected"{{/if}} value="1">已中奖</option>
							<option {{if $prize_status==2}}selected="selected"{{/if}} value="2">未中奖</option>
						</select>
					</div>
					<div class="form-group">
						<span class="pull-left form-span">用户账号：</span>
						<input type="text" name="mobile" id="mobile" class="form-control" style="width: 180px;" value="{{$mobile}}" placeholder="请输入用户账号">
                    </div>
					<div class="form-group">
						<input type="hidden" name="guessid" value="{{$guessid}}" />
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
						<th class="w10 text-center">用户昵称</th>
						<th class="w10 text-center">手机号码</th>
						<th class="w10 text-center">投注金币</th>
						<th class="w10 text-center">中奖金币</th>
						<th class="w10 text-center">是否中奖</th>
						<th class="w10 text-center">投注时间</th>
					</tr>
				</thead>
				<tbody>
                    {{foreach $list as $item}}
    	            <tr>
						<td align="center">{{$item.nickname}}</td>
						<td align="center">{{$item.mobile}}</td>
						<td align="center">{{$item.bet_gold}}</td>
						<td align="center">{{$item.prize_money}}</td>
						<td align="center">{{$item.prize_status}}</td>
						<td align="center">{{$item.c_time}}</td>
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
	$('.reload').click(function(){
		window.location.reload()
	})
});
</script>

{{include file="common/footer.php"}}