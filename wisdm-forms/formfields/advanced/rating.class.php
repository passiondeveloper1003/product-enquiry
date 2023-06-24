<?php
namespace Wisdmforms;

class Rating
{

    public function control_button()
    {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="Rating">
            <span class="lfi lfi-name"></span> <?php _e('Rating', QUOTEUP_TEXT_DOMAIN) ?> 
            <a title="<?php _e('Rating', QUOTEUP_TEXT_DOMAIN) ?>" rel="Rating" class="add" data-template='Rating' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
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

                    <div class='form-group'>
                        <label><?php _e('Configurations', QUOTEUP_TEXT_DOMAIN) ?></label>
                        <div class='row'>
                            <div class='col-md-6'>
                                <input type='text' class='form-control' value='<?php echo $field_infos[$fieldindex]['max_rating'] ?>' placeholder='<?php _e('Maximum rating', QUOTEUP_TEXT_DOMAIN) ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][max_rating]' />
                            </div>
                            <div class='col-md-6'>
                                <input type='text' class='form-control' value='<?php echo $field_infos[$fieldindex]['rating_steps'] ?>' placeholder='<?php _e('Rating steps', QUOTEUP_TEXT_DOMAIN) ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][rating_steps]' />
                            </div>
                        </div>
                    </div>
                    <?php getFormNoteDiv($fieldindex, $field_infos); ?>
                    <?php getFormConditionalDiv($fieldindex, $field_infos); ?>
                    <?php getFormRequiredDiv($fieldindex, $field_infos); ?>
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\","",__CLASS__)."_settings",$fieldindex, $fieldid, $field_infos); ?>
                <?php do_action("form_field_settings",$fieldindex, $fieldid, $field_infos); ?>
            </div>
            <div class="field-preview">
                <script type='text/javascript'>
                    jQuery(document).ready(function () { jQuery('.very_rand_rating_div').rateit({ max: 5, step: 2, backingfld: '#very_rand_rating_field' }); });
                </script>
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

        <input type='hidden' disabled="disabled" name='submitform[]' id='very_rand_rating_field' />
        <div class='form-group'>
            <div id='very_rand_rating_div' class="very_rand_rating_div"></div>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array())
    {
        ob_start();
        $max_rating = empty($params['max_rating']) ? '0' : $params['max_rating'];
        $rating_steps = empty($params['rating_steps']) ? '0' : $params['rating_steps'];
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
        $rating_field_id = $params['id'];
        $rating_div_field_id = $params['id'].rand();
        ?>
        <div id="<?php echo $rating_field_id ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <?php
            if (quoteupCFFieldsLabel($params['id'])) {
                ?>
                <label for='field' style='display: block;clear: both'><?php echo quoteupReturnCustomFormFieldLabel($params['label']); ?></label>
                <?php
            }
            ?>            
            <input type='hidden' name='submitform[<?php echo $name ?>]' id='<?php echo $rating_div_field_id ?>_rating_field' <?php echo required($params); ?>/>
            <div class='form-group'>
                <div id='<?php echo $rating_div_field_id?>_rating_div'></div>
                <div>
                    <label class="field-note"><?php echo quoteupReturnCustomFormFieldNote($params['note']); ?></label>
                </div>
            </div>
        </div>
        <script type='text/javascript'>
            jQuery(function () { jQuery('#<?php echo $rating_div_field_id?>_rating_div').rateit({ max: <?php echo $max_rating ?>, step: <?php echo $rating_steps ?>, backingfld: '#<?php echo $rating_div_field_id ?>_rating_field' }); });
        </script>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template()
    {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-Rating">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">{{title}}</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
    
            <div class="cog" id="cog_{{ID}}" style="display: none;">
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>

                    <?php getConfTempLabelDiv(); ?>
                    <div class='form-group'>
                        <label><?php _e('Configurations', QUOTEUP_TEXT_DOMAIN) ?></label>
                        <div class='row'>
                            <div class='col-md-6'>
                                <input type='text' class='form-control' value='' placeholder='<?php _e('Maximum rating', QUOTEUP_TEXT_DOMAIN) ?>' name='contact[fieldsinfo][{{ID}}][max_rating]' />
                            </div>
                            <div class='col-md-6'>
                                <input type='text' class='form-control' value='' placeholder='<?php _e('Rating steps', QUOTEUP_TEXT_DOMAIN) ?>' name='contact[fieldsinfo][{{ID}}][rating_steps]' />
                            </div>
                        </div>
                    </div>
                    <?php getConfTempNoteDiv(); ?>
                    <?php getConfTempConditionalDiv(); ?>
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
