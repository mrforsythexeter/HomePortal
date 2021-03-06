{$ci->load->model('money_stats_model')}

<div class="widget">
	<div class="widget_title">Account Balances</div>
	<div class="widget_content box_background border_color_bottom border_color_top border_color_left border_color_right">
		<table class="data_grid style_only">
			<thead>
				<tr>
					<th>Account</th>
					<th>Difference</th>
					<th>Balance</th>
				</tr>
			</thead>
			<tbody>
				{assign var='total_balance' value=0}
				{assign var='total_diff' value=0}
				{foreach from=$ci->money_stats_model->account_balances() item=a}
					{assign var='total_balance' value=$total_balance+$a.this_month}
					{assign var='total_diff' value=$total_diff+$a.diff}
					<tr title="{$a.description}">
						 <td>{$a.name}</td>
						 <td style="color:{if $a.diff<0}red{else}green{/if}">{$a.diff|number_format:2:".":","}</td>
						 <td style="color:{if $a.this_month<0}red{else}green{/if}">{$a.this_month|number_format:2:".":","}</td>
					</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<td>Total</td>
					<td style="color:{if $total_diff<0}red{else}green{/if}">{$total_diff|number_format:2:".":","}</td>
					<td style="color:{if $total_balance<0}red{else}green{/if}">{$total_balance|number_format:2:".":","}</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>