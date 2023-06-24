<?php
namespace Wisdmforms;

class Daterange
{

    public function control_button()
    {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="Daterange">
            <span class="lfi lfi-name"></span> <?php _e('Date range', QUOTEUP_TEXT_DOMAIN) ?> 
            <a title="<?php _e('Date Range', QUOTEUP_TEXT_DOMAIN) ?>" rel="Daterange" class="add" data-template='Daterange' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
        </li>
        <?php
        // control_button_html
        return ob_get_clean();
    }

    public function field_settings($fieldindex, $fieldid, $field_infos)
    {
        ob_start();
?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>"  id="field_<?php echo $fieldindex; ?>">
            <input type="hidden" name="contact[fields][<?php echo $fieldindex ?>]" value="<?php echo $fieldid; ?>">
            <span id="label_<?php echo $fieldindex; ?>"><?php echo $field_infos[$fieldindex]['label'] ?>:</span>
            <a href="#" rel="field_<?php echo $fieldindex; ?>" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_<?php echo $fieldindex; ?>"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
            <div class="cog" id="cog_<?php echo $fieldindex; ?>" style='display: none'>
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>
                    <?php getFormLabelDiv($fieldindex, $field_infos); ?>
                    <div class='form-group'>
                        <label><?php _e('Date format', QUOTEUP_TEXT_DOMAIN) ?>:</label>
                        <input class='form-control row-bottom-buffer' type="text" value="<?php echo $field_infos[$fieldindex]['date_format'] ?>" name="contact[fieldsinfo][<?php echo $fieldindex ?>][date_format]"/>
                        
                    </div>
                    <?php getFormNoteDiv($fieldindex, $field_infos); ?>
                    <?php getFormConditionalDiv($fieldindex, $field_infos); ?>
                    <!-- <div class="form-group">
                        <label>Validation:</label>
                        <select name="contact[fieldsinfo][<?php echo $fieldindex ?>][validation]" class="form-control">
                            <option selected='selected' value="date">Date</option>
                        </select>
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
        <div class="row">
            <div class="col-md-6">
                <input class='form-control datepicker' disabled="disabled" type='text' name='submitform[]' />
            </div>
            <div class="col-md-6">
                <input class='form-control datepicker' disabled="disabled" type='text' name='submitform[]' />
            </div>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array())
    {
        ob_start();
        $date_format = !isset($params['date_format']) ? "dd/mm/yy" : $params['date_format'];
        $datepicker_field1_id = "datepicker1_{$params['id']}".rand();
        $datepicker_field2_id = "datepicker2_{$params['id']}".rand();
        $condition_fields = '';
        $cond_action = '';
        $cond_boolean = '';
        if (isset($params['condition']) && isset($params['conditioned'])) {
            $cond_boolean = $params['condition']['boolean_op'];
            $cond_action = $params['condition']['action'];
            foreach ($params['condition']['field'] as $key => $value) {
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
            <?php
            if (quoteupCFFieldsLabel($params['id'])) {
                ?>
                <label for='field' style='display: block;clear: both'><?php echo quoteupReturnCustomFormFieldLabel($params['label']); ?></label>
                <?php
            }
            ?>
            <div class='row' >
                <div class="col-md-6">
                    <input class='form-control datepicker' type='text' name='submitform[<?php echo $name ?> from]' id='<?php echo $datepicker_field1_id ?>' <?php echo required($params); ?> onfocus="this.blur()" readonly="readonly"/>
                </div>
                <div class="col-md-6">
                    <input class='form-control datepicker' type='text' name='submitform[<?php echo $name; ?> to]' id='<?php echo $datepicker_field2_id; ?>' <?php echo required($params); ?> onfocus="this.blur()" readonly="readonly"/>
                </div>
            </div>
            <div>
                <label class="field-note"><?php echo quoteupReturnCustomFormFieldNote($params['note']); ?></label>
            </div>
        </div>

        <script type='text/javascript'>
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = today.getFullYear();
            jQuery(document).ready(function($){
                jQuery( '#<?php echo $datepicker_field1_id ?>' ).datepicker({
                    defaultDate: '+1w',
                    dateFormat: '<?php echo $date_format ?>',
                    changeMonth: false,
                    numberOfMonths: 1,
                    minDate: new Date(yyyy, mm - 1, dd),
                });
                jQuery( '#<?php echo $datepicker_field2_id ?>' ).datepicker({
                    defaultDate: '+1w',
                    dateFormat: '<?php echo $date_format ?>',
                    changeMonth: true,
                    numberOfMonths: 1,
                    minDate: new Date(yyyy, mm - 1, dd),
                });
        });
        </script>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template()
    {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-Daterange">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">{{title}}</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
            <div class="cog" id="cog_{{ID}}" style="display: none;">
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>

                    <?php getConfTempLabelDiv(); ?>
                <div class='form-group'>
                    <label><?php _e('Date format', QUOTEUP_TEXT_DOMAIN) ?>:</label>
                    <input class='form-control row-bottom-buffer' type="text" value="dd-mm-yy" name="contact[fieldsinfo][{{ID}}][date_format]"/>
                    
                </div>
                    <?php getConfTempNoteDiv(); ?>
                    <?php getConfTempConditionalDiv(); ?>
                    <!-- <div class="form-group">
                        <label>Validation:</label>
                        <select name="contact[fieldsinfo][{{ID}}][validation]" class="form-control">
                            <option selected='selected' value="date">Date</option>
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
