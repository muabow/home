<div class="div_contents_table" id="div_display_server_setup">
	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SERVER_SETUP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_PROTOCOL ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input type="radio" name="radio_protocolType" id="radio_protocol_tcp" class="input_class_radio" checked />
					<label class="label_class_radio" for="radio_protocol_tcp">TCP/IP</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_CAST_TYPE ?>
			</div>
			<div class="div_contents_cell_contents">
				<div class="div_contents_radio_wrap">
					<input type="checkbox" name="checkbox_cast_type" id="checkbox_cast_unicast" value="unicast" class="input_class_radio" />
					<label class="label_class_radio" for="checkbox_cast_unicast">Unicast</label>
				</div>

				<div class="div_contents_radio_wrap" <?=$server_handler->set_enable_multicast() ?>>
					<input type="checkbox" name="checkbox_cast_type" id="checkbox_cast_multicast" value="multicast" class="input_class_radio" />
					<label class="label_class_radio" for="checkbox_cast_multicast">Multicast</label>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line">
			<div class="div_contents_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" style="padding: 0px 0px 10px 0px;">
				<div class="div_contents_cell_row_wrap">
					<div class="div_contents_cell_row_wrap">
						<div class="div_contents_table_sub_title">
							<b><?=Source_file_setup\Lang\STR_SERVER_OPER_SETUP ?></b>
						</div>

						<div class="div_contents_cell_column_wrap" id="div_mcast_ip_addr" style="display: none;">
							<div class="div_contents_table_text_wrap">
								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_IP_M_ADDR ?>
								</div>
							</div>

							<div class="div_contents_table_box_wrap">
								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<label for="input_server_ip_addr"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
										<input type="text" id="input_server_ip_addr"/>
									</div>
								</div>
							</div>
						</div>

						<div class="div_contents_cell_column_wrap">
							<div class="div_contents_table_text_wrap">
								<div class="div_contents_table_text">
									<?=Source_file_setup\Lang\STR_COMMON_PORT ?>
								</div>
							</div>

							<div class="div_contents_table_box_wrap">
								<div class="div_contents_table_box">
									<div class="div_contents_textbox">
										<label for="input_server_port"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
										<input type="text" id="input_server_port"/>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_class_button_wrap">
			<div id="div_button_server_cancel" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_CANCEL ?>
			</div>
			<div id="div_button_server_apply" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_APPLY ?>
			</div>
		</div>
	</div>
</div>



