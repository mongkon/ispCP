<!-- INCLUDE "header.tpl" -->
<body>
	<div class="header">
		{MAIN_MENU}
		<div class="logo">
			<img src="{THEME_COLOR_PATH}/images/ispcp_logo.png" alt="ispCP Omega logo" />
			<img src="{THEME_COLOR_PATH}/images/ispcp_webhosting.png" alt="ispCP Omega" />
		</div>
	</div>
	<div class="location">
		<div class="location-area">
			<h1 class="webtools">{TR_MENU_SYSTEM_TOOLS}</h1>
		</div>
		<ul class="location-menu">
			
			<li><a href="../index.php?logout" class="logout">{TR_MENU_LOGOUT}</a></li>
		</ul>
		<ul class="path">
			<li><a>{TR_MENU_OVERVIEW}</a></li>
		</ul>
	</div>
	<div class="left_menu">{MENU}</div>
	<div class="main">
		<!-- BDP: page_message -->
		<div class="{MSG_TYPE}">{MESSAGE}</div>
		<!-- EDP: page_message -->
		<h2 class="system_vital"><span>{TR_SYSTEM_INFO}</span></h2>
		<!-- BDP: props_list -->
		<table class="description">
			<tr>
				<th style="width: 250px;">{TR_KERNEL}</th>
				<td>{KERNEL}</td>
			</tr>
			<tr>
				<th>{TR_UPTIME}</th>
				<td>{UPTIME}</td>
			</tr>
			<tr>
				<th>{TR_LOAD}</th>
				<td>{LOAD}</td>
			</tr>
		</table>
		<!-- EDP: props_list -->
		<h2 class="system_cpu"><span>{TR_CPU_SYSTEM_INFO}</span></h2>
		<table class="description">
			<tr>
				<th style="width: 250px;">{TR_CPU_MODEL}</th>
				<td>{CPU_MODEL}</td>
			</tr>
			<tr>
				<th>{TR_CPU_COUNT}</th>
				<td>{CPU_COUNT}</td>
			</tr>
			<tr>
				<th>{TR_CPU_MHZ}</th>
				<td>{CPU_MHZ}</td>
			</tr>
			<tr>
				<th>{TR_CPU_CACHE}</th>
				<td>{CPU_CACHE}</td>
			</tr>
			<tr>
				<th>{TR_CPU_BOGOMIPS}</th>
				<td>{CPU_BOGOMIPS}</td>
			</tr>
		</table>
		<h2 class="system_memory"><span>{TR_MEMRY_SYSTEM_INFO}</span></h2>
		<table>
			<tr>
				<th>{TR_RAM}</th>
				<th>{TR_TOTAL}</th>
				<th>{TR_USED}</th>
				<th>{TR_FREE}</th>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>{RAM_TOTAL}</td>
				<td>{RAM_USED}</td>
				<td>{RAM_FREE}</td>
			</tr>
			<tr>
				<th>{TR_SWAP}</th>
				<th>{TR_TOTAL}</th>
				<th>{TR_USED}</th>
				<th>{TR_FREE}</th>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>{SWAP_TOTAL}</td>
				<td>{SWAP_USED}</td>
				<td>{SWAP_FREE}</td>
			</tr>
		</table>
		<h2 class="system_filesystem"><span>{TR_FILE_SYSTEM_INFO}</span></h2>
		<!-- BDP: disk_list -->
		<table>
			<tr>
				<th>{TR_MOUNT}</th>
				<th>{TR_TYPE}</th>
				<th>{TR_PARTITION}</th>
				<th>{TR_PERCENT}</th>
				<th>{TR_FREE}</th>
				<th>{TR_USED}</th>
				<th>{TR_SIZE}</th>
			</tr>
			<!-- BDP: disk_list_item -->
			<tr>
				<td>{MOUNT}</td>
				<td>{TYPE}</td>
				<td>{PARTITION}</td>
				<td>{PERCENT} %</td>
				<td>{FREE}</td>
				<td>{USED}</td>
				<td>{SIZE}</td>
			</tr>
			<!-- EDP: disk_list_item -->
		</table>
		<!-- EDP: disk_list -->
	</div>
<!-- INCLUDE "footer.tpl" -->