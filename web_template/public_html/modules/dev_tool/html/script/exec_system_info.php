<?php
    function make_header_id_key($_ip_addr) {
        $result = hash("sha256", $_ip_addr);
    
        return base64_encode($result);
    }
    
    
    function make_header_secret_key($_ip_addr) {
        $salt_key = "00:1d:1d:{$_ip_addr}";
        $result   = hash("sha256", $salt_key);
    
        return base64_encode($result);
    }
    
    function status_display_network($_network_type) {
        $json_network_info = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/network_setup/conf/network_stat.json"));
        if( $json_network_info->$_network_type->use == "enabled" ) {
            echo "";
            return ;
        }

        echo "display: none;";
        return ;
    }

    $json_network_info = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/network_setup/conf/network_stat.json"));

    $arr_network_info["primary"]   = $json_network_info->network_primary;
    $arr_network_info["secondary"] = $json_network_info->network_secondary;
    $arr_network_info["bonding"]   = $json_network_info->network_bonding;

?>

<script type="text/javascript">

</script>

<div id="system-info-div_page_title_name"> 시스템 정보 (System information) </div>

<hr class="title-hr" />

<div id="system-info-div_contents_table">
	<div class="system-info-div_contents_cell" style="flex-direction : column;">
		<div class="system-info-div_contents_cell_line">
			<div class="system-info-div_contents_cell_title">
				API Super key 정보
			</div>

            <div style="display : flex; flex-direction : column; flex: 1; overflow : hidden;">
                <div class="system-info-div_contents_cell_contents" style="flex-direction:column; <?=status_display_network("network_bonding") ?>">
                    <div id="system-info-div_page_sub_title"> - Bonding network : [<?=$arr_network_info["bonding"]->ip_address ?>] </div>
                    
                    <div class="system-info-div_contents_cell_line">
                        <div class="system-info-div_contents_cell_title">
                            Header ID         
                        </div>
                        <div class="system-info-div_contents_cell_contents">
                            <?=make_header_id_key($arr_network_info["bonding"]->ip_address) ?>
                        </div>
                    </div>
                    <div class="system-info-div_contents_cell_line">
                        <div class="system-info-div_contents_cell_title">
                            Secret Key
                        </div>
                        <div class="system-info-div_contents_cell_contents">
                            <?=make_header_secret_key($arr_network_info["bonding"]->ip_address) ?>
                        </div>
                    </div>
                </div>
                
                <div class="system-info-div_contents_cell_contents" style="flex-direction:column; <?=status_display_network("network_primary") ?>">
                    <div id="system-info-div_page_sub_title"> - Primary network : [<?=$arr_network_info["primary"]->ip_address ?>] </div>
                    
                    <div class="system-info-div_contents_cell_line">
                        <div class="system-info-div_contents_cell_title">
                            Header ID         
                        </div>
                        <div class="system-info-div_contents_cell_contents">
                            <?=make_header_id_key($arr_network_info["primary"]->ip_address) ?>
                        </div>
                    </div>
                    <div class="system-info-div_contents_cell_line">
                        <div class="system-info-div_contents_cell_title">
                            Secret Key
                        </div>
                        <div class="system-info-div_contents_cell_contents">
                            <?=make_header_secret_key($arr_network_info["primary"]->ip_address) ?>
                        </div>
                    </div>
                </div>
                
                <div class="system-info-div_contents_cell_contents" style="flex-direction:column; <?=status_display_network("network_secondary") ?>">
                    <div id="system-info-div_page_sub_title"> - Secondary network : [<?=$arr_network_info["secondary"]->ip_address ?>] </div>
                    
                    <div class="system-info-div_contents_cell_line">
                        <div class="system-info-div_contents_cell_title">
                            Header ID         
                        </div>
                        <div class="system-info-div_contents_cell_contents">
                            <?=make_header_id_key($arr_network_info["secondary"]->ip_address) ?>
                        </div>
                    </div>
                    <div class="system-info-div_contents_cell_line">
                        <div class="system-info-div_contents_cell_title">
                            Secret Key
                        </div>
                        <div class="system-info-div_contents_cell_contents">
                            <?=make_header_secret_key($arr_network_info["secondary"]->ip_address) ?>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>
