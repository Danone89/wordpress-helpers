<?php


namespace pix\helpers;

class Bootstrap_Form_Builder
{
    /**
     * Where form should post
     */
    var $action = '';
    /**
     * Form ID used by validator, form display
     *
     * @var [type]
     */
    protected $id;
    protected $template_loader = null;
    protected $count = 1;
    protected $active_repeatables = [];
    /**
     * Holds selection options, and lists for form select,multiselect and radio fields
     *
     * @var array
     */
    protected $form_data = [];
    /**
     * Holds user/system populeted data
     *
     * @var array
     */
    protected $field_data = [];

    function __construct($id = '', $template_loader = null)
    {
        $this->id = ($id ? $id : 'form-' . $this->count);
        if (!session_id()) session_start();

        if (!$template_loader) {
            $template_loader = function ($element) {
                return $element . '.php';
            };
        }
        $this->template_loader = $template_loader;
    }

    function set_data($field_data = [], $form_data = [])
    {

        $this->field_data = $field_data;
        $this->form_data = $form_data;
    }
    function set_error($element,$msg)
    {
        \Form::setError($this->id, $msg,$element);
    }
    function errors()
    {
        return \Form::getErrorsArray($this->id);
    }
    function validate($ajax = false)
    {
        if (!\Form::isValid($this->id)) {
            if ($ajax) {
                \Form::renderAjaxErrorResponse($this->id);
                die();
            }
            return false;
        }
        return true;
    }
    /**
     * Pass all to builder
     *
     * @param [type] $name
     * @param [type] $args
     * @return void
     */
    function __call($name, $args)
    {
        if (in_array($name, ['Select', 'Radio'])) {
            if ($args[2] === [] && !empty($this->form_data[$args[1]])) {
                $args[2] = $this->form_data[$args[1]]['options'];
            }
        }
        return call_user_func("\Form::$name", ...$args);
    }

    function Open($args = [])
    {
        $args['errorView'] = new ErrorView_StandardPl();
        $args['action'] = $this->action;
        \Form::open($this->id, $this->field_data, $args, $this->count++);
        \Form::Hidden("nonce");
        \Form::Hidden("id");
        \Form::Hidden("action");
    }
    /**
     * 
     *
     * @param [type] $name - field label
     * @param [type] $slug - of partials to load
     * @return void
     */
    function Repeatable($name, $slug)
    {
        $data = [];
        $this->active_repeatables[$this->count][] = $slug;
        if (!empty($this->filed_data[$slug]))
            $data = unserialize($this->filed_data[$slug]);
        ?>
        <div class="form-group repeatable-wrap <?= $slug; ?>">
            <div class="col-xs-12 col-md-12">
                <div class="repeatable-container">
                    <?php if ($data) : ?>

                    <?php endif; ?>

                </div>
                <button href="#" class="add_<?= $slug; ?> btn btn-secondary " style="margin:7px auto; display:block">
                    <i class="fas fa-sm fa-plus"></i> Dodaj <?= strtolower($name); ?></button>
            </div>

            <input type="hidden" name="reps[]" value="<?= $slug; ?>" />

        </div>
        <?php
            }

            function Close($buttons = false)
            {

                \Form::close($buttons);
                if ($this->active_repeatables != [])
                    $this->render_repeatable_templates();
                include ($this->template_loader)('partials/loading-modal');

                $this->count--;
            }

            protected function render_repeatable_templates()
            {

                foreach ($this->active_repeatables[$this->count] as $element) :
                    $j = 1;
                    ?>
            <script type="text/template" id="<?= $element; ?>">
                <?php $func = $this->template_loader;
                            include $func('repeatable/'.$element);  ?>

            </script>
        <?php
                endforeach;
                ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                <?php foreach ($this->active_repeatables[$this->count] as $e) : ?>
                    createRepeatable('<?= $e; ?>');
                <?php endforeach; ?>
            });
        </script>
<?php
    }
}
