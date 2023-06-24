<?php
namespace Wisdmforms;

class Captcha
{
    public function control_button()
    {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\", "", __CLASS__) ?>" for="Captcha">
            <span class="lfi lfi-name"></span> <?php _e('Captcha', QUOTEUP_TEXT_DOMAIN) ?> 
            <a title="<?php _e('Captcha', QUOTEUP_TEXT_DOMAIN) ?>" rel="Captcha" class="add" data-template='Captcha' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
    </li>
        <?php
        // control_button_html
        return ob_get_clean();
    }

    public function field_settings($fieldindex, $fieldid, $field_infos)
    {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\", "", __CLASS__) ?>"  id="field_<?php echo $fieldindex; ?>">
            <input type="hidden" name="contact[fields][<?php echo $fieldindex ?>]" value="<?php echo $fieldid; ?>">
            <span id="label_<?php echo $fieldindex; ?>"><?php echo $field_infos[$fieldindex]['label'] ?>:</span>
            <a href="#" rel="field_<?php echo $fieldindex; ?>" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_<?php echo $fieldindex; ?>"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
            <div class="cog" id="cog_<?php echo $fieldindex; ?>" style='display: none'>
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>
                    <?php getFormLabelDiv($fieldindex, $field_infos); ?>
                    <?php getFormNoteDiv($fieldindex, $field_infos); ?>
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\", "", __CLASS__)."_settings", $fieldindex, $fieldid, $field_infos); ?>
                <?php do_action("form_field_settings", $fieldindex, $fieldid, $field_infos); ?>
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

        <div class='form-group' >
            <div class="row">
                <div class="col-md-12 row-bottom-buffer">
                    <img src=""/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <input type="text" disabled="disabled" placeholder="<?php _e('Enter captcha', QUOTEUP_TEXT_DOMAIN) ?>" class="form-control" required="required" data-required-message="" id="recaptcha_entry" name="recaptcha_entry"/>
                </div>
            </div>
        </div>
        <?php
        // field_preview_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array())
    {
        if (isset($_GET['page']) && $_GET['page'] =='quoteup-create-quote') {
            return;
        }

        static $isCaptcha3DataLocalized = false;
        $formSetting                    = quoteupSettings();
        $siteKey                        = $formSetting['google_site_key'];
        $lang_query_var                 = quoteupIsWpmlActive() ? '&hl='.ICL_LANGUAGE_CODE : '';

        // If captcha version 3.
        if (quoteupIsCaptchaVersion3($formSetting)) {
            if ($isCaptcha3DataLocalized) {
                return;
            }
            wp_enqueue_script('quoteup-google-captcha', 'https://www.google.com/recaptcha/api.js?render='.$siteKey.$lang_query_var, array(), QUOTEUP_VERSION, true);
            wp_localize_script(
                'quoteup-google-captcha', 'quoteup_captcha_data', array(
                'captcha_version'   => 'v3',
                'site_key'          =>  $siteKey,
                )
            );
            $isCaptcha3DataLocalized = true;
            return;
        }

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
        $captcha_field_id = "test_{$params['id']}".rand();
        ?>
        <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <?php
            if (quoteupCFFieldsLabel($params['id'])) {
                ?>
                <label for='field' style='display: block;clear: both'><?php echo quoteupReturnCustomFormFieldLabel($params['label']); ?></label>
                <?php
            }
            ?>
            <?php
            $scriptvar = '<script src="https://www.google.com/recaptcha/api.js?render=explicit'.$lang_query_var.'" async defer></script>';
            echo $scriptvar;
            self::loadCaptchaJs($params, $siteKey);
            ?>
            <div class='form-group' >
                <div id="<?php echo $captcha_field_id ?>" class="g-recaptcha" data-sitekey="<?php echo $siteKey ?>" data-widgetid="">
                </div>
            </div>
            <div>
                <label class="field-note"><?php echo quoteupReturnCustomFormFieldNote($params['note']); ?></label>
            </div>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public static function loadCaptchaJs($params, $siteKey)
    {
        ?>
        <script type='text/javascript'>
            var CaptchaCallback = function() {
                jQuery('.g-recaptcha').each(function(index, el) {
                    var id = jQuery(this).attr('id');
                    var widgetID = grecaptcha.render(id, {'sitekey' : '<?php echo $siteKey; ?>', 'callback' : correctCaptcha_quote});
                    jQuery('#'+id).attr('data-widgetID', widgetID);
                });
            };
            
            window.onload = function () {
                CaptchaCallback();
            };

            var correctCaptcha_quote = function(response) {
                jQuery('.wdmHiddenRecaptcha').val(response);
            };
        </script>
        <?php
    }

    public function configuration_template()
    {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-Captcha">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\", "", __CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">{{title}}</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>

            <div class="cog" id="cog_{{ID}}" style="display: none;">
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>

                    <?php getConfTempLabelDiv(); ?>
                    <?php getConfTempNoteDiv(); ?>                    
                </fieldset>
                <?php do_action("form_field_".str_replace("Wisdmforms\\", "", __CLASS__)."_settings_template"); ?>
                <?php do_action("form_field_settings_template"); ?>
            </div>
            <div class="field-preview">
                <?php echo self::field_preview_html() ?>
            </div>
        </li>
    </script>
        <?php
        //field_configuration_template
        return ob_get_clean();
    }

    function process_field()
    {
        
    }

}
