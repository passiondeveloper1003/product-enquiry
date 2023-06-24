<?php
namespace Wisdmforms;

class File
{

    public function control_button()
    {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="File">
            <span class="lfi lfi-name"></span> <?php _e('File', QUOTEUP_TEXT_DOMAIN) ?> 
            <a title="<?php _e('File', QUOTEUP_TEXT_DOMAIN) ?>" rel="File" class="add" data-template='File' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
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
                    <!-- <div class="form-group">
                        <label><?php _e('Validation', QUOTEUP_TEXT_DOMAIN) ?>:</label>
                        <div class="form-group">
                            <label><input type="text" name="contact[fieldsinfo][<?php echo $fieldindex ?>][filesize]" placeholder="<?php _e('File size limit', QUOTEUP_TEXT_DOMAIN) ?>" value="<?php echo (isset($field_infos[$fieldindex]['filesize']) ? $field_infos[$fieldindex]['filesize'] : '') ?>"/> <?php _e('Filesize', QUOTEUP_TEXT_DOMAIN) ?></label>
                            <label><input type="text" name="contact[fieldsinfo][<?php echo $fieldindex ?>][extensions]" placeholder="<?php _e('Allowed extensions', QUOTEUP_TEXT_DOMAIN) ?>" value="<?php echo (isset($field_infos[$fieldindex]['extensions']) ? $field_infos[$fieldindex]['extensions'] : '') ?>"/> <?php _e('Extensions', QUOTEUP_TEXT_DOMAIN) ?></label>
                        </div>
                    </div> -->
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

    public function field_preview_html($params = array())
    {
        ob_start();
        ?>
        <input type='file' disabled="disabled" name='upload[]' />
        <?php
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
        ?>
        <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <?php
            if (quoteupCFFieldsLabel($params['id'])) {
                ?>
                <label for='field' style='display: block;clear: both'><?php echo quoteupReturnCustomFormFieldLabel($params['label']); ?></label>
                <?php
            }
            ?>
            <input type='file' name='upload[<?php echo $params['id'] ?>]' <?php echo required($params); ?>/>
            <div>
                <label class="field-note"><?php echo quoteupReturnCustomFormFieldNote($params['note']); ?></label>
            </div>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template()
    {
        ob_start();
        ?>
        <script type="text/x-mustache" id="template-File">
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
                        <!-- <div class="form-group">
                            <label><?php _e('Validation', QUOTEUP_TEXT_DOMAIN) ?>:</label>
                            <div class="form-group">
                                <label><input type="text" name="contact[fieldsinfo][{{ID}}][filesize]"
                                              placeholder="<?php _e('File size limit', QUOTEUP_TEXT_DOMAIN) ?>" value=""/> <?php _e('Filesize', QUOTEUP_TEXT_DOMAIN) ?></label>
                                <label><input type="text" name="contact[fieldsinfo][{{ID}}][extensions]"
                                              placeholder="<?php _e('Allowed extensions', QUOTEUP_TEXT_DOMAIN) ?>" value=""/> <?php _e('Extensions', QUOTEUP_TEXT_DOMAIN) ?></label>
                            </div>
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
