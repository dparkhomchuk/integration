\<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 21.10.2018
 * Time: 20:30
 */

class BpmonlineFormidableformsMapping
{
	public static function get_my_new_settings ($values) {
		$form_fields = FrmField::getAll('fi.form_id='. (int) $values['id'] ." and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order');
		$my_form_opts = maybe_unserialize( get_option('frm_mysettings_' . $values['id']) );
		?>
		<table class="form-table">
			<tr>
				<td width="200px">
					<label>Select landing type.</label>
				</td>
				<td>
					<select name="frm_mysettings[firstopt]">
						<option value=""><?php _e( '— Select —' ); ?></option>
						<?php foreach ( $form_fields as $form_field ) {
							$selected = ( isset( $my_form_opts['firstopt'] ) && $my_form_opts['firstopt'] == $form_field->id ) ? ' selected="selected"' : '';
							?>
							<option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo FrmAppHelper::truncate( $form_field->name, 40 ) ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}
}