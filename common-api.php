<?php

function pmproecaddon_list_match_display(
	$lists,
	$subscription_name,
	$membership_level_lists
) {

	$subscription_name = str_replace( " ", "_", $subscription_name );

	?>
    <div class="pmproecaddon-checkbox-container">
		<?php foreach ( $lists as $list ) :
			$list_name = str_replace( " ", "_", $list['name'] );
			$name_checkbox = $subscription_name . '_' . $list_name;
			?>
            <label class="checkbox-item">
                <input
                        class="input-checkbox"
                        type="checkbox"
                        name="<?php echo esc_attr( $name_checkbox . "_checkbox" ); ?>"
                        value="<?php echo esc_attr( $list['id'] ); ?>"
					<?php checked( $membership_level_lists[ $name_checkbox ] ?? '', $list['id'] ); ?>>
				<?php echo esc_html( $list['name'] ); ?>
            </label>
		<?php endforeach; ?>
    </div>
	<?php
}
