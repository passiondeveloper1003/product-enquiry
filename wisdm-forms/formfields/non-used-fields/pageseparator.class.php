<?php
namespace Wisdmforms;

class Pageseparator {

    public function control_button() {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="Pageseparator">
            <span class="lfi lfi-name"></span> Page separator 
            <a title="Pageseparator" rel="Pageseparator" class="add" data-template='Pageseparator' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
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
            <span id="label_<?php echo $fieldindex; ?>"><?php echo "[Separator] " . $field_infos[$fieldindex]['label'] ?>:</span>
            <a href="#" rel="field_<?php echo $fieldindex; ?>" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_<?php echo $fieldindex; ?>"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>
            <div class="cog" id="cog_<?php echo $fieldindex; ?>" style='display: none'>
                <fieldset>
                    <h5>Settings</h5>
                    <div class="form-group">
                        <label>Label:</label>
                        <input class="form-control form-field-label" data-target="#label_<?php echo $fieldindex; ?>" type="text" value="<?php echo $field_infos[$fieldindex]['label'] ?>" data-field-type="separator" name="contact[fieldsinfo][<?php echo $fieldindex ?>][label]" />
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <textarea class="form-control" type="text" value="" name="contact[fieldsinfo][<?php echo $fieldindex ?>][note]"><?php echo $field_infos[$fieldindex]['note'] ?></textarea>
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
        ?>
        [Separator] 
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function field_render_html($params = array()) {
        ob_start();
        if (isset($params['label']))    
            $separator_html = $params['label'];
        else if (is_string($params))
            $separator_html = $params;
        ?>
        <?php echo $separator_html ?>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template() {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-Pageseparator">
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">[Separator] Page separator</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>

            
            <div class="cog" id="cog_{{ID}}" style="display: none;">
                <fieldset>
                    <h5>Settings</h5>

                    <div class="form-group">
                        <label>Label:</label>
                        <input class="form-control form-field-label" data-field-type="separator" data-target="#label_{{ID}}" type="text"
                               value="{{title}}"
                               name="contact[fieldsinfo][{{ID}}][label]"/>
                    </div>
                    <?php getConfTempNoteDiv(); ?>
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
