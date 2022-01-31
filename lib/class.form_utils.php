<?php

namespace m21;

/* tools for parsing web forms and storing the data into a mysql database */

class Form_Utils {
    private static $instance;

    static function buildForm($fields, $prepop = '') {
        /* builds a form (not including form tags) based on supplied arrays.  prepop is optional.
           $fields is an array. each key is a field name which will be used as the html id & name
           for the form  element (unless overridden by name & id params below).

           the values can be are an array that can contain...
           type (required) : text, textarea, select, radio, button, checkbox, or message (just text, not a form element)
           label : text to describe input field
           id : by defailt the field name is used, but you can make it something else
           name : by defailt the field name is used, but you can make it something else
           default : default value to prepopulate field with.
           classes : any classes you would like to apply to the input element
           required : if set tyo 'y' an asterisk will appear next to the field. use span.form_required to style.
           rows : a number, for textarea only
           cols : a number, for textarea only
           size: a number, the size for a text input
           options : an array of options for select menus only
           hint: instructions for filling out the field. use span.form_hint to style.
           wrapper: html tag to wrap around each label/input group (example: 'p')
           button_text : the text inside the button, buttons only
           button_onclick : the value for the button's onclick attribute

           $prepop is an array which has the same keys as fields, with the current value (overrides default value)
        */

        if ( ! is_array($fields)) {
            return false;
        }

        $html = '';

        foreach ($fields as $field => $info) {
            $prepop_value = '';
            if (isset($prepop) && isset($prepop[ $field ])) {
                $prepop_value = $prepop[ $field ];
            }

            // build the input/select html
            $html .= buildField($field, $info, $prepop_value);
        }

        return $html;
    }

    static function buildField($field, $info, $prepop_value = '') {
        /* usage is similar to buildForm (see above), but instead an array of fields, there's only 1 */
        $html = '';

        $prepop_value = $prepop_value ? $prepop_value : $info['default'];

        // wrapper
        if (!empty($info['wrapper'])) {
            $html .= '<' . $info['wrapper'] . ' class="form_message">';
        }

        // type message - not a form element
        if ($info['type'] == 'message') {
            $html .= $info['label'];
            if (!empty($info['wrapper'])) {
                $html .= '</' . $info['wrapper'] . '>';
            }

            return $html;
        }

        $name = !empty($info['name']) ? $info['name'] : $field;

        if($info['type'] === 'multi'){
            $name .= '[]';
        }

        $id   = !empty($info['id']) ? $info['id'] : $field;

        // BUILD LABEL
        $html .= '<label for="' . $id . '">';
        if (is_array($info) && isset($info['label'])) {
            // custom label text that differs from the field name
            $html .= $info['label'];
        } else {
            // just use field name
            $html .= $field;
        }
        // is required?
        if (isset($info['required']) && $info['required'] == 'y') {
            $html .= '<span class="form_required">*</span>';
        }
        $html .= '</label>';

        // BUILD HTML INPUT ELEMENT

        // name & id
        $name_n_id = ' name="' . $name . '" id="' . $id . '" ';

        // classes
        if (!empty($info['classes'])) {
            $name_n_id .= ' class="' . $info['classes'] . '"';
        }

        switch($info['type']){
            case 'text':
                $html .= '<input type="text"' . $name_n_id;
                $html .= ' value="' . esc_attr($prepop_value) . '"';
                if (isset($info['size']) && $info['size'] > 0) {
                    $html .= ' size="' . $info['size'] . '"';
                }
                $html .= '>';
                break;
            case 'date':
                $html .= '<input type="date"' . $name_n_id;
                $html .= ' value="' . esc_attr($prepop_value) . '"';
                if (isset($info['max'])) {
                    $html .= ' max="' . $info['max'] . '"';
                }
                $html .= '>';
                break;
            case 'checkbox':
                $html .= '<input type="checkbox"' . $name_n_id;
                if ($prepop_value == 1 || $prepop_value === 'yes' || $prepop_value === 'on') {
                    $html .= ' checked="checked"';
                }
                $html .= '>';
                break;
            case 'textarea':
                $html .= '<textarea' . $name_n_id;
                if (isset($info['rows']) && is_numeric($info['rows'])) {
                    $html .= ' rows="' . $info['rows'] . '"';
                }
                if (isset($info['cols']) && is_numeric($info['cols'])) {
                    $html .= ' cols="' . $info['cols'] . '"';
                }
                $html .= '>';
                if ($prepop_value) {
                    $html .= esc_attr($prepop_value);
                }
                $html .= '</textarea>';
                break;
            case 'button':
                if ($info['button_onclick']) {
                    $html .= '<input type="submit"' . $name_n_id . ' value="' . $info['button_text'] . '"';
                    $html .= ' onClick="' . $info['button_onclick'] . '" />';
                }
                break;
            case 'multi':
                $html .= '<select' . $name_n_id . ' multiple="multiple">';
                $html .= '<option value="">-- select --</option>';
                // is array numerically indexed or associative?
                $is_assoc = false;
                if (count(array_diff_key($info['options'], array_keys(array_keys($info['options'])))) > 0) {
                    $is_assoc = true;
                }

                foreach ($info['options'] as $id => $label) {
                    // for non-associative arrays, use the name for both the text and the value
                    if ( ! $is_assoc) $id = $label;

                    $html .= sprintf(
                        '<option value="%s" %s >%s</option>',
                        $id,
                        is_array($prepop_value) && in_array( $id, $prepop_value) ? 'selected="selected"' : '',
                        $label
                    );
                }
                $html .= '</select>';
                break;
            case 'select':
                $html .= '<select' . $name_n_id . '>';
                $html .= '<option value="">-- select --</option>';
                // is array numerically indexed or associative?
                $is_assoc = false;
                if (count(array_diff_key($info['options'], array_keys(array_keys($info['options'])))) > 0) {
                    $is_assoc = true;
                }

                foreach ($info['options'] as $id => $label) {
                    // for non-associative arrays, use the name for both the text and the value
                    if ( ! $is_assoc) $id = $label;

                    $html .= '<option value="' . $id . '"';
                    if ($id == $prepop_value) {
                        $html .= ' selected="selected"';
                    }
                    $html .= '>' . $label . '</option>';
                }
                $html .= '</select>';
                break;
            case 'radio':
                foreach ($info['options'] as $value => $radio_label) {
                    $html .= '<span class="radio_unit">' . $radio_label . '<input type="radio" name="' . $field . '" value="' . $value . '"';
                    if ($prepop_value == $value) {
                        $html .= ' checked="checked"';
                    }
                    $html .= '></span>';
                }
                break;

        }

        // add in hint field
        if (isset($info['hint']) && $info['hint'] != '') {
            $html .= '<span class="form_hint">' . $info['hint'] . '</span>';
        }

        // end wrapper
        if (!empty($info['wrapper'])) {
            $html .= '</' . $info['wrapper'] . '>';
        }

        return $html;
    }

    protected function __construct() {

    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Form_Utils The singleton instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone() {
    }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup() {
    }
}