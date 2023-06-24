<?php
namespace Wisdmforms;

class PaymentMethods {

    public function control_button() {
        ob_start();
        ?>
        <li class="list-group-item" data-type="<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>" for="PaymentMethods">
            <span class="lfi lfi-name"></span> <?php _e('Payment Methods', QUOTEUP_TEXT_DOMAIN) ?>
            <a title="<?php _e('Payment Methods', QUOTEUP_TEXT_DOMAIN) ?>" rel="PaymentMethods" class="add" data-template='PaymentMethods' href="#"><i class="glyphicon glyphicon-plus-sign pull-right ttipf" title=""></i></a>
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
                    <div class='form-group'>
                        <div class='row'>
                            <div class='col-md-6'>
                                <label>Amount</label>
                                <input type="text" class="form-control" value="<?php echo $field_infos[$fieldindex]['amount'] ?>" placeholder="Enter an amount here" name="contact[fieldsinfo][<?php echo $fieldindex ?>][amount]"/>
                            </div>
                            <div class='col-md-6'>
                                <label>Currency</label>
                                <?php $current_selection = $field_infos[$fieldindex]['currency']; ?>
                                <select class='form-control' name="contact[fieldsinfo][<?php echo $fieldindex ?>][currency]">
                                    <option value="none" <?php if ($current_selection == 'none') echo 'selected="selected"' ?>>Select a Currency</option>
                                    <?php foreach(currencies() as $value => $currency) { ?>
                                        <option <?php if ($current_selection == $value) echo 'selected="selected"' ?> value="<?php echo $value ?>"><?php echo $currency ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkboxes">
                            <ul>
                                <input type="hidden"
                                       name="contact[fieldsinfo][<?php echo $fieldindex ?>][payment][]"
                                       value="none"
                                       />
                                       <?php
                                        $methods_set = set_methods();
                                        foreach ($methods_set as $value => $name) {
                                           ?>

                                        <?php $payment_class = ucwords($value); ?>
                                    <li style="list-style: none"><label><input type="checkbox"
                                                                               name="contact[fieldsinfo][<?php echo $fieldindex ?>][payment][]"
                                                                               value="<?php echo $value ?>" <?php if (in_array($value, $field_infos[$fieldindex]['payment'])) echo 'checked="checked"'; ?> class="payment-method-select" data-config-panel="payment-<?php echo $fieldindex.'-'.$payment_class; ?>" <?php if (!class_exists($payment_class)) echo 'disabled="disabled"'; ?>/> <?php echo $name; ?>
                                        </label>
                                        <?php if (class_exists($payment_class)): ?>
                                        <div id='configs-payment-<?php echo $fieldindex.'-'.$payment_class ?>' class='form-group <?php if (!in_array($value, $field_infos[$fieldindex]['payment'])) echo 'hidden'; ?>'>
                                            <?php $payment = new $payment_class(); ?>
                                            <?php
                                            $fieldprefix = "contact[fieldsinfo][{$fieldindex}][paymethods]";
                                            $cache = $field_infos[$fieldindex]['paymethods'][ucwords(str_replace('wisdmform_','',$payment_class))];
                                            echo $payment->ConfigOptions($fieldprefix, $cache);    ?>
                                        </div>
                                        <?php endif ?>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>

                    <?php getFormNoteDiv($fieldindex, $field_infos); ?>
                    <div class='form-group'>
                        <label><input rel='condition-params' class='cond' type='checkbox' name='contact[fieldsinfo][<?php echo $fieldindex ?>][conditioned]' value='1' <?php if (isset($field_infos[$fieldindex]['conditioned'])) echo 'checked="checked"'; ?>/> Conditional logic</label>
                        <div id="cond_<?php echo $fieldindex ?>" class='cond-params' style='display:none'>
                            <div class="form-group">
                                <div class="row row-bottom-buffer">
                                    <div class="col-md-12">
                                        <select class="select" name="contact[fieldsinfo][<?php echo $fieldindex ?>][condition][action]">
                                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['action'] == 'show') echo 'selected="selected"' ?> value="show">Show</option>
                                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['action'] == 'hide') echo 'selected="selected"' ?> value="hide">Hide</option>
                                        </select>
                                        this field if
                                        <select class="select" name="contact[fieldsinfo][<?php echo $fieldindex ?>][condition][boolean_op]">
                                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['boolean_op'] == 'all') echo 'selected="selected"' ?> value="all">All</option>
                                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['boolean_op'] == 'any') echo 'selected="selected"' ?> value="any">Any</option>
                                        </select>
                                        of these conditions are met
                                    </div>
                                </div>
                                <?php $cond_list = isset($field_infos[$fieldindex]['condition']) ? $field_infos[$fieldindex]['condition']['value'] : array(); ?>
                                <?php foreach($cond_list as $key => $value) { ?>
                                <div class='row row-bottom-buffer' rel="row">
                                    <div class='col-md-4'>
                                        <select class='form-control cond-field-selector' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['field'][$key]) ? $field_infos[$fieldindex]['condition']['field'][$key] : ''  ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][field][]'>
                                            <option value="">Select a field</option>
                                        </select>
                                    </div>
                                    <div class='col-md-3'>
                                        <select class='form-control cond-operator' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['op'][$key]) ? $field_infos[$fieldindex]['condition']['op'][$key] : ''  ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][op][]'>
                                            <?php getIsCondSelectorOptions(); ?>
                                        </select>
                                    </div>
                                    <div class='col-md-4'>
                                        <select class='form-control is-cond-selector' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>'>
                                            <option value='email'>Email</option>
                                            <option value='phone'>Phone</option>
                                        </select>
                                        <input type='text' class='form-control is-cond-text hide' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>' placeholder='Enter a value' value=''/>
                                        <input type='hidden' value='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>' class="is-cond-data" name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][value][]'/>
                                    </div>
                                    <div class="col-md-1">
                                        <a href="#" class="add-cond-option"
                                           rel="<?php echo $fieldindex ?>"><i
                                                class="glyphicon glyphicon-plus-sign pull-left"></i></a>
                                        <a href="#" class="del-cond-option"
                                           rel="<?php echo $fieldindex ?>"><i
                                                class="glyphicon glyphicon-minus-sign pull-left"></i></a>

                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><input rel="req-params" class="req" type="checkbox"
                                      name="contact[fieldsinfo][<?php echo $fieldindex ?>][required]"
                                      value="1" <?php echo (isset($field_infos[$fieldindex]['required']) ? "checked=checked" : "") ?> />
                            Required</label>

                        <div
                            class="req-params" <?php echo (!isset($field_infos[$fieldindex]['required']) ? "style='display: none'" : "") ?>>
                            <input type="text"
                                   name="contact[fieldsinfo][<?php echo $fieldindex ?>][reqmsg]"
                                   placeholder="Field Required Message"
                                   value="<?php echo $field_infos[$fieldindex]['reqmsg'] ?>"
                                   class="form-control"/>
                            <label>Validation:</label>
                            <select
                                name="contact[fieldsinfo][<?php echo $fieldindex ?>][validation]"
                                class="form-control">
                                    <?php
                                    $validation_ops = get_validation_ops();
                                    foreach ($validation_ops as $value => $text) {
                                        echo '<option value="' . $value . '" ' . ($field_infos[$fieldindex]['validation'] == $value ? 'selected="selected "' : "") . '>' . $text . '</option>';
                                    }
                                    ?>
                            </select>
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
        ?>
        <div  class='form-group' >
            <label for='field' style='display: block;clear: both'>Payable amount: <?php echo get_currency_symbolised_amount(isset($params['amount']) ? $params['amount'] : '0.00', isset($params['currency']) ? $params['currency'] : 'USD'); ?></label>
            <select class='form-control' disabled="disabled" name=submitform[] id='payment' >
                <option selected='selected' disabled='disabled' value=''>Select a Payment Method</option>
            </select>
        </div>
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
        $selected_methods = isset( $params['payment'] ) ? $params['payment'] : array() ;  // get the list of selected types of methods
        ?>
        <div id="<?php echo $params['id'] ?>" class='form-group <?php echo isset($params['conditioned']) ? " conditioned hide " : ''?>' data-cond-fields="<?php echo $condition_fields ?>" data-cond-action="<?php echo $cond_action.':'.$cond_boolean ?>" >
            <label for='field' style='display: block;clear: both'>Payable amount: <?php echo get_currency_symbolised_amount($params['amount'], $params['currency']); ?></label>
            <select class='form-control' name=submitform[<?php echo $params['id']; ?>] id='payment' <?php echo required($params); ?>>
                <option selected='selected' disabled='disabled' value=''>Select a Payment Method</option>
            <?php
            $methods_set = set_methods();
            foreach ( $selected_methods as $method_name ) {
                if ( isset( $methods_set[$method_name] ) ) {  ?>
                    <option value='<?php echo $method_name; ?>'><?php echo $methods_set[$method_name] ?></option>
                <?php
                }
            }
            ?>
            </select>
            <div>
                <label class="field-note"><?php echo $params['note'] ?></label>
            </div>
        </div>
        <?php
        // field_render_html
        return ob_get_clean();
    }

    public function configuration_template() {
        ob_start();
        ?>
    <script type="text/x-mustache" id="template-PaymentMethods">
        <li class="list-group-item" data-type='<?php echo str_replace("Wisdmforms\\","",__CLASS__) ?>' id="field_{{ID}}"><input type="hidden" name="contact[fields][{{ID}}]" value="{{value}}">
            <span id="label_{{ID}}">{{title}}</span>
            <a href="#" rel="field_{{ID}}" class="remove"><i class="glyphicon glyphicon-remove-circle pull-right"></i></a>
            <a href="#" class="cog-trigger" rel="#cog_select_{{ID}}"><i class="glyphicon glyphicon-cog pull-right button-buffer-right"></i></a>

            <div class="field-preview">
                {{fieldpreview}}
            </div>
            <div class="cog" id="cog_select_{{ID}}" style="display: none;">
                <fieldset>
                    <h5><?php _e('Settings', QUOTEUP_TEXT_DOMAIN) ?></h5>

                    <?php getConfTempLabelDiv(); ?>
                    <?php //getConfTempNoteDiv(); ?>
                    <div class='form-group'>
                        <div class='row'>
                            <div class='col-md-6'>
                                <label><?php _e('Amount', QUOTEUP_TEXT_DOMAIN) ?></label>
                                <input type="text" class="form-control" value="" placeholder="<?php _e('Enter an amount here', QUOTEUP_TEXT_DOMAIN) ?>" name="contact[fieldsinfo][{{ID}}][amount]"/>
                            </div>
                            <div class='col-md-6'>
                                <label><?php _e('Currency', QUOTEUP_TEXT_DOMAIN) ?></label>
                                <select class='form-control' name="contact[fieldsinfo][{{ID}}][currency]">
                                    <option value="none" selected="selected"><?php _e('Select a currency', QUOTEUP_TEXT_DOMAIN) ?></option>
                                    <?php foreach(currencies() as $value => $currency) { ?>
                                        <option value="<?php echo $value ?>"><?php echo $currency ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Options', QUOTEUP_TEXT_DOMAIN) ?></label>

                        <div class="form-group">
                            <div class="checkboxes">
                                <ul>
                                    <input type="hidden" name="contact[fieldsinfo][{{ID}}][payment][]" value="none"/>
                                    <?php
                                    $methods_set = set_methods();
                                    foreach ($methods_set as $value => $name) {
                                        ?>

                                    <?php $payment_class = ucwords($value); ?>
                                        <li style="list-style: none">
                                            <label><input type="checkbox" name="contact[fieldsinfo][{{ID}}][payment][]" value="<?php echo $value ?>" data-config-panel="payment-{{ID}}-<?php echo $payment_class ?>" class="payment-method-select" <?php if (!class_exists($payment_class)) echo 'disabled="disabled"' ?>/> <?php echo $name ?>
                                            </label>
                                            <?php if (class_exists($payment_class)) { ?>
                                            <div id='configs-payment-{{ID}}-<?php echo $payment_class ?>' class='form-group hidden'>
                                            <?php $payment = new $payment_class() ?>
                                            <?php
                                            $fieldprefix = 'contact[fieldsinfo][{{ID}}][paymethods]';
                                            echo $payment->ConfigOptions($fieldprefix)    ?>
                                            </div>

                                            <?php } ?>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                        <label><input rel="req-params" class="req" type="checkbox"
                                      name="contact[fieldsinfo][{{ID}}][required]" value="1"/> <?php _e('Required', QUOTEUP_TEXT_DOMAIN) ?></label>

                        <div class="req-params" style="display: none">
                            <input type="text" name="contact[fieldsinfo][{{ID}}][reqmsg]"
                                   placeholder="Field Required Message" value="" class="form-control"/>
                            <label>Validation:</label>
                            <select name="contact[fieldsinfo][{{ID}}][validation]" class="form-control">
                            <?php foreach(get_validation_ops() as $op => $label) { ?>
                                <option value="<?php echo $op ?>"><?php echo $label ?></option>
                            <?php } ?>
                            </select>
                        </div>
                    </div>
                </fieldset>
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
