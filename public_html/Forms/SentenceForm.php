<?php

namespace Sip\Forms;

class SentenceForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct('sentence');
        $this->setField('foreign_sentence', 'Foreign sentence', 'text', '');
        $this->setField('native_sentence', 'Native sentence', 'text', '');
        $this->setField('parts', 'Parts', 'text', '');
    }
}