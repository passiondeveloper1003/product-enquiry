<?php
namespace Wisdmforms;

class Radio {

    public function control_button() {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="Radio">
            <span class="lfi lfi-name"></span> <?php _e('Radio', QUOTEUP_TEXT_DOMAIN) ?> 
            <a title="<?php _e('Radio', QUOTEUP_TEXT_DOMAIN) ?>" rel="Radio" class="add" data-template='Radio' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
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
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>
                    <?php getFormLabelDiv($fieldindex, $field_infos); ?>
                    <?php getFormNoteDiv($fieldindex, $field_infos); ?>
                    <?php getFormConditionalDiv($fieldindex, $field_infos); ?>
                    <div class="form-group">
                        <label><?php _e('Options', QUOTEUP_TEXT_DOMAIN) ?></label>
                        <table class="options" id="option_<?php echo $fieldindex ?>">
                            <?php for ($i = 0; $i < count($field_infos[$fieldindex]['options']['name']); $i++): ?>
                                <tr>
                                    <td><input type="text"
                                               name="contact[fieldsinfo][<?php echo $fieldindex; ?>][options][name][]"
                                               class="form-control" placeholder="<?php _e('Name', QUOTEUP_TEXT_DOMAIN) ?>"
                                               value="<?php echo $field_infos[$fieldindex]['options']['name'][$i] ?>"/>
                                    </td>
                                    <td><input type="text"
                                               name="contact[fieldsinfo][<?php echo $fieldindex; ?>][options][value][]"
                                               class="form-control" placeholder="<?php _e('Value', QUOTEUP_TEXT_DOMAIN) ?>"
                                               value="<?php echo $field_infos[$fieldindex]['options']['value'][$i] ?>"/>
                                    </td>
                                    <td>
                                        <a href="#" class="add-option"
                                           rel="<?php echo $fieldindex ?>"><i
                                                class="glyphicon glyphicon-plus-sign pull-left"></i></a>
                                        <a href="#" class="del-option"
                                           rel="<?php echo $fieldindex ?>"><i
                                                class="glyphicon glyphicon-minus-sign pull-left"></i></a>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </div>
                    <?php getFormRequiredDiv($fieldindex, $field_infos); ?>
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
        $option_names = isset($params['options']['name']) ? $params['options']['name'] : array();
        // $option_values = isset($params['options']['value']) ? $params['options']['value'] : array();
        ob_start();
        ?>
        <div class='radiobuttons'>
        <?php
        
        foreach ($option_names as $id => $label) {
        ?>
            <label><input type='radio' disabled="disabled" value='' name='submitform[]' /> <?php echo $label ?></label>
        <?php
            unset($id);
        }
        ?>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array()) {
        $option_names = isset($params['options']['name']) ? $params['options']['name'] : array();
        $option_values = isset($params['options']['value']) ? $params['options']['value'] : array();
        ob_start();
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
        ?>
        <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <label for='field' style='display: block;clear: both'><?php echo quoteupReturnCustomFormFieldLabel($params['label']); ?></label>
            <div class='radiobuttons'>
            <?php
            foreach ($option_names as $id => $label) {
            ?>
                <label><input type='radio' value='<?php echo $option_values[$id] ?>' name='submitform[<?php echo $name; ?>]' <?php echo required($params); ?> /> <?php echo quoteupReturnCustomFormFieldLabel($label); ?></label>
            <?php
            }
            ?>
            </div>
            <div>
                <label class="field-note"><?php echo quoteupReturnCustomFormFieldNote($params['note']); ?></label>
            </div>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
        
    }

    public function configuration_template() {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-Radio">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">{{title}}</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_select_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>

            
            <div class="cog" id="cog_select_{{ID}}" style="display: none;">
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>

                    <?php getConfTempLabelDiv(); ?>
                    <?php getConfTempNoteDiv(); ?>
                    <?php getConfTempConditionalDiv(); ?>
                    <div class="form-group">
                        <label><?php _e('Options', QUOTEUP_TEXT_DOMAIN) ?></label>

                        <table class="options" id="option_{{ID}}">
                            <tr>
                                <td><input type="text" name="contact[fieldsinfo][{{ID}}][options][name][]"
                                           class="form-control" placeholder="<?php _e('Name', QUOTEUP_TEXT_DOMAIN) ?>"/></td>
                                <td><input type="text" name="contact[fieldsinfo][{{ID}}][options][value][]"
                                           class="form-control" placeholder="<?php _e('Value', QUOTEUP_TEXT_DOMAIN) ?>"/></td>
                                <td>
                                    <a href="#" class="add-option" rel="{{ID}}"><i
                                            class="glyphicon glyphicon-plus-sign pull-left"></i></a>
                                    <a href="#" class="del-option" rel="{{ID}}"><i
                                            class="glyphicon glyphicon-minus-sign pull-left"></i></a>
                                </td>
                            </tr>
                        </table>
                    </div>
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
