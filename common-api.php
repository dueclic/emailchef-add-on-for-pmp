<?php

function pmproecaddon_list_ec_plugin_display( $lists, $name ) {
	$list_config = get_option( 'pmproecaddon_plugin_list_config', '' );
	?>
    <td>
        <div class="checkbox-container">
			<?php foreach ( $lists as $list ) :
				$list_name = str_replace( " ", "_", $list['name'] );
				$name_checkbox = $name . '_' . $list_name . "_checkbox";
				$is_checked = isset( $list_config[ $name_checkbox ] ) && $list_config[ $name_checkbox ] == $list['id'];
				?>
                <label class="checkbox-item">
                    <input
                            class="input-checkbox"
                            type="checkbox"
                            name="<?php echo esc_attr( $name_checkbox ); ?>"
                            value="<?php echo esc_attr( $list['id'] ); ?>"
						<?php echo $is_checked ? 'checked' : ''; ?>
                    >
					<?php echo esc_html( $list['name'] ); ?>
                </label>
			<?php endforeach; ?>
        </div>
    </td>
	<?php
}
