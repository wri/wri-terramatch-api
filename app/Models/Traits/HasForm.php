<?php

namespace App\Models\Traits;

use App\Models\V2\Forms\Form;

/**
 * @property string framework_key
 */
trait HasForm {
    private ?Form $form = null;

    public function getForm(): ?Form
    {
        if (is_null($this->form)) {
            $this->form = Form::where('model', get_class($this))
                ->where('framework_key', $this->framework_key)
                ->first();
        }
        return $this->form;
    }
}