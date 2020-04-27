<?php
namespace pix\helpers;

class ErrorView_StandardPl extends \ErrorView {

    public function applyAjaxErrorResponse() {
        $id = $this->_form->getAttribute("id");
        echo <<<JS
        var errorSize = response.errors.length;
        if(errorSize == 1)
            var errorFormat = "1 błąd";
        else
            var errorFormat = errorSize + " błędów";

        jQuery('.alert-danger').hide();
        var errorHTML = '<div class="alert alert-danger"><a class="close" data-dismiss="alert" href="#">×</a><strong class="alert-heading">Znaleziono ' + errorFormat + ':</strong><ul>';
        for(e = 0; e < errorSize; ++e)
            errorHTML += '<li>' + response.errors[e] + '</li>';
        errorHTML += '</ul></div>';
        jQuery("#$id").prepend(errorHTML);
JS;

    }

    private function parse($errors) {
        foreach($errors as $k=>&$error){
            $errors[$k] = is_array($error)? $k.': '.$error[0] : $error;
        }
        return  $errors;
    }

    public function render() {
        $errors = $this->parse($this->_form->getErrors());
        if(!empty($errors)) {
            $size = count($errors);
            $errors_string = implode("</li><li>", $errors);

            if($size == 1)
                $format = "1 błąd";
            else
                $format = $size . " błędów";

            echo <<<HTML
            <div class="alert alert-danger">
                <a class="close" data-dismiss="alert" href="#">×</a>
                <strong class="alert-heading">Znaleziono $format :</strong>
                <ul>$errors_string</ul>
            </div>
HTML;
        }
    }

    public function renderAjaxErrorResponse() {
        $errors = $this->parse($this->_form->getErrors());
        if(!empty($errors)) {
            header("Content-type: application/json");
            echo json_encode(array("errors" => $errors));
        }
    }
}
