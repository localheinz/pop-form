<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form;

use Pop\Dom\Child;
use Pop\Form\Element;

/**
 * Form class
 *
 * @category   Pop
 * @package    Pop\Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */

class Form extends Child implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Field fieldsets
     * @var array
     */
    protected $fieldsets = [];

    /**
     * Current field fieldset
     * @var int
     */
    protected $current = 0;

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     */
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        $action = ((null === $action) && isset($_SERVER['REQUEST_URI'])) ?
            $_SERVER['REQUEST_URI'] : '#';

        parent::__construct('form');
        $this->setAttributes([
            'action' => $action,
            'method' => $method
        ]);

        if (null !== $fields) {
            $this->addFields($fields);
        }
    }

    /**
     * Method to create form object and fields from config
     *
     * @param  array  $config
     * @param  string $action
     * @param  string $method
     * @return Form
     */
    public static function createFromConfig(array $config, $action = null, $method = 'post')
    {
        $fields = [];

        foreach ($config as $name => $field) {
            $fields[$name] = Field::create($name, $field);
        }

        return new self($fields, $action, $method);
    }

    /**
     * Method to create a new fieldset object
     *
     * @param  string  $legend
     * @return Fieldset
     */
    public function createFieldset($legend = null)
    {
        $fieldset = new Fieldset();
        if (null !== $legend) {
            $fieldset->setLegend($legend);
        }

        $this->addFieldset($fieldset);

        $id = (null !== $this->getAttribute('id')) ?
            $this->getAttribute('id') . '-fieldset-' . ($this->current + 1) : 'pop-form-fieldset-' . ($this->current + 1);

        $class = (null !== $this->getAttribute('class')) ?
            $this->getAttribute('id') . '-fieldset' : 'pop-form-fieldset';

        $fieldset->setAttribute('id', $id);
        $fieldset->setAttribute('class', $class);

        return $fieldset;
    }

    /**
     * Method to add fieldset
     *
     * @param  Fieldset $fieldset
     * @return Form
     */
    public function addFieldset(Fieldset $fieldset)
    {
        $this->fieldsets[] = $fieldset;
        $this->current     = count($this->fieldsets) - 1;
        return $this;
    }

    /**
     * Method to remove fieldset
     *
     * @param  int $i
     * @return Form
     */
    public function removeFieldset($i)
    {
        if (isset($this->fieldsets[(int)$i])) {
            unset($this->fieldsets[(int)$i]);
        }
        $this->fieldsets = array_values($this->fieldsets);
        if (!isset($this->fieldsets[$this->current])) {
            $this->current = (count($this->fieldsets) > 0) ? count($this->fieldsets) - 1 : 0;
        }
        return $this;
    }

    /**
     * Method to get current fieldset
     *
     * @return Fieldset
     */
    public function getFieldset()
    {
        return (isset($this->fieldsets[$this->current])) ? $this->fieldsets[$this->current] : null;
    }

    /**
     * Method to get current fieldset index
     *
     * @return int
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Method to get current fieldset index
     *
     * @param  int $i
     * @return Form
     */
    public function setCurrent($i)
    {
        $this->current = (int)$i;
        if (!isset($this->fieldsets[$this->current])) {
            $this->fieldsets[$this->current] = $this->createFieldset();
        }
        return $this;
    }

    /**
     * Method to get the legend of the current fieldset
     *
     * @return string
     */
    public function getLegend()
    {
        return (isset($this->fieldsets[$this->current])) ?
            $this->fieldsets[$this->current]->getLegend() : null;
    }

    /**
     * Method to set the legend of the current fieldset
     *
     * @param  string $legend
     * @return Form
     */
    public function setLegend($legend)
    {
        if (isset($this->fieldsets[$this->current])) {
            $this->fieldsets[$this->current]->setLegend($legend);
        }
        return $this;
    }

    /**
     * Method to add a form field
     *
     * @param  Element\AbstractElement $field
     * @return Form
     */
    public function addField(Element\AbstractElement $field)
    {
        if (count($this->fieldsets) == 0) {
            $this->createFieldset();
        }
        $this->fieldsets[$this->current]->addField($field);
        return $this;
    }

    /**
     * Method to add form fields
     *
     * @param  array $fields
     * @return Form
     */
    public function addFields(array $fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    /**
     * Method to get the count of elements in the form
     *
     * @return int
     */
    public function count()
    {
        $count = 0;
        foreach ($this->fieldsets as $fieldset) {
            $count += $fieldset->count();
        }
        return $count;
    }

    /**
     * Method to get the field values as an array
     *
     * @return array
     */
    public function toArray()
    {
        $fieldValues = [];

        foreach ($this->fieldsets as $fieldset) {
            $fieldValues = array_merge($fieldValues, $fieldset->toArray());
        }

        return $fieldValues;
    }

    /**
     * Get the form action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getAttribute('action');
    }
    /**
     * Get the form method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getAttribute('method');
    }

    /**
     * Method to get a field element object
     *
     * @param  string $name
     * @return Element\AbstractElement
     */
    public function getField($name)
    {
        return (isset($this->fieldsets[$this->current])) ? $this->fieldsets[$this->current]->getField($name) : null;
    }

    /**
     * Method to get field element objects
     *
     * @return array
     */
    public function getFields()
    {
        $fields = [];

        foreach ($this->fieldsets as $fieldset) {
            $fields = array_merge($fields, $fieldset->getFields());
        }

        return $fields;
    }

    /**
     * Method to get a field element value
     *
     * @param  string $name
     * @return mixed
     */
    public function getFieldValue($name)
    {
        return (isset($this->fieldsets[$this->current])) ? $this->fieldsets[$this->current]->getFieldValue($name) : null;
    }

    /**
     * Method to set a field element value
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Form
     */
    public function setFieldValue($name, $value)
    {
        if (isset($this->fieldsets[$this->current])) {
            $this->fieldsets[$this->current]->setFieldValue($name, $value);
        }
        return $this;
    }

    /**
     * Method to set field element values
     *
     * @param  array $values
     * @return Form
     */
    public function setFieldValues(array $values)
    {
        foreach ($values as $name => $value) {
            $this->setFieldValue($name, $value);
        }
        return $this;
    }

    /**
     * Method to iterate over the form elements
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }

    /**
     * Determine whether or not the form object is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $valid  = true;
        $fields = $this->getFields();

        // Check each element for validators, validate them and return the result.
        foreach ($fields as $field) {
            if ($field->validate() == false) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Get form element errors for a field.
     *
     * @param  string $name
     * @return array
     */
    public function getErrors($name)
    {
        $field  = $this->getField($name);
        $errors = (null !== $field) ? $field->getErrors() : [];

        return $errors;
    }

    /**
     * Get all form element errors
     *
     * @return array
     */
    public function getAllErrors()
    {
        $errors = [];
        $fields = $this->getFields();
        foreach ($fields as $name => $field) {
            if ($field->hasErrors()) {
                $errors[str_replace('[]', '', $field->getName())] = $field->getErrors();
            }
        }

        return $errors;
    }

    /**
     * Method to reset and clear any form field values
     *
     * @return Form
     */
    public function reset()
    {
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $field->resetValue();
        }
        return $this;
    }

    /**
     * Method to clear any security tokens
     *
     * @return Form
     */
    public function clearTokens()
    {
        // Start a session.
        if (session_id() == '') {
            session_start();
        }
        if (isset($_SESSION['pop_csrf'])) {
            unset($_SESSION['pop_csrf']);
        }
        if (isset($_SESSION['pop_captcha'])) {
            unset($_SESSION['pop_captcha']);
        }

        return $this;
    }

    /**
     * Prepare form object for rendering
     *
     * @return Form
     */
    public function prepare()
    {
        if (null !== $this->getAttribute('id')) {
            $this->setAttribute('id', 'pop-form');
        }
        if (null !== $this->getAttribute('class')) {
            $this->setAttribute('class', 'pop-form');
        }

        foreach ($this->fieldsets as $fieldset) {
            $fieldset->prepare();
            $this->addChild($fieldset);
        }

        return $this;
    }

    /**
     * Render the form object
     *
     * @param  int     $depth
     * @param  string  $indent
     * @return mixed
     */
    public function render($depth = 0, $indent = null)
    {
        if (!($this->hasChildren())) {
            $this->prepare();
        }
        return parent::render($depth, $indent);
    }

    /**
     * Render and return the form object as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Set method to set the property to the value of fields[$name]
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setFieldValue($name, $value);
    }

    /**
     * Get method to return the value of fields[$name]
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }

    /**
     * Return the isset value of fields[$name]
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return (isset($this->fieldsets[$this->current]) && (null !== $this->fieldsets[$this->current][$name]));
    }

    /**
     * Unset fields[$name]
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->fieldsets[$this->current])) {
            $this->fieldsets[$this->current][$name] = null;
            unset($this->fieldsets[$this->current][$name]);
        }
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

}
