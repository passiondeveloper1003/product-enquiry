<?php
namespace Wisdmforms;

class Location {

    public function control_button() {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="Location">
            <span class="lfi lfi-name"></span> Location 
            <a title="Location" rel="Location" class="add" data-template='Location' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
    </li>
        <?php
        // control_button_html
        return ob_get_clean();
    }

    public function field_settings($fieldindex, $fieldid, $field_infos) {
        ob_start();
?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_<?php echo $fieldindex; ?>">
            <input type="hidden" name="contact[fields][<?php echo $fieldindex ?>]" value="<?php echo $fieldid; ?>">
            <span id="label_<?php echo $fieldindex; ?>"><?php echo $field_infos[$fieldindex]['label'] ?>:</span>
            <a href="#" rel="field_<?php echo $fieldindex; ?>" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_<?php echo $fieldindex; ?>"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
            <div class="cog" id="cog_<?php echo $fieldindex; ?>" style='display: none'>
                <fieldset>
                    <h5>Settings</h5>
                    <?php getFormLabelDiv($fieldindex, $field_infos); ?>
                    <input type='hidden' name="contact[fieldsinfo][<?php echo $fieldindex ?>][location_params][set]" value="1"/>
                    <div class='form-group'>
                        <label>Accuracy</label>
                        <div class='checkboxes'>
                            <ul>
                            <li style="list-style: none"><label><input type='checkbox' value='state' name='contact[fieldsinfo][<?php echo $fieldindex ?>][location_params][]' <?php if (in_array('state',$field_infos[$fieldindex]['location_params'])) echo "checked='checked'" ?>/> State</label></li>
                            </ul>
                        </div>
                    </div>
                    <?php getFormNoteDiv($fieldindex, $field_infos); ?>
                    <?php getFormConditionalDiv($fieldindex, $field_infos); ?>
                    <!-- <div class="form-group">
                        <label>Validation:</label>
                        <select name="contact[fieldsinfo][<?php echo $fieldindex ?>][validation]" class="form-control">
                            <option value='text'>Text</option>
                        </select>
                    </div> -->
                    <div class="form-group">
                        <label><input rel="req-params" class="req" type="checkbox" name="contact[fieldsinfo][<?php echo $fieldindex ?>][required]" value="1" <?php echo (isset($field_infos[$fieldindex]['required']) ? "checked=checked" : "") ?> /> Required</label>
                        <div class="req-params" <?php echo (!isset($field_infos[$fieldindex]['required']) ? "style='display: none'" : "") ?>>
                            <input type="text"
                                   name="contact[fieldsinfo][<?php echo $fieldindex ?>][reqmsg]"
                                   placeholder="Field Required Message"
                                   value="<?php echo $field_infos[$fieldindex]['reqmsg'] ?>"
                                   class="form-control"/>
                        </div>
                    </div>
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\","",__CLASS__)."_settings",$fieldindex, $fieldid, $field_infos); ?>
                <?php do_action("form_field_settings",$fieldindex, $fieldid, $field_infos); ?>
            </div>
            <div class="field-preview">
                <?php     
                    $finfo = $field_infos[$fieldindex];
                    $finfo['id'] = $fieldindex;
                    echo self::field_preview_html($finfo);
                ?>
            </div>
        </li>
        <?php
        // field_settings_html
        return ob_get_clean();
    }

