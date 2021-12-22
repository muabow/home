<!-- 동작 모드 화면 -->
<div class="div_contents_table">
	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SERVER_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_server_operation_run" class="div_class_operation_run" style="display: none;">
			<span><?=Source_file_setup\Lang\STR_SERVER_OP_RUN ?></span>
		</div>

		<div id="div_server_operation_stop" class="div_class_operation_stop" style="display: none;">
			<span><?=Source_file_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div id="div_server_operation_wait" class="div_class_operation_wait" style="display: none;">
			<span><?=Source_file_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div class="div_button_wrap">
			<div id="div_button_server_stop" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_STOP ?>
			</div>

			<div id="div_button_server_start" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_START ?>
			</div>
		</div>
		<div class="div_contents_cell_line" style="margin-top: 10px;"></div>
	</div>

	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SRCFILE_TABLE ?>
	</div>

	<div class="div_contents_cell_1">
		<div class="div_title_row">
			<div class="div_title_number"> 		<?=Source_file_setup\Lang\STR_TITLE_NUMBER ?> 	</div>
			<div class="div_title_source_name"> <?=Source_file_setup\Lang\STR_TITLE_NAME ?> 	</div>
			<div class="div_title_source_type"> <?=Source_file_setup\Lang\STR_TITLE_TYPE ?> 	</div>
			<div class="div_title_source_info"> <?=Source_file_setup\Lang\STR_TITLE_INFO ?> 	</div>
			<div class="div_title_channel"> 	<?=Source_file_setup\Lang\STR_TITLE_CHANNEL ?> </div>
			<div class="div_title_play_time">	<?=Source_file_setup\Lang\STR_TITLE_PLAY_TIME ?> </div>
			<div class="div_title_loop_count">	<?=Source_file_setup\Lang\STR_TITLE_LOOP ?> 	</div>
			<div class="div_title_checkBox"><input type="checkbox" class="input_source_check_all"> 	</div>
		</div>
		<div class="div_row_wrap" id="sortable"></div>
	</div>

	<div class="div_contents_cell_line"></div>

	<div class="div_source_control_play">
		<div class="div_source_control_display" id="div_current_play_source">
		</div>
	</div>

	<div class="div_source_controller">
		<div class="div_control_button_prev"		id="control_button_prev">		</div>
		<div class="div_control_button_play"		id="control_button_play">		</div>
		<div class="div_control_button_pause"		id="control_button_pause">		</div>
		<div class="div_control_button_stop"		id="control_button_stop">		</div>
		<div class="div_control_button_next"		id="control_button_next">		</div>
		<div class="div_control_button_loop"		id="control_button_loop">		</div>
	</div>

	<div class="div_contents_title" style="padding-top: 20px;">
		<?=Source_file_setup\Lang\STR_SERVER_OP_SETUP_INFO ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category">
				<?=Source_file_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>
			<div class="div_contents_run_cell_contents">
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<label id="radio_run_protocol_label" for="radio_run_protocol" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_table_box">
						<label id="radio_run_castType_label" for="radio_run_castType" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_table_box">
						<label id="radio_run_encode_label" for="radio_run_encode" style="cursor:default;"></label>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_category" style="margin-top:10px;">
				<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" id="div_operation_view_unicast">
				<div class="div_contents_table_run_sub_title">
					<b><?=Source_file_setup\Lang\STR_SERVER_UNICAST ?>&nbsp;<?=Source_file_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>

				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_ipAddr1" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>
					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_port1" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>
				</div>
			</div>

			<div class="div_contents_cell_contents" id="div_operation_view_multicast"  style="display: none;">
				<div class="div_contents_table_run_sub_title">
					<b><?=Source_file_setup\Lang\STR_SERVER_MULTICAST ?>&nbsp;<?=Source_file_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?></b>
				</div>
				<div class="table_box_wrap">
					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_ipAddr2" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>

					<div class="div_contents_run_table_box">
						<input type="text" id="input_server_run_port2" value="-" disabled style="color: #808080; background: #ffffff;" />
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>
	</div>

	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SERVER_LIST_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="divTable">
			<div class="divTableBody">
				<div class="divTableRow">
					<div class="divTableCell">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_NUM ?>
					</div>
					<div class="divTableCell">
						<?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?>
					</div>
					<div class="divTableCell">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_HOSTNAME ?>
					</div>
					<div class="divTableCell">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_STATUS ?>
					</div>
					<div class="divTableCell">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_CONN_TIME ?>
					</div>
				</div>
			</div>
			<div class="divTableBody" id="table_server_connList">
			</div>
		</div>
	</div>
</div>
