<?php

namespace Sip\Forms;

abstract class BaseForm
{
    private $formName;
    private $fields;
    private $errors;

    public function __construct($formName='form')
    {
        $this->formName = $formName;
        $this->fields = array();
    }

    public function getFormName()
    {
        return $this->formName;
    }

    public function setField($name, $label, $type='text', $defValue='', $validators=array())
    {
        $this->fields[$name] = array(
            'label' => $label,
            'type' => $type,
            'def_value' => $defValue,
            'value' => $defValue,
            'validators' => $validators
        );
    }

    public function setSelectField($name, $label, $defValue, $selectList=array(), $validators=array())
    {
        $this->fields[$name] = array(
            'label' => $label,
            'type' => 'select',
            'def_value' => $defValue,
            'value' => $defValue,
            'select_list' => $selectList,
            'validators' => $validators
        );
    }

    public function setListField($name, $value=array())
    {
        $this->fields[$name] = array(
            'type' => 'list',
            'def_value' => array(),
            'value' => $value
        );
    }

    public function removeField($name)
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }
    }

    public function setParam($fieldName, $paramName, $value)
    {
        if (!isset($this->fields[$fieldName])) {
            return False;
        }

        if (!isset($this->fields[$fieldName][$paramName])) {
            return False;
        }

        $this->fields[$fieldName][$paramName] = $value;
        return True;
    }

    public function getParam($fieldName, $paramName)
    {
        if (!isset($this->fields[$fieldName])) {
            return Null;
        }

        if (!isset($this->fields[$fieldName][$paramName])) {
            return Null;
        }

        return $this->fields[$fieldName][$paramName];
    }

    public function fillFromRequest($request)
    {
        $formData = $request->request->get($this->getFormName());

        if ($formData == Null) {
            return False;
        }

        foreach ($this->fields as $fieldName => $params) {

            if ($params['type'] == 'list') {
                continue;
            }

            $this->fields[$fieldName]['value'] = (isset($formData[$fieldName]))
                ? $formData[$fieldName] : $this->fields[$fieldName]['def_value'];
        }
        return True;
    }

    public function fillFromDB($data)
    {
        foreach ($this->fields as $fieldName => $params) {

            if ($params['type'] == 'list') {
                continue;
            }

            $this->fields[$fieldName]['value'] = (isset($data[$fieldName]))
                ? $data[$fieldName] : $this->fields[$fieldName]['def_value'];
        }
    }

    public function clearForm()
    {
        foreach ($this->fields as $fieldName => $params) {
            $this->fields[$fieldName]['value'] = $this->fields[$fieldName]['def_value'];
        }

        $this->errors = array();
    }

    public function validate($app)
    {
        $this->errors = array();

        foreach ($this->fields as $fieldName => $params) {

            if ($params['type'] == 'list') {
                continue;
            }

            $localErrors = $app['validator']->validate($params['value'], $params['validators']);

            foreach ($localErrors as $localError) {
                $this->errors[] = $params['label'] . ': ' . $localError->getMessage();
            }
        }
        return (count($this->errors) == 0);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }
}