    public function field_preview_html($params = array()) {
        ob_start();
        $show_states = isset($params['location_params']) ? in_array('state', $params['location_params']) : false;
        ?>
        <div class='form-group'>
            <div class='row'>
                <div class='col-md-<?php echo ($show_states == true ? '6' : '12') ?>'>
                    <select disabled="disabled" data-placeholder='Choose a country' style='width: 100%' class='select2element' id='_selector_country' name='submitform[][country]' >
                        <option value='none'>Choose a country</option>
                    </select>
                </div>
                <?php if ($show_states == true) { ?>
                <div class='col-md-6'>
                    <select disabled="disabled" data-placeholder='Choose a state' style='width: 100%' class='select2element' id='_selector_state' name='submitform[][state]'>
                    <option value='none'>Choose a state</option>
                    </select>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array()) {
        ob_start();
        $locations_file = file_get_contents(WF_BASE_DIR . '/libs/locations.json');
        $locations_array = json_decode($locations_file, true);
        $countries = $locations_array['countries'];
        $states = $locations_array['states'];
        
        $all_countries = array();
        foreach($countries as $region => $reg_conts) {
            $all_countries = array_merge($all_countries, $reg_conts);
            unset($region);
        }
        
        $countries = json_encode($locations_array['countries']);
        $states = json_encode($locations_array['states']);
        $show_states = in_array('state', $params['location_params']);

        $condition_fields = '';
        $cond_action = '';
        $cond_boolean = '';
        if (isset($params['condition']) && isset($params['conditioned'])) {
            $cond_boolean = $params['condition']['boolean_op'];
            $cond_action = $params['condition']['action'];
            foreach($params['condition']['field'] as $key => $value) {
                $field_id = $value;
                $field_op = $params['condition']['op'][$key];
                $field_value = $params['condition']['value'][$key];
                $condition_fields .= ($field_id.':'.$field_op.':'.$field_value . '|');
            }
            $condition_fields = rtrim($condition_fields, '|');
        }
        $id = str_replace(" ", "_", $params['label']);
        $name = $params['label'];
        $location_field_id = "location_{$params['id']}".rand();
        ?>
        <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <label style="display:block; clear:both"><?php echo $params['label'] ?></label>
            <div class='form-group' >
                <div class='row'>
                    <div class='col-md-<?php echo ($show_states == true ? '6' : '12') ?>'>
                        <select data-placeholder='Choose a country' style='width: 100%' class='select select2element' id='<?php echo $location_field_id?>_selector_country' name='submitform[<?php echo $name ?> country]' <?php echo required($params) ?>>
                            <option value='none'>Choose a country</option>
                            <?php
                            foreach($all_countries as $country) { ?>
                            <option value='<?php echo $country ?>'><?php echo $country ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php if ($show_states == true) { ?>
                    <div class='col-md-6'>
                        <select data-placeholder='Choose a state' style='width: 100%' class='select2element' id='<?php echo $params['id'] ?>_selector_state' name='submitform[<?php echo $name ?> state]' <?php required($params) ?>>
                        <option value='none'>Choose a state</option>
                        </select>
                    </div>
                    <?php } ?>
                </div>
                <div class='hidden' id='wisdmform_json_countries'><?php echo $countries ?></div>
                <?php if ($show_states == true) { ?>
                <div class='hidden' id='wisdmform_json_states'><?php echo $states ?></div>
                <script type='text/javascript'>
                    jQuery('.select#<?php echo $location_field_id ?>_selector_country').on('change',function(){
                        var sel_country = jQuery(this).val();
                        var json_state = JSON.parse(jQuery('#wisdmform_json_states').html());
                        json_state['none'] = [];
                        jQuery('#<?php echo $params['id']?>_selector_state').html(get_selections(json_state[sel_country]));
                    });
                    function get_selections(states) {
                        options_html = '<option selected=\'selected\' value=\'\'>Choose a state</option>';
                        for (i = 0 ; i<states.length ; i++) {
                            options_html += ('<option value=\''+states[i]+'\'>'+states[i]+'</option>')
                        };
                        return options_html;
                    };
                </script>
                <?php } ?>
                <div>
                    <label class="field-note"><?php echo $params['note'] ?></label>
                </div>
            </div>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template() {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-Location">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">{{title}}</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
            <div class="cog" id="cog_{{ID}}" style="display: none;">
                <fieldset>
                    <h5>Settings</h5>

                    <?php getConfTempLabelDiv(); ?>
                    <input type='hidden' name="contact[fieldsinfo][{{ID}}][location_params][set]" value="1"/>
                    <div class='form-group'>
                        <label>Accuracy</label>
                        <div class='checkboxes'>
                            <ul>
                            <li style="list-style: none"><label><input type='checkbox' value='state' name='contact[fieldsinfo][{{ID}}][location_params][]' /> State</label></li>
                            </ul>
                        </div>
                    </div>
                    <?php getConfTempNoteDiv(); ?>
                    <?php getConfTempConditionalDiv(); ?>
                    <!-- <div class="form-group">
                        <label>Validation:</label>
                        <select name="contact[fieldsinfo][{{ID}}][validation]" class="form-control">
                            <option value='text'>Text</option>
                        </select>
                    </div> -->
                    <?php getConfTempRequiredDiv(); ?>
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\","",__CLASS__)."_settings_template"); ?>
                <?php do_action("form_field_settings_template"); ?>
            </div>
            <div class="field-preview">
                <?php echo self::field_preview_html() ?>
            </div>
        </li>
    </script>
        <?php
        // field_configuration_template
        return ob_get_clean();
    }

    function process_field() {
        
    }

}
