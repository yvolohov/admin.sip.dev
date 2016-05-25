<?php

namespace Sip\Forms;

use Symfony\Component\Validator\Constraints as Assert;

class TemplateForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct('template');
        $this->setField('foreign_template', 'Foreign template', 'text', '');
        $this->setField('native_template', 'Native template', 'text', '');
    }
}