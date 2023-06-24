<?php
namespace Wisdmforms;

class Text
{
    public function control_button()
    {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="Text">
            <span class="lfi lfi-name"></span> <?php _e('Text', QUOTEUP_TEXT_DOMAIN) ?> 
            <a title="<?php _e('Text', QUOTEUP_TEXT_DOMAIN) ?>" rel="Text" class="add" data-template='Text' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
    </li>
        <?php
        // control_button_html
        return ob_get_clean();
    }

    public function field_settings($fieldindex, $fieldid, $field_infos)
    {
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
                        <label><?php _e('Validation', QUOTEUP_TEXT_DOMAIN) ?>:</label>
                        <select name="contact[fieldsinfo][<?php echo $fieldindex ?>][validation]" class="form-control">
                            <?php
                            $validation_ops = get_validation_ops();
                            foreach ($validation_ops as $value => $text) {
                                echo '<option value="' . $value . '" ' . ($field_infos[$fieldindex]['validation'] == $value ? 'selected="selected "' : "") . '>' . $text . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?php getFormRequiredDiv($fieldindex, $field_infos); ?>
                    <label><?php _e('Icon', QUOTEUP_TEXT_DOMAIN) ?></label>
                    <div class="row">
                        <div class="form-group col-md-6">
                           <select name="contact[fieldsinfo][<?php echo $fieldindex ?>][icon-pos][op]" class="form-control icontext" id="icontext" data-selection='<?php echo isset($field_infos[$fieldindex]['icon-pos']['op']) ? $field_infos[$fieldindex]['icon-pos']['op'] : ''  ?>'>
                               <option value="no icon"><?php _e('No icon', QUOTEUP_TEXT_DOMAIN) ?></option>
                               <option value="before"><?php _e('Before', QUOTEUP_TEXT_DOMAIN) ?></option>
                               <option value="after"><?php _e('After', QUOTEUP_TEXT_DOMAIN) ?></option>
                           </select>
                        </div>
                        <div class="form-group col-md-6 hide icon-code-div">
                            <select name="contact[fieldsinfo][<?php echo $fieldindex ?>][icon-code]" data-selection='<?php echo isset($field_infos[$fieldindex]['icon-code']) ? $field_infos[$fieldindex]['icon-code'] : ''  ?>' class="form-control icon-code" id="icon-code">
                               <option value="no icon"><?php _e('Select your Icon', QUOTEUP_TEXT_DOMAIN) ?></option>
                               <?php 
                                  $fa = new \FontAweome();
                                  echo $fa->html();
                               ?>
                           </select>
                        </div>
                    </div>
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\","",__CLASS__)."_settings",$fieldindex, $fieldid, $field_infos); ?>
                <?php do_action("form_field_settings",$fieldindex, $fieldid, $field_infos); ?>
            </div>
            <div class="field-preview" id="found">
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

    public function field_preview_html($params = array())
    {
        ob_start();
        if(isset($params['icon-pos']) && $params['icon-pos']['op'] != 'no icon')
        {
            if($params['icon-pos']['op'] == 'before')
            {
                ?>
                <div class="input-group">
                    <span class="input-group-addon" id=""><i class='<?php echo isset($params['icon-code']) ? $params['icon-code'] : ''; ?>'></i></span>
                    <input type='text' disabled="disabled" name='submitform[]' class='form-control' value='' aria-describedby="" />
                </div>
        <?php 
            } 
            if($params['icon-pos']['op'] == 'after')
            {
                ?>
                <div class="input-group">
                    <input type='text' disabled="disabled" name='submitform[]' class='form-control' value='' aria-describedby="" />
                    <span class="input-group-addon" id=""><i class='<?php echo isset($params['icon-code']) ? $params['icon-code'] : ''; ?>'></i></span>
                </div>
            <?php 
            } 
        } else {
            ?>
         <input type='text' disabled="disabled" name='submitform[]' class='form-control' value='' aria-describedby="" />
        
        <?php
        }

          if(!isset($params['icon-code'])) {
        ?>
         <input type='text' disabled="disabled" name='submitform[]' class='form-control' value='' aria-describedby="" />
        <?php      
          }
         
        // field_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array())
    {
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

        if(isset($params['icon-pos']['op']) && isset($params['icon-code']) && $params['icon-pos']['op'] != 'no icon'):
        ?>
            <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?> input-group' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>">
                <?php
                if (quoteupCFFieldsLabel($params['id'])) {
                    ?>
                    <label for='field' style='display: block;clear: both'><?php echo quoteupReturnCustomFormFieldLabel($params['label']); ?></label>
                    <?php 
                }
                ?>                    
                <?php if($params['icon-pos']['op'] == 'before'):?>
                <span class="input-group-addon" id=""><i class='<?php echo isset($params['icon-code']) ? $params['icon-code'] : ''; ?>'></i></span>
                <?php endif; ?>
                <input type='text' name='submitform[<?php echo $name ?>]' class='form-control' value='' <?php echo required($params); ?>  aria-describedby="" />
                <?php if($params['icon-pos']['op'] == 'after'):?>
                <span class="input-group-addon" id=""><i class='<?php echo isset($params['icon-code']) ? $params['icon-code'] : ''; ?>'></i></span>
                <?php endif; ?>
                <div>
                    <label class="field-note"><?php echo quoteupReturnCustomFormFieldNote($params['note']); ?></label>
                </div>
            </div>
        <?php else:?>
        <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <?php
            if (quoteupCFFieldsLabel($params['id'])) {
                ?>
                <label for='field' style='display: block;clear: both'><?php echo quoteupReturnCustomFormFieldLabel($params['label']); ?></label>
                <?php
            }
            ?>
            <input type='text'  name='submitform[<?php echo $name ?>]' class='form-control' placeholder='<?php echo esc_attr(quoteupReturnCustomFormFieldPlaceholder($params['label'])); ?>' value='' <?php echo required($params)?> />
            <div>
                <label class="field-note"><?php echo quoteupReturnCustomFormFieldNote($params['note']); ?></label>
            </div>
        </div>
        <?php endif; ?>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template()
    {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-Text">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">{{title}}</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>

            
            <div class="cog" id="cog_{{ID}}" style="display: none;">
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>

                    <?php getConfTempLabelDiv(); ?>
                    <?php getConfTempNoteDiv(); ?>
                    <?php getConfTempConditionalDiv(); ?>
                    <div class="form-group">
                        <label><?php _e('Validation', QUOTEUP_TEXT_DOMAIN) ?>:</label>
                        <select name="contact[fieldsinfo][{{ID}}][validation]" class="form-control">
                        <?php foreach(get_validation_ops() as $op => $label) { ?>
                            <option value="<?php echo $op ?>"><?php echo $label ?></option>
                        <?php } ?>
                        </select>
                    </div>
                    <?php getConfTempRequiredDiv(); ?>
                    <label>Icon:</label>
                    <div class="row">
                        <div class="form-group col-md-6">
                           <select name="contact[fieldsinfo][{{ID}}][icon-pos][op]" class="form-control icontext" id="icontext">
                               <option value="no icon"><?php _e('No icon', QUOTEUP_TEXT_DOMAIN) ?></option>
                               <option value="before"><?php _e('Before', QUOTEUP_TEXT_DOMAIN) ?></option>
                               <option value="after"><?php _e('After', QUOTEUP_TEXT_DOMAIN) ?></option>
                           </select>
                        </div>
                        <div class="form-group col-md-6 hide icon-code-div">
                            <select name="contact[fieldsinfo][{{ID}}][icon-code]" class="form-control icon-code" id="icon-code">
                               <option value="no icon"><?php _e('Select your Icon', QUOTEUP_TEXT_DOMAIN) ?></option>
                               <?php 
                                  $fa = new \FontAweome();
                                  echo $fa->html();
                               ?>
                           </select>
                        </div>
                    </div>
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\","",__CLASS__)."_settings_template"); ?>
                <?php do_action("form_field_settings_template"); ?>
            </div>
            <div class="field-preview" id="found">
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
