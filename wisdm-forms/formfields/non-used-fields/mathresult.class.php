<?php
namespace Wisdmforms;

class Mathresult {


    public function control_button() {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="Mathresult">
            <span class="lfi lfi-name"></span> Math result 
            <a title="Mathresult" rel="Mathresult" class="add" data-template='Mathresult' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
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
                    <div class='form-group'>
                        <label>Field 1</label>
                        <select name='contact[fieldsinfo][<?php echo $fieldindex?>][fields][0]' data-selection='<?php echo $field_infos[$fieldindex]['fields'][0] ?>' class='form-control math-field-variable math-field-variable-<?php echo $fieldindex ?>'>
                            <option value=''>Select first field</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <label>Field 2</label>
                        <select name='contact[fieldsinfo][<?php echo $fieldindex?>][fields][1]' data-selection='<?php echo $field_infos[$fieldindex]['fields'][1] ?>'  class='form-control math-field-variable math-field-variable-<?php echo $fieldindex?>'>
                            <option value=''>Select second field</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <label>Operator</label>
                        <select name='contact[fieldsinfo][<?php echo $fieldindex ?>][operator]' class='form-control math-field-operator'>
                            <option <?php if ($field_infos[$fieldindex]['operator'] == '') echo 'selected="selected"' ?> value=''>Select an operator</option>
                            <option <?php if ($field_infos[$fieldindex]['operator'] == 'add') echo 'selected="selected"' ?> value='add'>Add</option>
                            <option <?php if ($field_infos[$fieldindex]['operator'] == 'substract') echo 'selected="selected"' ?> value='substract'>Substract</option>
                            <option <?php if ($field_infos[$fieldindex]['operator'] == 'multiply') echo 'selected="selected"' ?> value='multiply'>Multiply</option>
                            <option <?php if ($field_infos[$fieldindex]['operator'] == 'divide') echo 'selected="selected"' ?> value='divide'>Divide</option>
                        </select>
                    </div>
                    <?php getFormNoteDiv($fieldindex, $field_infos); ?>
                    <?php getFormConditionalDiv($fieldindex, $field_infos); ?>
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
        ?>
        Number field sum
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array()) {
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
        ?>
        <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <label style="display:block; clear:both"><?php echo $params['label'] ?></label>
            <div class='form-group' id='mathresult_<?php echo $params['id'] ?>' ></div>
            <div>
                <label class="field-note"><?php echo $params['note'] ?></label>
            </div>
        </div>
        <script type='text/javascript'>
            jQuery(document).ready(function($){
                var math_op = '<?php echo $params['operator'] ?>';
                jQuery('#<?php echo $params['fields'][0] ?>').on('keyup', function($){
                    var mathres = 0;
                    var field1 = jQuery('#<?php echo $params['fields'][0] ?> input[type=text]');
                    var field2 = jQuery('#<?php echo $params['fields'][1] ?> input[type=text]');
                    if (field1.length > 0 && field2.length > 0) {
                        val1 = parseInt(jQuery(field1).val());
                        val2 = parseInt(jQuery(field2).val());
                        switch (math_op) {
                            case 'add':
                                mathres = val1 + val2;
                                break;
                            case 'substract':
                                mathres = val1 - val2;
                                break;
                            case 'multiply':
                                mathres = val1 * val2;
                                break;
                            case 'divide':
                                mathres = val1 / val2;
                                break;
                        }
                    }
                    jQuery('#mathresult_<?php echo $params['id'] ?>').html(mathres);
                });
                jQuery('#<?php echo $params['fields'][1] ?>').on('keyup', function($){
                    var mathres = 0;
                    var field1 = jQuery('#<?php echo $params['fields'][0] ?> input[type=text]');
                    var field2 = jQuery('#<?php echo $params['fields'][1] ?> input[type=text]');
                    if (field1.length > 0 && field2.length > 0) {
                        val1 = parseInt(jQuery(field1).val());
                        val2 = parseInt(jQuery(field2).val());
                        switch (math_op) {
                            case 'add':
                                mathres = val1 + val2;
                                break;
                            case 'substract':
                                mathres = val1 - val2;
                                break;
                            case 'multiply':
                                mathres = val1 * val2;
                                break;
                            case 'divide':
                                mathres = val1 / val2;
                                break;
                        }

                    }
                    jQuery('#mathresult_<?php echo $params['id'] ?>').html(mathres);
                });
            });
        </script>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template() {
        ob_start();
        ?>
    <script type='text/x-mustache' id="template-Mathresult">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">Math result</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>

            
            <div class="cog" id="cog_{{ID}}" style="display: none;">
                <fieldset>
                    <h5>Settings</h5>
                    <div class="form-group">
                        <label>Label:</label>
                        <input class="form-control form-field-label" data-target="#label_{{ID}}" type="text"
                               value="Math result"
                               name="contact[fieldsinfo][{{ID}}][label]"/>
                    </div>
                    <div class='form-group'>
                        <label>Field 1</label>
                        <select name='contact[fieldsinfo][{{ID}}][fields][0]' data-selection=''  class='form-control math-field-variable'>
                            <option value=''>Select first field</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <label>Field 2</label>
                        <select name='contact[fieldsinfo][{{ID}}][fields][1]' data-selection='' class='form-control math-field-variable'>
                            <option value=''>Select second field</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <label>Operator</label>
                        <select name='contact[fieldsinfo][{{ID}}][operator]' class='form-control math-field-operator'>
                            <option value=''>Select an operator</option>
                            <option value='add'>Add</option>
                            <option value='substract'>Substract</option>
                            <option value='multiply'>Multiply</option>
                            <option value='divide'>Divide</option>
                        </select>
                    </div>
                    <?php getConfTempNoteDiv(); ?>
                    <?php getConfTempConditionalDiv(); ?>
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\","",__CLASS__)."_settings_template"); ?>
                <?php do_action("form_field_settings_template"); ?>
            </div>
            <div class="field-preview">
                <?php echo self::field_preview_html() ?>
            </div>
        </li>
    </script>
    <script type='text/javascript'>
        jQuery(document).ready(function($){
            var form_selections = "<option value=''><?php _e('Selected Field', QUOTEUP_TEXT_DOMAIN) ?></option>";
            $('#selectedfields-list .fl-field').each(function(){
                this_id = $(this).attr('id');
                if (this_id != '{{INDEX}}' && $('#'+this_id+'_CLASS').attr('rel') == 'Number') {
                    form_selections +=     "<option value='"+this_id+"'>"+$('#'+this_id+'_LABEL').attr('rel')+"</option>";
                }
            });
            $('.math-field-variable').each(function () {
                $(this).html(form_selections);
                var preselected_var = $(this).attr('data-selection');
                $(this).children().each(function(){
                    if ($(this).attr('value') == preselected_var)
                        $(this).attr('selected', 'selected');
                });
            });
            $('#selectedfields-list').bind('DOMNodeInserted DOMNodeRemoved DOMSubtreeModified', throttle( function(){
                var form_selections = "<option value=''><?php _e('Selected Field', QUOTEUP_TEXT_DOMAIN) ?></option>";
                $('#selectedfields-list .fl-field').each(function(){
                    this_id = $(this).attr('id');
                    if (this_id != '{{INDEX}}' && $('#'+this_id+'_CLASS').attr('rel') == 'Number') {
                        form_selections +=     "<option value='"+this_id+"'>"+$('#'+this_id+'_LABEL').attr('rel')+"</option>";
                    }
                });
                $('.math-field-variable').each(function () {
                    $(this).html(form_selections);
                    var preselected_var = $(this).attr('data-selection');
                    $(this).children().each(function(){
                        if ($(this).attr('value') == preselected_var)
                            $(this).attr('selected', 'selected');
                    });
                });
            }, 50));
        });
    </script>
        <?php
        // field_configuration_template
        return ob_get_clean();
    }

    function process_field() {
        
    }

}