<!-- 동작 모드 화면 -->
<div class="div_contents_table" id="div_display_server_operation">
	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SERVER_OP_TITLE ?>
	</div>
	<div class="div_contents_cell">
		<div id="div_server_operation_wait" class="div_class_operation_wait" >
			<span></span>
		</div>
		<div id="div_server_operation_run" class="div_class_operation_run" style="display: none;">
			<span><?=Source_file_setup\Lang\STR_SERVER_OP_RUN ?></span>
		</div>

		<div id="div_server_operation_stop" class="div_class_operation_stop" style="display: none;">
			<span><?=Source_file_setup\Lang\STR_SERVER_OP_STOP ?></span>
		</div>

		<div class="div_contents_cell_line" style="margin-top: 10px; border-top: 1px dashed #cccccc;"></div>
		<div class="div_class_button_wrap">
			<div id="div_button_server_setup" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_SETUP ?>
			</div>
			<div id="div_button_server_stop" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_STOP ?>
			</div>
			<div id="div_button_server_start" class="div_class_button">
				<?=Source_file_setup\Lang\STR_COMMON_START ?>
			</div>
		</div>
	</div>


	<div id="div_content_table_source_list">
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
				<div class="div_title_checkBox" style="margin-right: 17px;"><input type="checkbox" class="input_source_check_all"> 	</div>
			</div>
			<div class="div_row_wrap" id="sortable"></div>
		</div>

		<div class="div_contents_cell_line"></div>

		<div class="div_source_control_play">
			<div class="div_source_control_display">
				<div class="div_source_control_marquee">
					<div id="div_current_play_source"></div>
				</div>
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
	</div>

	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SERVER_OP_SETUP_INFO ?>
	</div>
	<div class="div_contents_cell">
		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_PROTOCOL_INFO ?>
			</div>

			<div class="div_contents_cell_row_wrap">
				<div class="div_contents_cell_column_wrap">
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Source_file_setup\Lang\STR_SERVER_PROTOCOL ?>
					</div>
					<div class="div_radio_double_wrap_title">
						|&nbsp;<?=Source_file_setup\Lang\STR_SERVER_CAST_TYPE ?>
					</div>
					<div class="div_radio_double_wrap_title">
					</div>
				</div>

				<div class="div_contents_cell_column_wrap">
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_protocol" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_protocol_label" for="radio_run_protocol" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_cell_contents">
						<input type="radio" id="radio_run_castType" class="input_class_radio" checked />
						<label class="label_class_radio" id="radio_run_castType_label" for="radio_run_castType" style="cursor:default;">-</label>
					</div>
					<div class="div_contents_run_cell_contents">
					</div>
				</div>
			</div>
		</div>


		<div class="div_contents_cell_line">
			<div class="div_contents_run_cell_title">
				<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?>
			</div>
			<div class="div_contents_cell_contents" style="padding-bottom: 0px;">
				<div class="div_contents_cell_contents">
					<div id="div_operation_view_unicast">
						<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
							<b><?=Source_file_setup\Lang\STR_SERVER_UNICAST ?>&nbsp;<?=Source_file_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?></b>
						</div>
						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_ipAddr"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_server_run_ipAddr1" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>
						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_port"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
								<input type="text" id="input_server_run_port1" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>
					</div>

					<div id="div_operation_view_multicast" style="display: none;">
						<div class="div_contents_table_run_sub_title" style="margin-top: 10px;">
							<b><?=Source_file_setup\Lang\STR_SERVER_MULTICAST ?>&nbsp;<?=Source_file_setup\Lang\STR_COMMON_SERVER ?>&nbsp;<?=Source_file_setup\Lang\STR_SERVER_OPER_INFO ?></b>
						</div>

						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_ipAddr"><?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?></label>
								<input type="text" id="input_server_run_ipAddr2" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>

						<div class="div_contents_run_table_box">
							<div class="div_contents_textbox">
								<label for="input_server_run_port"><?=Source_file_setup\Lang\STR_COMMON_PORT ?></label>
								<input type="text" id="input_server_run_port2" value="-" disabled style="color: #808080; background: #ffffff;" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="div_contents_cell_line"></div>
	</div>

	<div class="div_contents_title">
		<?=Source_file_setup\Lang\STR_SERVER_LIST_TITLE ?>
	</div>
	<div class="div_contents_cell" style="margin-left: 10px;">
		<div class="div_no_support_for_multicast">
			<?=Source_file_setup\Lang\STR_SERVER_LIST_NOTICE ?>
		</div>
		<div class="divTable">
			<div class="divTableBody">
				<div class="divTableRow">
					<div class="divTableCell" style="width: 70px;">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_NUM ?>
					</div>
					<div class="divTableCell" style="width: 160px;">
						<?=Source_file_setup\Lang\STR_COMMON_IP_ADDR ?>
					</div>
					<div class="divTableCell" style="width: 230px;">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_HOSTNAME ?>
					</div>
					<div class="divTableCell" style="width: 100px;">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_STATUS ?>
					</div>
					<div class="divTableCell" style="width: 180px;">
						<?=Source_file_setup\Lang\STR_SERVER_LIST_CONN_TIME ?>
					</div>
				</div>
			</div>
			<div class="divTableBody" id="table_server_connList">
			</div>
		</div>
	</div>
</div>
